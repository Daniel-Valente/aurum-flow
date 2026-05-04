<?php

namespace App\Services\Gasto;

use App\Models\GastoExcepcion;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class GastoExcepcionService
{
    public function resolver(
        GastoExcepcion $excepcion,
        $user,
        string $accion,
        ?string $comentario = null
    ): void {
        if (!in_array($accion, ['aprobado', 'rechazado'], true)) {
            throw new \InvalidArgumentException("Acción inválida: {$accion}");
        }

        if ($excepcion->estatus !== 'pendiente') {
            throw new \Exception('Esta excepción ya fue resuelta');
        }

        $rolNombre = $user->roles->first()?->name;

        // Valida que el rol corresponda al nivel
        $rolesPermitidos = match($excepcion->nivel) {
            1 => ['manager'],
            2 => ['admin'],
            default => [],
        };

        if (!in_array($rolNombre, $rolesPermitidos, true)) {
            throw new AuthorizationException(
                "Tu rol ({$rolNombre}) no puede resolver excepciones de Nivel {$excepcion->nivel}"
            );
        }

        DB::transaction(function () use ($excepcion, $user, $accion, $comentario) {
            $excepcion->update([
                'estatus'      => $accion,
                'aprobado_por' => $user->id,
                'comentario'   => $comentario,
                'resuelto_en'  => now(),
            ]);

            if ($accion === 'rechazado') {
                // Cualquier nivel que rechace → gasto rechazado definitivamente
                $excepcion->gasto->update(['estatus' => 'rechazado']);
                return;
            }

            // Aprobado
            if ($excepcion->nivel === 1) {
                // Escala a N2
                GastoExcepcion::create([
                    'gasto_id' => $excepcion->gasto_id,
                    'nivel'    => 2,
                    'estatus'  => 'pendiente',
                ]);
                // El gasto sigue en 'excepcion' hasta que N2 lo cierre
            } else {
                // N2 aprobado → gasto aprobado definitivamente
                $excepcion->gasto->update(['estatus' => 'aprobado']);
            }
        });
    }
}
