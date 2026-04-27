<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use App\Models\SolicitudDetalle;
use App\Services\Auditoria\AuditoriaService;
use App\Services\Solicitudes\SolicitudGastoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class SolicitudService
{
    public function create(array $data, $user): Solicitud
    {
        return DB::transaction(function () use ($data, $user) {
            // Secuencia de PostgreSQL — sin colisiones en concurrencia
            // Crea esta secuencia una sola vez en una migration:
            //   DB::statement("CREATE SEQUENCE IF NOT EXISTS solicitudes_folio_seq START 1");
            $seq   = DB::selectOne("SELECT nextval('solicitudes_folio_seq') AS val")->val;
            $folio = 'SQL-' . str_pad($seq, 6, '0', STR_PAD_LEFT);

            return Solicitud::create([
                'folio'        => $folio,
                'empleado_id'  => $user->empleado->id,   // siempre del servidor, nunca del request
                'area_id'      => $user->empleado->area_id,
                'proyecto_id'  => $data['proyecto_id']  ?? null,
                'fecha_inicio' => $data['fecha_inicio'] ?? null,
                'fecha_fin'    => $data['fecha_fin']    ?? null,
                'motivo'       => $data['motivo']       ?? null,
                'estatus'      => 'Borrador',            // siempre del servidor
            ]);
        });
    }

    public function agregarDetalle(Solicitud $solicitud, array $detalles): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $detalles) {
            // Insert masivo — un solo INSERT en lugar de N
            $now  = now();
            $rows = array_map(fn($d) => [
                'solicitud_id'   => $solicitud->id,
                'concepto_id'    => $d['concepto_id'],
                'monto_estimado' => $d['monto_estimado'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ], $detalles);

            SolicitudDetalle::insert($rows);

            // Recalcula total desde DB (no confiar en la colección en memoria)
            $total = SolicitudDetalle::where('solicitud_id', $solicitud->id)
                ->sum('monto_estimado');

            $solicitud->update(['monto_total' => $total]);

            return $solicitud->fresh('detalles');
        });
    }

    public function enviar(Solicitud $solicitud, $user): Solicitud
    {
        if ($solicitud->estatus !== 'Borrador') {
            throw new \Exception('Solo solicitudes en borrador pueden enviarse');
        }

        if ($user->empleado->id !== $solicitud->empleado_id) {
            throw new AuthorizationException('No es su solicitud');
        }

        // Evita query count si la relación ya está en memoria
        $sinDetalles = $solicitud->relationLoaded('detalles')
            ? $solicitud->detalles->isEmpty()
            : !$solicitud->detalles()->exists();

        if ($sinDetalles) {
            throw new \Exception('Debe agregar al menos un concepto');
        }

        $solicitud->update(['estatus' => 'Pendiente']);

        return $solicitud;
    }

    public function resolver(Solicitud $solicitud, string $accion, ?string $motivo, $user): Solicitud
    {
        // ✅ Validación de permisos en el service, no solo en el caller
        if (!$user->can('solicitudes.resolver')) {
            throw new AuthorizationException('No autorizado para resolver solicitudes');
        }

        // Gerente solo puede resolver solicitudes de su área
        if ($user->hasRole('gerente')) {
            $areaEmpleado = $solicitud->empleado->area_id ?? null;
            if ($user->empleado->area_id !== $areaEmpleado) {
                throw new AuthorizationException('Solicitud fuera de tu área');
            }
        }

        if ($solicitud->estatus !== 'Pendiente') {
            throw new \Exception('Solicitud ya procesada');
        }

        if ($accion === 'rechazado') {
            $solicitud->update([
                'estatus'        => 'Rechazado',
                'motivo_rechazo' => $motivo,
            ]);
            return $solicitud;
        }

        // Autorizar — todo en una sola transacción
        return DB::transaction(function () use ($solicitud) {
            $solicitud->update(['estatus' => 'Autorizado']);

            // SolicitudGastoService es el único responsable de crear gastos
            app(SolicitudGastoService::class)->generarGastos($solicitud);

            return $solicitud;
        });
    }

    public function cancelar(Solicitud $solicitud, $user, ?string $motivo = null): Solicitud
    {
        // ✅ Typo corregido: era $solicitud->status, debe ser ->estatus
        if (!in_array($solicitud->estatus, ['Borrador', 'Pendiente'], true)) {
            throw new \Exception('No se puede cancelar esta solicitud');
        }

        $esDueno  = $user->empleado?->id === $solicitud->empleado_id;
        $puedeAdmin = $user->can('solicitudes.eliminar');

        if (!$esDueno && !$puedeAdmin) {
            throw new AuthorizationException('No autorizado');
        }

        $solicitud->update([
            'estatus'             => 'Cancelado',
            'motivo_cancelacion'  => $motivo,
        ]);

        app(AuditoriaService::class)->registrar([
            'solicitud_id' => $solicitud->id,
            'evento'       => 'cancelado',
        ]);

        return $solicitud;
    }

    public function reabrir(Solicitud $solicitud, $user): Solicitud
    {
        if (!in_array($solicitud->estatus, ['Rechazado', 'Cancelado'], true)) {
            throw new \Exception('No se puede reabrir');
        }

        if ($user->empleado?->id !== $solicitud->empleado_id) {
            throw new AuthorizationException('Solo el dueño puede reabrir');
        }

        $solicitud->update(['estatus' => 'Borrador']);

        app(AuditoriaService::class)->registrar([
            'solicitud_id' => $solicitud->id,
            'evento'       => 'reabierto',
        ]);

        return $solicitud;
    }

    public function aprobar(Solicitud $solicitud, $user): Solicitud
    {
        if (!$user->can('solicitudes.aprobar')) {
            throw new AuthorizationException('No autorizado');
        }

        if ($solicitud->estatus !== 'Pendiente') {
            throw new \Exception('Solicitud no válida para aprobación');
        }

        return DB::transaction(function () use ($solicitud) {
            // lockForUpdate previene doble aprobación concurrente
            Solicitud::lockForUpdate()->findOrFail($solicitud->id);

            if ($solicitud->gastos()->exists()) {
                throw new \Exception('La solicitud ya tiene gastos generados');
            }

            $solicitud->update(['estatus' => 'Autorizado']);

            // Único punto de generación de gastos
            app(SolicitudGastoService::class)->generarGastos($solicitud);

            return $solicitud->load('gastos');
        });
    }

    public function evaluarCierre(Solicitud $solicitud): void
    {
        if ($solicitud->estatus !== 'Autorizado') {
            return;
        }

        // Una sola query con dos aggregates — sin N+1
        $counts = $solicitud->gastos()
            ->selectRaw("
                COUNT(*)                                           AS total,
                COUNT(*) FILTER (WHERE estatus = 'comprobado')    AS comprobados
            ")
            ->first();

        // Sin gastos o no todos comprobados → no cerrar
        if (!$counts || $counts->total === 0 || (int) $counts->total !== (int) $counts->comprobados) {
            return;
        }

        // ✅ Actualiza ANTES de auditar — si falla el update, la auditoría no se crea
        $solicitud->update(['estatus' => 'Comprobado']);

        app(AuditoriaService::class)->registrar([
            'solicitud_id' => $solicitud->id,
            'evento'       => 'completada',
        ]);
    }
}
