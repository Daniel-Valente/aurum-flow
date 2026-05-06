<?php

namespace App\Services\CFDI;

use App\Models\Gasto;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PhpCfdi\CfdiCleaner\Cleaner;
use PhpCfdi\CfdiToJson\JsonConverter;
use PhpCfdi\SatEstadoCfdi\Clients\Soap\SoapConsumerClient;
use PhpCfdi\SatEstadoCfdi\Consumer;

class CFDIService
{
    public function parse(TemporaryUploadedFile $file, Gasto $gasto): array
    {
        // 1. Leer archivo
        $xmlContent = file_get_contents($file->getRealPath());

        // 2. Limpiar XML usando phpcfdi/cfdi-cleaner (Namespace correcto)
        $xmlContent = Cleaner::staticClean($xmlContent);

        // 3. Cargar y parsear CFDI a estructura de Array nativo
        try {
            // Convertimos el XML a un string JSON válido
            $jsonString = JsonConverter::convertToJson($xmlContent);

            // CONVERSIÓN CLAVE: Transformamos el string JSON en un Array asociativo de PHP
            $cfdiData = json_decode($jsonString, true);

            if (!is_array($cfdiData)) {
                throw new \Exception('Error al decodificar la estructura JSON del CFDI.');
            }
        } catch (\Throwable $e) {
            throw new \Exception('XML inválido o no convertible: ' . $e->getMessage());
        }

        // 4. Extraer datos base desde el Array de phpCfdi
        $version = (string) ($cfdiData['Version'] ?? '');

        // Extracción del Timbre Fiscal Digital de forma unificada (independiente de la versión)
        $tfd = $cfdiData['Complemento']['TimbreFiscalDigital'] ?? null;

        if (!$tfd) {
            throw new \Exception('No se encontró el Timbre Fiscal Digital');
        }

        $uuid = (string) ($tfd['UUID'] ?? '');
        if (empty($uuid)) {
            throw new \Exception('UUID vacío en el CFDI');
        }

        // Normalizaciones de datos
        $rfcEmisor   = strtoupper((string) ($cfdiData['Emisor']['Rfc'] ?? ''));
        $rfcReceptor = strtoupper((string) ($cfdiData['Receptor']['Rfc'] ?? ''));
        $totalCfdi   = (float) ($cfdiData['Total'] ?? 0.0);
        $fecha       = (string) ($cfdiData['Fecha'] ?? '');

        // 5. Validación SAT (Se actualizaron los nombres de métodos a la API actual de phpCfdi)
        $estadoSat = 'pendiente';

        try {
            $client = new SoapConsumerClient();
            $consumer = new Consumer($client);

            $simpleExpression = "?re=$rfcEmisor&rr=$rfcReceptor&tt=$totalCfdi&id=$uuid";

            $response = $consumer->execute($simpleExpression);

            // ✅ Forma correcta usando helpers
            if ($response->document->isActive()) {
                $estadoSat = 'vigente';
            } elseif ($response->document->isCancelled()) {
                $estadoSat = 'cancelado';
            } else {
                $estadoSat = 'no_encontrado';
            }

            if ($estadoSat !== 'vigente') {
                throw new \Exception("El CFDI no está vigente en el SAT. Estado: {$estadoSat}");
            }

        } catch (\Throwable $e) {
            \Log::error("Error consultando SAT para CFDI {$uuid}: " . $e->getMessage());
            $estadoSat = 'error_consulta';
        }
        // 6. Validar RFC receptor
        $rfcEmpresa = strtoupper(config('app.rfc_empresa'));
        /*if ($rfcReceptor !== $rfcEmpresa) {
            throw new \Exception("RFC receptor incorrecto. Esperado: {$rfcEmpresa}, Recibido: {$rfcReceptor}");
        }*/

        // 7. Validar monto vs gasto
        if (abs($totalCfdi - $gasto->monto) > 0.01) {
            \Log::warning('Monto CFDI difiere del gasto estimado', [
                'cfdi' => $totalCfdi,
                'gasto' => $gasto->monto,
            ]);
        }

        // 8. RETURN COMPLETO (Idéntico al original para mantener compatibilidad)
        return [
            'uuid'         => strtoupper($uuid),
            'version'      => $version,
            'version_cfdi' => $version,
            'emisor'       => $rfcEmisor,
            'rfc_emisor'   => $rfcEmisor,
            'receptor'     => $rfcReceptor,
            'rfc_receptor' => $rfcReceptor,
            'total'        => $totalCfdi,
            'fecha'        => $fecha,
            'estado_sat'   => $estadoSat,
        ];
    }

    public function parseTemporary(TemporaryUploadedFile $file): array
    {
        $xml = simplexml_load_string(file_get_contents($file->getRealPath()));

        if (!$xml) {
            throw new \Exception('El archivo no es un XML válido.');
        }

        $ns   = $xml->getNamespaces(true);
        $cfdi = $xml->children($ns['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4');

        // Compatibilidad CFDI 3.3 y 4.0
        $atributos = $xml->attributes();

        return [
            'uuid'          => (string) ($xml->children($ns['tfd'] ?? '')
                                ->attributes()['UUID'] ?? ''),
            'total'         => (float) ($atributos['Total'] ?? 0),
            'emisor_rfc'    => (string) ($cfdi->Emisor->attributes()['Rfc'] ?? ''),
            'emisor_nombre' => (string) ($cfdi->Emisor->attributes()['Nombre'] ?? ''),
            'receptor_rfc'  => (string) ($cfdi->Receptor->attributes()['Rfc'] ?? ''),
            'fecha'         => substr((string) ($atributos['Fecha'] ?? ''), 0, 10),
            'estado_sat'    => 'pendiente',
        ];
    }
}
