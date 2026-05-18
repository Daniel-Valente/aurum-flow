<?php

namespace App\Services\CFDI;

use App\Models\ConfiguracionEmpresa;
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
        $xmlContent = file_get_contents($file->getRealPath());
        $xmlContent = Cleaner::staticClean($xmlContent);
        try {
            $jsonString = JsonConverter::convertToJson($xmlContent);
            $cfdiData = json_decode($jsonString, true);
            if (!is_array($cfdiData)) {
                throw new \Exception('Error al decodificar la estructura JSON del CFDI.');
            }
        } catch (\Throwable $e) {
            throw new \Exception('XML inválido o no convertible: ' . $e->getMessage());
        }

        $version = (string) ($cfdiData['Version'] ?? '');
        $tfd = $cfdiData['Complemento']['TimbreFiscalDigital'] ?? null;

        if (!$tfd) {
            throw new \Exception('No se encontró el Timbre Fiscal Digital');
        }

        $uuid = (string) ($tfd['UUID'] ?? '');
        if (empty($uuid)) {
            throw new \Exception('UUID vacío en el CFDI');
        }

        $rfcEmisor   = strtoupper((string) ($cfdiData['Emisor']['Rfc'] ?? ''));
        $rfcReceptor = strtoupper((string) ($cfdiData['Receptor']['Rfc'] ?? ''));
        $totalCfdi   = (float) ($cfdiData['Total'] ?? 0.0);
        $subTotalCfdi    = (float) ($cfdiData['SubTotal'] ?? 0.0);
        $fecha       = (string) ($cfdiData['Fecha'] ?? '');
        $estadoSat = 'pendiente';

        try {
            $client = new SoapConsumerClient();
            $consumer = new Consumer($client);

            $simpleExpression = "?re=$rfcEmisor&rr=$rfcReceptor&tt=$totalCfdi&id=$uuid";

            $response = $consumer->execute($simpleExpression);
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

        /*
        $config = $this->obtenerConfiguracion($gasto);

        if ($config->validar_rfc_receptor && $config->rfc_empresa) {
            $rfcEmpresa = strtoupper($config->rfc_empresa);
            if ($rfcReceptor !== $rfcEmpresa) {
                throw new \Exception(
                    "RFC receptor incorrecto. Esperado: {$rfcEmpresa}, Recibido: {$rfcReceptor}"
                );
            }
        }*/

        if (abs($totalCfdi - $gasto->monto) > 0.01) {
            \Log::warning('Monto CFDI difiere del gasto estimado', [
                'cfdi' => $totalCfdi,
                'gasto' => $gasto->monto,
            ]);
        }

        $impuestos = $this->extraerImpuestos($cfdiData);

        return [
            'uuid'         => strtoupper($uuid),
            'version'      => $version,
            'version_cfdi' => $version,
            'emisor'       => $rfcEmisor,
            'rfc_emisor'   => $rfcEmisor,
            'receptor'     => $rfcReceptor,
            'rfc_receptor' => $rfcReceptor,
            'total'        => $totalCfdi,
            'subtotal'     => $subTotalCfdi,
            'fecha'        => $fecha,
            'estado_sat'   => $estadoSat,

            'iva'          => $impuestos['iva'],
            'ieps'         => $impuestos['ieps'],
            'ish'          => $impuestos['ish'],
            'tasa_iva'     => $impuestos['tasa_iva'],
            'tasa_ieps'    => $impuestos['tasa_ieps'],
            'tasa_ish'     => $impuestos['tasa_ish'],
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
            'subtotal'      => (float) ($atributos['Subtotal'] ?? 0),
            'emisor_rfc'    => (string) ($cfdi->Emisor->attributes()['Rfc'] ?? ''),
            'emisor_nombre' => (string) ($cfdi->Emisor->attributes()['Nombre'] ?? ''),
            'receptor_rfc'  => (string) ($cfdi->Receptor->attributes()['Rfc'] ?? ''),
            'fecha'         => substr((string) ($atributos['Fecha'] ?? ''), 0, 10),
            'estado_sat'    => 'pendiente',
        ];
    }

    private function extraerImpuestos(array $cfdiData): array
    {
        $resultado = [
            'iva'       => 0.0,
            'ieps'      => 0.0,
            'ish'       => 0.0,
            'tasa_iva'  => null,
            'tasa_ieps' => null,
            'tasa_ish'  => null,
        ];

        $traslados = $cfdiData['Impuestos']['Traslados']['Traslado'] ?? [];
        if (isset($traslados['Impuesto'])) {
            $traslados = [$traslados];
        }

        foreach ($traslados as $traslado) {
            $impuesto = (string) ($traslado['Impuesto'] ?? '');
            $importe  = (float) ($traslado['Importe'] ?? 0);
            $tasa     = isset($traslado['TasaOCuota'])
                ? (float) $traslado['TasaOCuota']
                : null;

            match ($impuesto) {
                '002' => $resultado['iva'] += $importe,   // IVA
                '003' => $resultado['ieps'] += $importe,  // IEPS
                default => null,
            };
            if ($impuesto === '002' && $resultado['tasa_iva'] === null) {
                $resultado['tasa_iva'] = $tasa;
            }
            if ($impuesto === '003' && $resultado['tasa_ieps'] === null) {
                $resultado['tasa_ieps'] = $tasa;
            }
        }

        $trasladosLocales = $cfdiData['Complemento']['ImpuestosLocales']['TrasladosLocales'] ?? [];

        if (isset($trasladosLocales['ImpLocTrasladado'])) {
            $trasladosLocales = [$trasladosLocales];
        }

        foreach ($trasladosLocales as $local) {
            $impuestoLocal = strtoupper((string) ($local['ImpLocTrasladado'] ?? ''));

            if ($impuestoLocal === 'ISH') {
                $resultado['ish'] = (float) ($local['Importe'] ?? 0);
                $resultado['tasa_ish'] = isset($local['TasadeTraslado'])
                    ? (float) $local['TasadeTraslado']
                    : null;
            }
        }

        return $resultado;
    }

    private function obtenerConfiguracion(Gasto $gasto): ConfiguracionEmpresa
    {
        $empresa = $gasto->empleado?->empresa;
        return ConfiguracionEmpresa::obtenerPorEmpresa($empresa);
    }
}
