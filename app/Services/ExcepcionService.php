<?php

namespace App\Services;

use App\Models\GastoExcepcion;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;


class ExcepcionService
{
    private const ACCIONES_VALIDAS = ['aprobado', 'rechazado'];

    public function resolver(
        GastoExcepcion $excepcion,
        $user,
        string $accion,
        ?string $comentario = null
    ): array {
        // ✅ Whitelist de acciones — antes de abrir transacción
        if (!in_array($accion, self::ACCIONES_VALIDAS, true)) {
            throw new \InvalidArgumentException(
                "Acción inválida: '{$accion}'. Valores aceptados: " . implode(', ', self::ACCIONES_VALIDAS)
            );
        }

        // ✅ Validación de permisos ANTES de abrir la transacción
        // Si el permiso falla, no tiene sentido haber iniciado una TX
        $this->validarPermisoPorNivel($user, $excepcion);

        return DB::transaction(function () use ($excepcion, $user, $accion, $comentario) {

            // Bloquea la fila para evitar resoluciones concurrentes
            $excepcion = GastoExcepcion::lockForUpdate()->findOrFail($excepcion->id);

            if ($excepcion->estatus !== 'pendiente') {
                throw new \Exception('La excepción ya fue procesada');
            }

            // ✅ Captura "antes" ANTES del update — getOriginal() es poco confiable
            // si el modelo fue hidratado en distintos momentos
            $antesExcepcion = $excepcion->toArray();

            $excepcion->update([
                'estatus'      => $accion,
                'comentario'   => $comentario,
                'aprobado_por' => $user->id,
                'resuelto_en'  => now(),
            ]);

            // Carga el gasto con sus relaciones para validar ownership y auditar
            $gasto = $excepcion->gasto()->with('solicitud.empleado')->firstOrFail();

            // ✅ Matriz clara nivel × acción — sin if/elseif entrelazados
            $mensaje = match (true) {
                // Nivel 1 aprobado → escalar a nivel 2
                $excepcion->nivel === 1 && $accion === 'aprobado' => $this->escalarNivel2($gasto),

                // Nivel 1 rechazado → gasto rechazado directamente
                $excepcion->nivel === 1 && $accion === 'rechazado' => $this->rechazarGasto($gasto),

                // Nivel 2 aprobado → gasto aprobado (decisión final)
                $excepcion->nivel === 2 && $accion === 'aprobado'  => $this->aprobarGasto($gasto),

                // Nivel 2 rechazado → gasto rechazado (decisión final)
                $excepcion->nivel === 2 && $accion === 'rechazado' => $this->rechazarGasto($gasto),

                default => throw new \Exception("Combinación de nivel/acción no manejada"),
            };

            // Auditoría de la excepción
            app(AuditoriaService::class)->registrar([
                'gasto_id'     => $gasto->id,
                'excepcion_id' => $excepcion->id,
                'evento'       => "excepcion_{$accion}",
                'antes'        => $antesExcepcion,
                // ✅ Sin fresh() — usamos los datos ya en memoria
                'despues'      => array_merge($antesExcepcion, [
                    'estatus'      => $accion,
                    'aprobado_por' => $user->id,
                    'resuelto_en'  => now()->toISOString(),
                ]),
            ]);

            return ['mensaje' => $mensaje];
        });
    }

    // -------------------------------------------------------------------------
    // Acciones sobre el gasto — separadas para claridad y reusabilidad
    // -------------------------------------------------------------------------

    private function escalarNivel2(\App\Models\Gasto $gasto): string
    {
        GastoExcepcion::create([
            'gasto_id' => $gasto->id,
            'nivel'    => 2,
            'estatus'  => 'pendiente',
        ]);

        app(AuditoriaService::class)->registrar([
            'gasto_id' => $gasto->id,
            'evento'   => 'excepcion_escalada_nivel2',
        ]);

        return 'Excepción escalada a nivel 2';
    }

    private function aprobarGasto(\App\Models\Gasto $gasto): string
    {
        $antes = $gasto->toArray();

        $gasto->update(['estatus' => 'aprobado']);

        app(AuditoriaService::class)->registrar([
            'gasto_id' => $gasto->id,
            'evento'   => 'gasto_aprobado_por_excepcion',
            'antes'    => $antes,
            'despues'  => ['estatus' => 'aprobado'],
        ]);

        return 'Excepción aprobada — gasto autorizado';
    }

    private function rechazarGasto(\App\Models\Gasto $gasto): string
    {
        $antes = $gasto->toArray();

        $gasto->update(['estatus' => 'rechazado']);

        app(AuditoriaService::class)->registrar([
            'gasto_id' => $gasto->id,
            'evento'   => 'gasto_rechazado_por_excepcion',
            'antes'    => $antes,
            'despues'  => ['estatus' => 'rechazado'],
        ]);

        return 'Gasto rechazado';
    }

    // -------------------------------------------------------------------------
    // Validación de permisos
    // -------------------------------------------------------------------------

    protected function validarPermisoPorNivel($user, GastoExcepcion $excepcion): void
    {
        $nivel = $excepcion->nivel;

        if ($nivel === 1) {
            if (!$user->hasRole('gerente')) {
                throw new AuthorizationException('Solo gerente puede aprobar nivel 1');
            }

            // ✅ Ownership: gerente solo puede resolver excepciones de su área
            $areaGasto = $excepcion->gasto?->solicitud?->empleado?->area_id;

            if ($areaGasto && $user->empleado?->area_id !== $areaGasto) {
                throw new AuthorizationException('Esta excepción no pertenece a tu área');
            }
        }

        if ($nivel === 2) {
            if (!$user->can('excepciones.aprobar.nivel2')) {
                throw new AuthorizationException('No tiene permiso para aprobar nivel 2');
            }
        }

        // Guard para niveles no definidos
        if (!in_array($nivel, [1, 2], true)) {
            throw new \Exception("Nivel de excepción no soportado: {$nivel}");
        }
    }
}
