<?php

namespace App\Services\CFDI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SATService
{
    public function validar(array $cfdi): array
    {
        $key = 'sat_cfdi_' . $cfdi['uuid'];

        return Cache::remember($key, now()->addHours(12), function () use ($cfdi) {
            return $this->consultarSAT($cfdi);
        });
    }

    protected function consultarSAT(array $cfdi): array
    {
        $url = $this->buildUrl($cfdi);

        $response = Http::retry(3, 200)
            ->timeout(10)
            ->get($url);

        if (!$response->ok()) {
            throw new \Exception('Error SAT');
        }

        return $this->parseResponse($response->body());
    }

    protected function buildUrl(array $cfdi): string
    {
        return 'https://consultaqr.facturaelectronica.sat.gob.mx/default.aspx?' . http_build_query([
            'id' => $cfdi['uuid'],
            're' => $cfdi['rfc_emisor'],
            'rr' => $cfdi['rfc_receptor'],
            'tt' => number_format($cfdi['total'], 6, '.', ''),
            'fe' => substr(md5($cfdi['uuid']), -8), // simulación parcial
        ]);
    }

    protected function parseResponse(string $html): array
    {
        if (str_contains($html, 'Vigente')) {
            return ['estatus' => 'vigente'];
        }

        if (str_contains($html, 'Cancelado')) {
            return ['estatus' => 'cancelado'];
        }

        return ['estatus' => 'no_encontrado'];
    }
}
