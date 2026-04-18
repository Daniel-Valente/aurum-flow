<?php

namespace App\Services;

use App\Models\GastoExcepcion;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ExcepcionService
{
    public function resolver(GastoExcepcion $excepcion, $user, string $accion, ?string $comentario = null)
    {
        return DB::transaction(function () use ($excepcion, $user, $accion, $comentario) {

            $this->validarPermisoPorNivel($user, $excepcion);

            if ($excepcion->estatus !== 'pendiente') {
                throw new \Exception('La excepción ya fue procesada');
            }

            $antes = $excepcion->toArray();

            $excepcion->update([
                'estatus' => $accion, // aprobado | rechazado
                'comentario' => $comentario,
                'aprobado_por' => $user->id,
                'resuelto_en' => now()
            ]);

            $gasto = $excepcion->gasto;

            // 🔥 NIVEL 1 → escalar
            if ($accion === 'aprobado' && $excepcion->nivel === 1) {

                GastoExcepcion::create([
                    'gasto_id' => $gasto->id,
                    'nivel' => 2,
                    'estatus' => 'pendiente'
                ]);

                $mensaje = 'Excepción escalada a nivel 2';
            }

            // 🔥 NIVEL 2 → decisión final
            elseif ($excepcion->nivel === 2) {
                $antesGasto = $gasto->getOriginal();

                $gasto->update([
                    'estatus' => $accion === 'aprobado' ? 'aprobado' : 'rechazado'
                ]);

                app(AuditoriaService::class)->registrar([
                    'gasto_id' => $gasto->id,
                    'evento' => 'estatus_actualizado',
                    'antes' => $antesGasto,
                    'despues' => $gasto->fresh()->toArray()
                ]);

                $mensaje = 'Excepción finalizada';
            }

            // 🔥 Rechazo en nivel 1
            elseif ($accion === 'rechazado') {
                $gasto->update([
                    'estatus' => 'rechazado'
                ]);

                $mensaje = 'Gasto rechazado';
            } else {
                $mensaje = 'Proceso completado';
            }

            app(AuditoriaService::class)->registrar([
                'gasto_id' => $gasto->id,
                'excepcion_id' => $excepcion->id,
                'evento' => $accion,
                'antes' => $antes,
                'despues' => $excepcion->fresh()->toArray()
            ]);

            return ['mensaje' => $mensaje];
        });
    }

    protected function validarPermisoPorNivel($user, GastoExcepcion $excepcion)
    {
        $nivel = $excepcion->nivel;

        if ($nivel === 1 && !$user->hasRole('gerente')) {
            throw new AuthorizationException('Solo gerente puede aprobar nivel 1');
        }

        if ($nivel === 2 && !$user->can('excepciones.aprobar.nivel2')) {
            throw new AuthorizationException('No tiene permiso para aprobar nivel 2');
        }
    }
}
