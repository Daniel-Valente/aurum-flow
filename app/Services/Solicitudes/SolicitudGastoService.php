<?php

namespace App\Services\Solicitudes;

use App\Models\Gasto;
use App\Models\Solicitud;
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
        DB::transaction(function () use ($solicitud) {
            // Carga detalles si no están en memoria
            $detalles = $solicitud->relationLoaded('detalles')
                ? $solicitud->detalles
                : $solicitud->detalles()->get();

            if ($detalles->isEmpty()) {
                throw new \Exception('La solicitud no tiene detalles para generar gastos');
            }

            $now = now();

            // Insert masivo — un solo INSERT en lugar de N
            $gastos = $detalles->map(fn($d) => [
                'solicitud_id' => $solicitud->id,
                'concepto_id'  => $d->concepto_id,
                'fecha_gasto'  => $now,
                'monto'        => $d->monto_estimado,
                'estatus'      => 'pendiente',
                'created_at'   => $now,
                'updated_at'   => $now,
            ])->all();

            Gasto::insert($gastos);

            // Recarga solicitud con los gastos recién insertados Y relaciones
            // necesarias para que ValidadorGastosService no haga N+1
            $solicitud->load([
                'gastos.concepto',
                'empleado.user.roles',
            ]);

            app(ValidadorGastosService::class)->validarSolicitud($solicitud);
        });
    }
}
