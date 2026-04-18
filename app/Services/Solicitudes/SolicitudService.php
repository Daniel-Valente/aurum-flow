<?php

namespace App\Services\Solicitudes;

use App\Models\Gasto;
use App\Models\Solicitud;
use App\Models\SolicitudDetalle;
use App\Services\Auditoria\AuditoriaService;
use App\Services\Gasto\ValidadorGastosService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class SolicitudService
{
    public function create(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $folio = 'SQL-' . now()->format('YmdHis');

            $solicitud = Solicitud::create([
                'folio' => $folio,
                'empleado_id' => $user->empleado->id,
                'area_id' => $user->empleado->area_id,
                'proyecto_id' => $data['proyecto_id'] ?? null,
                'fecha_inicio' => $data['fecha_inicio'] ?? null,
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'motivo' => $data['motivo'] ?? null,
                'estatus' => 'Borrador',
            ]);

            return $solicitud;
        });
    }

    public function agregarDetalle(Solicitud $solicitud, array $detalles)
    {
        return DB::transaction(function () use ($solicitud, $detalles) {

            foreach ($detalles as $detalle) {
                SolicitudDetalle::create([
                    'solicitud_id' => $solicitud->id,
                    'concepto_id' => $detalle['concepto_id'],
                    'monto_estimado' => $detalle['monto_estimado'],
                ]);
            }

            $total = $solicitud->detalles()->sum('monto_estimado');

            $solicitud->update([
                'monto_total' => $total
            ]);

            return $solicitud->fresh('detalles');
        });
    }

    public function enviar(Solicitud $solicitud, $user)
    {
        if ($solicitud->estatus !== 'Borrador') {
            throw new \Exception('Solo solicitudes en borrador pueden enviarse');
        }

        if ($user->empleado->id !== $solicitud->empleado_id) {
            throw new AuthorizationException('No es su solicitud');
        }

        if ($solicitud->detalles()->count() === 0) {
            throw new \Exception('Debe agregar al menos un concepto');
        }

        $solicitud->update([
            'estatus' => 'Pendiente'
        ]);

        return $solicitud;
    }

    public function resolver(Solicitud $solicitud, string $accion, ?string $motivo = null)
    {
        if ($solicitud->estatus !== 'Pendiente') {
            throw new \Exception('Solicitud ya procesada');
        }

        if ($accion === 'rechazado') {
            $solicitud->update([
                'estatus' => 'Rechazado',
                'motivo_rechazo' => $motivo
            ]);

            return $solicitud;
        }

        $solicitud->update([
            'estatus' => 'Autorizado'
        ]);

        app(SolicitudGastoService::class)->generarGastos($solicitud);

        return $solicitud;
    }

    public function cancelar(Solicitud $solicitud, $user, ?string $motivo = null)
    {
        if (!in_array($solicitud->status, ['Borrador', 'Pendiente'])) {
            throw new \Exception('No se puede cancelar esta solicitud');
        }

        if ($user->empleado?->id !== $solicitud->empleado_id && !$user->can('solicitudes.eliminar')) {
            throw new AuthorizationException('No autorizado');
        }

        $solicitud->update([
            'estatus' => 'Cancelado',
            'motivo_cancelacion' => $motivo
        ]);

        app(AuditoriaService::class)->registrar([
            'solicitud_id' => $solicitud->id,
            'evento' => 'cancelado',
            'actor_id' => $user->id
        ]);

        return $solicitud;
    }

    public function reabrir(Solicitud $solicitud, $user)
    {
        if (!in_array($solicitud->estatus, ['Rechazado', 'Cancelado'])) {
            throw new \Exception('No se puede reabrir');
        }

        if ($user->empleado?->id !== $solicitud->empleado_id) {
            throw new AuthorizationException('Solo el dueño puede reabrir');
        }

        $solicitud->update([
            'estatus' => 'Borrador'
        ]);

        app(AuditoriaService::class)->registrar([
            'solicitud_id' => $solicitud->id,
            'evento' => 'reabierto',
            'actor_id' => $user->id,
        ]);

        return $solicitud;
    }

    public function aprobar(Solicitud $solicitud, $user)
    {
        if ($solicitud->estatus !== 'Pendiente') {
            throw new \Exception('Solicitud no válida para aprobación');
        }

        if (!$user->can('solicitudes.aprobar')) {
            throw new AuthorizationException('No autorizado');
        }

        DB::transaction(function () use ($solicitud) {
            if ($solicitud->gastos()->lockForUpdate()->exists()) {
                throw new \Exception('La solicitud ya tiene gastos generados');
            }

            // 🔥 Cambiar estatus
            $solicitud->update([
                'estatus' => 'Autorizado'
            ]);

            // 🔥 Generar gastos
            foreach ($solicitud->detalles as $detalle) {

                Gasto::create([
                    'solicitud_id' => $solicitud->id,
                    'concepto_id' => $detalle->concepto_id,
                    'monto' => $detalle->monto_estimado,
                    'fecha_gasto' => now(),
                    'estatus' => 'pendiente'
                ]);
            }

            // 🔥 Validación automática
            app(ValidadorGastosService::class)
                ->validarSolicitud($solicitud->fresh()->load('gastos.concepto'));
        });

        return $solicitud->load('gastos');
    }
}
