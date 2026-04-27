<?php

namespace App\Services\CFDI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SATService
{
    private const SAT_URL    = 'https://consultaqr.facturaelectronica.sat.gob.mx/default.aspx';
    private const CACHE_TTL  = 43200; // 12 horas en segundos

    public function validar(array $cfdi): array
    {
        // Cache por UUID para no martillar al SAT en cada request
        $key = 'sat_cfdi_' . strtolower($cfdi['uuid']);

        return \Illuminate\Support\Facades\Cache::remember($key, self::CACHE_TTL, function () use ($cfdi) {
            return $this->consultarSAT($cfdi);
        });
    }

    /**
     * Invalida el cache de un CFDI (útil si el SAT reporta cancelación posterior).
     */
    public function invalidarCache(string $uuid): void
    {
        \Illuminate\Support\Facades\Cache::forget('sat_cfdi_' . strtolower($uuid));
    }

    protected function consultarSAT(array $cfdi): array
    {
        $url = $this->buildUrl($cfdi);

        try {
            $response = \Illuminate\Support\Facades\Http::retry(3, 300)
                ->timeout(10)
                ->get($url);
        } catch (\Exception $e) {
            throw new \Exception('Error al conectar con el SAT: ' . $e->getMessage());
        }

        if (!$response->ok()) {
            throw new \Exception('SAT respondió con HTTP ' . $response->status());
        }

        return $this->parseResponse($response->body());
    }

    protected function buildUrl(array $cfdi): string
    {
        // ✅ 'fe' es los últimos 8 caracteres del SELLO del CFDI (no un md5 del UUID)
        // Referencia: https://www.sat.gob.mx/consultas/20585/consulta-de-cfdi
        $fe = substr($cfdi['sello'] ?? '', -8);

        return self::SAT_URL . '?' . http_build_query([
            'id' => $cfdi['uuid'],
            're' => $cfdi['rfc_emisor'],
            'rr' => $cfdi['rfc_receptor'],
            'tt' => number_format((float) $cfdi['total'], 6, '.', ''),
            'fe' => $fe,
        ]);
    }

    protected function parseResponse(string $body): array
    {
        // Normaliza para comparación case-insensitive
        $lower = mb_strtolower($body);

        // El SAT puede responder HTML o texto plano dependiendo del endpoint
        if (str_contains($lower, 'vigente')) {
            return ['estatus' => 'vigente'];
        }

        if (str_contains($lower, 'cancelado')) {
            return ['estatus' => 'cancelado'];
        }

        // Log para detectar cambios en la respuesta del SAT
        \Illuminate\Support\Facades\Log::warning('SATService: respuesta no reconocida', [
            'body_preview' => mb_substr($body, 0, 500),
        ]);

        return ['estatus' => 'no_encontrado'];
    }
}
