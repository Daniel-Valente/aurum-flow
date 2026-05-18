<?php

namespace App\Observers;

use App\Models\GastoComprobante;
use App\Services\Auditoria\ActividadLogService;

class GastoComprobanteObserver
{
    public function __construct(
        private ActividadLogService $actividadLog
    ) {}

    /**
     * Handle the GastoComprobante "created" event.
     */
    public function created(GastoComprobante $comprobante): void
    {
        $this->actividadLog->registrar([
            'user' => auth()->user(),
            'evento' => 'created',
            'modulo' => 'comprobantes',
            'entidad' => $comprobante,
            'entidad_descripcion' => "Comprobante subido para gasto {$comprobante->gasto_id}",
            'datos_despues' => [
                'tipo' => $comprobante->tipo,
                'monto' => $comprobante->monto,
                'uuid' => $comprobante->uuid,
            ],
            'severidad' => 'info',
        ]);
    }

    /**
     * Handle the GastoComprobante "updated" event.
     */
    public function updated(GastoComprobante $comprobante): void
    {
        // Registrar cambios en validación
        $cambiosValidacion = $comprobante->wasChanged([
            'validacion_manual',
            'validado_por',
            'sat_status',
        ]);

        if ($cambiosValidacion) {
            $cambios = $comprobante->getChanges();
            $original = $comprobante->getOriginal();

            $evento = match(true) {
                isset($cambios['validacion_manual']) && $cambios['validacion_manual'] === 'aprobado' => 'approved',
                isset($cambios['validacion_manual']) && $cambios['validacion_manual'] === 'rechazado' => 'rejected',
                default => 'updated',
            };

            $this->actividadLog->registrar([
                'user' => auth()->user(),
                'evento' => $evento,
                'modulo' => 'comprobantes',
                'entidad' => $comprobante,
                'entidad_descripcion' => "Comprobante {$comprobante->id} " .
                    ($evento === 'approved' ? 'aprobado' : ($evento === 'rejected' ? 'rechazado' : 'actualizado')),
                'datos_antes' => array_intersect_key($original, $cambios),
                'datos_despues' => $cambios,
                'severidad' => $evento === 'rejected' ? 'warning' : 'info',
                'es_sensible' => true,
            ]);
        }
    }

    /**
     * Handle the GastoComprobante "deleted" event.
     */
    public function deleted(GastoComprobante $comprobante): void
    {
        $this->actividadLog->registrar([
            'user' => auth()->user(),
            'evento' => 'deleted',
            'modulo' => 'comprobantes',
            'entidad' => $comprobante,
            'entidad_descripcion' => "Comprobante {$comprobante->id} eliminado",
            'datos_antes' => [
                'tipo' => $comprobante->tipo,
                'monto' => $comprobante->monto,
            ],
            'severidad' => 'warning',
            'es_sensible' => true,
        ]);
    }
}
