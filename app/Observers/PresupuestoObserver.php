<?php

namespace App\Observers;

use App\Models\Presupuesto;
use App\Services\Auditoria\ActividadLogService;

class PresupuestoObserver
{
    public function __construct(
        private ActividadLogService $actividadLog
    ) {}

    /**
     * Handle the Presupuesto "updated" event.
     */
    public function updated(Presupuesto $presupuesto): void
    {
        // Registrar solo cambios importantes
        $cambiosImportantes = $presupuesto->wasChanged([
            'monto_total',
            'monto_gastado',
            'monto_comprometido',
            'estatus',
        ]);

        if ($cambiosImportantes) {
            $cambios = $presupuesto->getChanges();
            $original = $presupuesto->getOriginal();

            $this->actividadLog->registrar([
                'user' => auth()->user(),
                'evento' => 'updated',
                'modulo' => 'presupuestos',
                'entidad' => $presupuesto,
                'entidad_descripcion' => "Presupuesto {$presupuesto->codigo} actualizado",
                'datos_antes' => array_intersect_key($original, $cambios),
                'datos_despues' => $cambios,
                'severidad' => 'info',
                'es_sensible' => true,
            ]);
        }
    }
}
