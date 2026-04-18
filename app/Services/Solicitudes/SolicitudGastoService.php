<?php

namespace App\Services\Solicitudes;

use App\Models\Gasto;
use App\Models\Solicitud;
use App\Services\Gasto\ValidadorGastosService;

class SolicitudGastoService
{
    public function generarGastos(Solicitud $solicitud)
    {
        foreach ($solicitud->detalles as $detalle) {

            Gasto::create([
                'solicitud_id' => $solicitud->id,
                'concepto_id' => $detalle->concepto_id,
                'fecha_gasto' => now(),
                'monto' => $detalle->monto_estimado,
                'estatus' => 'pendiente'
            ]);
        }

        app(ValidadorGastosService::class)
            ->validarSolicitud($solicitud);
    }
}
