<?php

namespace App\Services\Solicitudes;

use App\Helpers\FolioHelper;
use App\Models\ComprobacionTarjeta;
use App\Models\Gasto;
use App\Models\Solicitud;
use App\Services\Auditoria\AuditoriaService;
use App\Services\Gasto\ValidadorGastosService;
use Illuminate\Support\Facades\DB;

class SolicitudGastoService
{
    /**
     * Único punto de creación de gastos a partir de una solicitud.
     * SolicitudService::resolver() y ::aprobar() delegan aquí.
     * No duplicar esta lógica en ningún otro service.
     */
    public function generarGastos(Solicitud $solicitud): void
    {
        $detalles = $solicitud->detalles()->with('concepto')->get();
        $now     = now();
        $rows    = [];

        foreach ($detalles as $detalle) {
            $montoGasto = (float) $detalle->monto_estimado;
            if ($detalle->requiere_extension_tarjeta && $detalle->monto_extension_tarjeta > 0) {
                $montoGasto = $montoGasto - (float) $detalle->monto_extension_tarjeta;
            }

            $rows[] = [
                'solicitud_id' => $solicitud->id,
                'concepto_id'  => $detalle->concepto_id,
                'fecha_gasto'  => $solicitud->fecha_inicio,
                'monto'        => $montoGasto,
                'estatus'      => 'pendiente',
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        Gasto::insert($rows);
        $detallesConExtension = $detalles->filter(
            fn($d) => $d->requiere_extension_tarjeta && $d->monto_extension_tarjeta > 0
        );

        if ($detallesConExtension->isNotEmpty()) {
            $this->crearExtensionAutomatica($solicitud, $detallesConExtension);
        }
    }

    private function crearExtensionAutomatica(Solicitud $solicitud, $detalles): void
    {
        $comprobacion = ComprobacionTarjeta::create([
            'folio'        => FolioHelper::generar('CT'),
            'empleado_id'  => $solicitud->empleado_id,
            'proyecto_id'  => $solicitud->proyecto_id,
            'solicitud_id' => $solicitud->id,
            'fecha_inicio' => $solicitud->fecha_inicio,
            'fecha_fin'    => $solicitud->fecha_fin,
            'descripcion'  => "Extensión automática de {$solicitud->folio}",
            'monto_total'  => $detalles->sum('monto_extension_tarjeta'),
            'es_extension' => true,
            'estatus'      => 'abierta',
        ]);

        $now  = now();
        $rows = [];

        foreach ($detalles as $detalle) {
            $rows[] = [
                'comprobacion_tarjeta_id' => $comprobacion->id,
                'concepto_id'             => $detalle->concepto_id,
                'fecha_gasto'             => $solicitud->fecha_inicio,
                'monto'                   => (float) $detalle->monto_extension_tarjeta,
                'estatus'                 => 'pendiente',
                'created_at'              => $now,
                'updated_at'              => $now,
            ];
        }

        Gasto::insert($rows);

        app(AuditoriaService::class)->registrar([
            'gasto_id' => null,
            'evento'   => 'extension_tarjeta_generada_automaticamente',
            'despues'  => [
                'solicitud_id'    => $solicitud->id,
                'comprobacion_id' => $comprobacion->id,
                'folio'           => $comprobacion->folio,
                'conceptos'       => $detalles->pluck('concepto_id')->all(),
            ],
        ]);
    }
}
