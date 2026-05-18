<?php

namespace App\Observers;

use App\Models\Solicitud;
use App\Services\Auditoria\ActividadLogService;

class SolicitudObserver
{
    public function __construct(
        private ActividadLogService $actividadLog
    ) {}

    public function created(Solicitud $solicitud): void
    {
        $this->actividadLog->registrar([
            'user' => auth()->user(),
            'evento' => 'created',
            'modulo' => 'solicitudes',
            'entidad' => $solicitud,
            'entidad_descripcion' => "Solicitud {$solicitud->folio} creada",
            'datos_despues' => [
                'folio' => $solicitud->folio,
                'monto_total' => $solicitud->monto_total,
                'destino' => $solicitud->destino,
            ],
            'severidad' => 'info',
        ]);
    }

    public function updated(Solicitud $solicitud): void
    {
        $cambiosImportantes = $solicitud->wasChanged([
            'estatus',
            'monto_total',
            'destino',
            'fecha_inicio',
            'fecha_fin',
        ]);

        if ($cambiosImportantes) {
            $cambios = $solicitud->getChanges();
            $original = $solicitud->getOriginal();

            $this->actividadLog->registrar([
                'user' => auth()->user(),
                'evento' => 'updated',
                'modulo' => 'solicitudes',
                'entidad' => $solicitud,
                'entidad_descripcion' => "Solicitud {$solicitud->folio} actualizada",
                'datos_antes' => array_intersect_key($original, $cambios),
                'datos_despues' => $cambios,
                'severidad' => 'info',
            ]);
        }
    }

    public function deleted(Solicitud $solicitud): void
    {
        $this->actividadLog->registrar([
            'user' => auth()->user(),
            'evento' => 'deleted',
            'modulo' => 'solicitudes',
            'entidad' => $solicitud,
            'entidad_descripcion' => "Solicitud {$solicitud->folio} eliminada",
            'datos_antes' => [
                'folio' => $solicitud->folio,
                'monto_total' => $solicitud->monto_total,
                'estatus' => $solicitud->estatus,
            ],
            'severidad' => 'warning',
            'es_sensible' => true,
        ]);
    }
}
