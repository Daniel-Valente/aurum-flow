<?php

namespace App\Services\Solicitudes;

use App\Models\FlujoAprobacion;
use App\Models\Solicitud;
use App\Models\SolicitudAprobacion;
use App\Models\User;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class SolicitudAprobacionService
{
    /**
     * Registra la aprobación o rechazo de un usuario para una solicitud.
     * Después evalúa si ya se alcanzó el mínimo o si fue rechazada.
     */
    public function resolver(
        Solicitud $solicitud,
        $user,
        string $accion,
        ?string $comentario = null
    ): array {
        // Whitelist de acciones
        if (!in_array($accion, ['aprobado', 'rechazado'], true)) {
            throw new \InvalidArgumentException("Acción inválida: {$accion}");
        }

        if ($solicitud->estatus !== 'Pendiente') {
            throw new \Exception('La solicitud ya fue procesada');
        }

        $role = $user->roles->first();

        if (!$role) {
            throw new AuthorizationException('Sin rol asignado');
        }

        // Verifica que el rol esté en el flujo de aprobación
        $flujo = FlujoAprobacion::activo()
            ->tipo('viaticos')
            ->where('role_id', $role->id)
            ->first();

        if (!$flujo) {
            throw new AuthorizationException('Tu rol no forma parte del flujo de aprobación');
        }

        // Bloquea auto-aprobación
        if ($solicitud->empleado->user_id === $user->id) {
            throw new \App\Exceptions\Solicitudes\AutoAprobacionException();
        }

        DB::transaction(function () use ($solicitud, $user, $role, $accion, $comentario) {
            // updateOrCreate — si ya aprobó antes, actualiza (permite cambiar de opinión
            // mientras no haya resolución final)
            SolicitudAprobacion::updateOrCreate(
                [
                    'solicitud_id' => $solicitud->id,
                    'role_id'      => $role->id,
                ],
                [
                    'user_id'     => $user->id,
                    'accion'      => $accion,
                    'comentario'  => $comentario,
                    'created_at'  => now(),
                ]
            );

            app(AuditoriaService::class)->registrar([
                'solicitud_id' => $solicitud->id,
                'evento'       => $accion,
                'despues'      => [
                    'role'       => $role->name,
                    'comentario' => $comentario,
                ],
            ]);
        });

        return $this->evaluarResultado($solicitud->fresh());
    }

    /**
     * Evalúa si la solicitud ya alcanzó el mínimo de aprobaciones
     * o si fue rechazada por alguno de los aprobadores.
     *
     * Retorna: ['resultado' => 'pendiente|autorizado|rechazado', 'aprobadas' => N]
     */
    public function evaluarResultado(Solicitud $solicitud): array
    {
        $flujos = FlujoAprobacion::activo()->tipo('viaticos')->get();

        if ($flujos->isEmpty()) {
            throw new \Exception('No hay flujo de aprobación configurado para viáticos');
        }

        $minimo      = $flujos->first()->minimo_aprobaciones;
        $aprobaciones = SolicitudAprobacion::where('solicitud_id', $solicitud->id)->get();

        $totalAprobadas = $aprobaciones->where('accion', 'aprobado')->count();
        $hayRechazo     = $aprobaciones->where('accion', 'rechazado')->count() > 0;

        if ($hayRechazo) {
            $rechazadoPor = $aprobaciones->firstWhere('accion', 'rechazado');
            $motivo       = $rechazadoPor?->comentario;

            $solicitud->update([
                'estatus'        => 'Rechazado',
                'motivo_rechazo' => $motivo,
            ]);

            return ['resultado' => 'rechazado', 'aprobadas' => $totalAprobadas];
        }

        if ($totalAprobadas >= $minimo) {
            DB::transaction(function () use ($solicitud) {
                $solicitud->update(['estatus' => 'Autorizado']);
                app(SolicitudGastoService::class)->generarGastos($solicitud);
            });

            return ['resultado' => 'autorizado', 'aprobadas' => $totalAprobadas];
        }

        return [
            'resultado' => 'pendiente',
            'aprobadas' => $totalAprobadas,
            'minimo'    => $minimo,
            'faltan'    => $minimo - $totalAprobadas,
        ];
    }

    /**
     * Devuelve el estado de aprobación de cada rol en el flujo.
     * Usado por Show.php (step 2) y la vista de Autorizaciones.
     */
    public function aprobadoresDe(Solicitud $solicitud): array
    {
        $flujos = FlujoAprobacion::activo()
            ->tipo('viaticos')
            ->with('role')
            ->orderBy('orden')
            ->get();

        $aprobaciones = SolicitudAprobacion::where('solicitud_id', $solicitud->id)
            ->with('user:id,name')
            ->get()
            ->keyBy('role_id');

        $minimo     = $flujos->first()?->minimo_aprobaciones ?? 2;
        $aprobadas  = $aprobaciones->where('accion', 'aprobado')->count();

        return [
            'minimo'    => $minimo,
            'aprobadas' => $aprobadas,
            'faltan'    => max(0, $minimo - $aprobadas),
            'aprobadores' => $flujos->map(function ($flujo) use ($aprobaciones) {
                $ap = $aprobaciones->get($flujo->role_id);

                return [
                    'role_id'   => $flujo->role_id,
                    'rol'       => $flujo->role->name,
                    'nombre'    => $ap?->user?->name ?? ucfirst($flujo->role->name),
                    'aprobado'  => $ap?->accion === 'aprobado',
                    'rechazado' => $ap?->accion === 'rechazado',
                    'pendiente' => $ap === null,
                    'comentario'=> $ap?->comentario,
                    'fecha'     => $ap?->created_at?->format('d/m/Y H:i'),
                ];
            })->toArray(),
        ];
    }

    /**
     * Indica si el usuario autenticado puede aprobar/rechazar esta solicitud.
     * Útil para mostrar u ocultar botones en la vista de Autorizaciones.
     */
    public function puedeResolver(Solicitud $solicitud, $user): bool
    {
        if ($solicitud->estatus !== 'Pendiente') {
            return false;
        }

        // No puede aprobar la propia
        if ($solicitud->empleado->user_id === $user->id) {
            return false;
        }

        $roleId = $user->roles->first()?->id;

        if (!$roleId) {
            return false;
        }

        // El rol debe estar en el flujo y no haber resuelto ya
        $estaEnFlujo = FlujoAprobacion::activo()
            ->tipo('viaticos')
            ->where('role_id', $roleId)
            ->exists();

        if (!$estaEnFlujo) {
            return false;
        }

        // Ya resolvió
        $yaResolvio = SolicitudAprobacion::where('solicitud_id', $solicitud->id)
            ->where('role_id', $roleId)
            ->exists();

        return !$yaResolvio;
    }
}
