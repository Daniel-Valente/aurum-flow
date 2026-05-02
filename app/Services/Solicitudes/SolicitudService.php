<?php

namespace App\Services\Solicitudes;

use App\Helpers\FolioHelper;
use App\Models\Solicitud;
use App\Models\SolicitudAuditoria;
use App\Models\SolicitudDetalle;
use App\Services\Gasto\PoliticaGastoService;
use App\Services\Solicitudes\SolicitudGastoService;
use Cache;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SolicitudService
{
    private const ALLOWED_SORT_COLUMNS = [
        'folio',
        'monto_total',
        'fecha_solicitud',
        'fecha_inicio',
        'fecha_fin',
        'created_at',
    ];
    private const ALLOWED_SORT_DIRS = ['asc', 'desc'];
    private const ESTATUS_VALIDOS   = [
        'Borrador',
        'Pendiente',
        'Autorizado',
        'Rechazado',
        'Comprobado',
        'Cancelado',
    ];

    private const LIST_CACHE_KEY     = 'solicitudes.list.activos';

    // -------------------------------------------------------------------------
    // Listado paginado — vista "Mis Solicitudes"
    // Scope automático por permisos: propias | área | todas
    // -------------------------------------------------------------------------

    public function paginate(
        $user,
        string  $search       = '',
        string  $estatus      = '',
        string  $cumplimiento = '',  // ok | con_excepcion | rechazado | sin_captura
        ?int    $proyectoId   = null,
        ?string $fechaInicio  = null,
        ?string $fechaFin     = null,
        string  $sortBy       = 'created_at',
        string  $sortDir      = 'desc',
        int     $perPage      = 15,
    ): LengthAwarePaginator {
        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return Solicitud::query()
            ->leftJoin('proyectos', 'proyectos.id', '=', 'solicitudes.proyecto_id')
            ->leftJoin('areas',     'areas.id',     '=', 'solicitudes.area_id')
            ->select(
                'solicitudes.*',
                'proyectos.nombre AS proyecto_nombre',
                'proyectos.codigo AS proyecto_codigo',
                'areas.nombre     AS area_nombre',

                // ── Columna: monto aprobable (suma de gastos en estatus aprobado/pendiente)
                DB::raw('(
                    SELECT COALESCE(SUM(g.monto), 0)
                    FROM gastos g
                    WHERE g.solicitud_id = solicitudes.id
                    AND g.deleted_at IS NULL
                    AND g.estatus IN (\'aprobado\', \'pendiente\', \'excepcion\')
                ) AS monto_aprobable'),

                // ── Columna: monto comprobado (gastos con comprobante aceptado)
                DB::raw('(
                    SELECT COALESCE(SUM(g.monto), 0)
                    FROM gastos g
                    WHERE g.solicitud_id = solicitudes.id
                    AND g.deleted_at IS NULL
                    AND g.estatus = \'comprobado\'
                ) AS monto_comprobado'),

                // ── Columna: excepciones nivel 1 pendientes
                DB::raw('(
                    SELECT COUNT(*)
                    FROM gastos_excepciones ge
                    INNER JOIN gastos g ON g.id = ge.gasto_id
                    WHERE g.solicitud_id = solicitudes.id
                    AND ge.nivel = 1
                ) AS excepciones_n1'),

                // ── Columna: excepciones nivel 2 pendientes
                DB::raw('(
                    SELECT COUNT(*)
                    FROM gastos_excepciones ge
                    INNER JOIN gastos g ON g.id = ge.gasto_id
                    WHERE g.solicitud_id = solicitudes.id
                    AND ge.nivel = 2
                ) AS excepciones_n2'),

                // ── Columna: cumplimiento calculado
                // Usada para el filtro y para mostrar el badge en la tabla
                DB::raw("(
                    CASE
                        WHEN NOT EXISTS (
                            SELECT 1 FROM gastos g
                            WHERE g.solicitud_id = solicitudes.id AND g.deleted_at IS NULL
                        ) THEN 'sin_captura'
                        WHEN EXISTS (
                            SELECT 1 FROM gastos g
                            WHERE g.solicitud_id = solicitudes.id
                            AND g.deleted_at IS NULL
                            AND g.estatus = 'rechazado'
                        ) THEN 'rechazado'
                        WHEN EXISTS (
                            SELECT 1 FROM gastos_excepciones ge
                            INNER JOIN gastos g ON g.id = ge.gasto_id
                            WHERE g.solicitud_id = solicitudes.id
                        ) THEN 'con_excepcion'
                        ELSE 'ok'
                    END
                ) AS cumplimiento_calculado"),
            )
            // ── Scope por permisos (solo las propias en esta vista)
            ->where('solicitudes.empleado_id', $user->empleado->id)
            // ── Filtros
            ->when($search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('solicitudes.folio',  'ilike', "%{$search}%")
                    ->orWhere('solicitudes.motivo', 'ilike', "%{$search}%")
                    ->orWhere('proyectos.nombre',   'ilike', "%{$search}%")
                )
            )
            ->when(
                $estatus && in_array($estatus, self::ESTATUS_VALIDOS, true),
                fn($q) => $q->where('solicitudes.estatus', $estatus)
            )
            ->when($proyectoId, fn($q) =>
                $q->where('solicitudes.proyecto_id', $proyectoId)
            )
            ->when($fechaInicio, fn($q) =>
                $q->whereDate('solicitudes.fecha_inicio', '>=', $fechaInicio)
            )
            ->when($fechaFin, fn($q) =>
                $q->whereDate('solicitudes.fecha_fin', '<=', $fechaFin)
            )
            // ── Filtro de cumplimiento — filtra sobre la subquery calculada
            ->when($cumplimiento, fn($q) =>
                $q->havingRaw("(
                    CASE
                        WHEN NOT EXISTS (
                            SELECT 1 FROM gastos g
                            WHERE g.solicitud_id = solicitudes.id AND g.deleted_at IS NULL
                        ) THEN 'sin_captura'
                        WHEN EXISTS (
                            SELECT 1 FROM gastos g
                            WHERE g.solicitud_id = solicitudes.id
                            AND g.deleted_at IS NULL
                            AND g.estatus = 'rechazado'
                        ) THEN 'rechazado'
                        WHEN EXISTS (
                            SELECT 1 FROM gastos_excepciones ge
                            INNER JOIN gastos g ON g.id = ge.gasto_id
                            WHERE g.solicitud_id = solicitudes.id
                        ) THEN 'con_excepcion'
                        ELSE 'ok'
                    END
                ) = ?", [$cumplimiento])
            )
            ->orderBy("solicitudes.{$sortBy}", $sortDir)
            ->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Listado paginado — vista "Autorizaciones" (admin / gerente)
    // Solo solicitudes en estatus Pendiente; gerente filtra por su área
    // -------------------------------------------------------------------------

    public function paginateAutorizaciones(
        $user,
        string  $search     = '',
        ?int    $proyectoId = null,
        ?int    $areaId     = null,
        string  $sortBy     = 'created_at',
        string  $sortDir    = 'desc',
        int     $perPage    = 15,
    ): LengthAwarePaginator {
        if (!$user->can('solicitudes.aprobar') && !$user->can('solicitudes.rechazar')) {
            throw new AuthorizationException('No autorizado para ver autorizaciones');
        }

        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return Solicitud::query()
            ->leftJoin('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->leftJoin('proyectos', 'proyectos.id', '=', 'solicitudes.proyecto_id')
            ->leftJoin('areas',     'areas.id',     '=', 'solicitudes.area_id')
            ->select(
                'solicitudes.*',
                'empleados.nombre_completo AS empleado_nombre',
                'proyectos.nombre          AS proyecto_nombre',
                'areas.nombre              AS area_nombre',
            )
            // Solo pendientes — esta vista es exclusivamente para autorizar
            ->where('solicitudes.estatus', 'Pendiente')
            // Gerente: solo su área; admin: todas
            ->when(
                $user->hasRole('gerente') && $user->empleado?->area_id,
                fn($q) => $q->where('solicitudes.area_id', $user->empleado->area_id)
            )
            ->when(
                $search,
                fn($q) =>
                $q->where(
                    fn($q2) =>
                    $q2->where('solicitudes.folio', 'ilike', "%{$search}%")
                        ->orWhere('empleados.nombre_completo', 'ilike', "%{$search}%")
                )
            )
            ->when(
                $proyectoId,
                fn($q) =>
                $q->where('solicitudes.proyecto_id', $proyectoId)
            )
            // Filtro de área solo disponible para admin
            ->when(
                $areaId && $user->hasRole('admin'),
                fn($q) =>
                $q->where('solicitudes.area_id', $areaId)
            )
            ->orderBy("solicitudes.{$sortBy}", $sortDir)
            ->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Lista plana para selects / dropdowns
    // -------------------------------------------------------------------------

    public function list($user): array
    {
        $query = Solicitud::query()
            ->whereIn('estatus', ['Borrador', 'Pendiente'])
            ->orderBy('folio')
            ->toRawSql();

        // ✅ Sin JOIN — usar nombre de columna directo, no con prefijo de tabla
        if ($user->can('solicitudes.ver.todas')) {
            // sin restricción
        } elseif ($user->hasRole('gerente') && $user->empleado?->area_id) {
            $query->where('area_id', $user->empleado->area_id);
        } elseif ($user->can('solicitudes.ver.propias')) {
            $query->where('empleado_id', $user->empleado->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->get(['id', 'folio', 'estatus', 'monto_total'])->toArray();
    }

    // -------------------------------------------------------------------------
    // Toggle cancelación
    // Cancelado → Borrador (reabrir) | Borrador/Pendiente → Cancelado
    // -------------------------------------------------------------------------

    public function toggleCancelacion(Solicitud $solicitud, $user, ?string $motivo = null): Solicitud
    {
        if ($solicitud->estatus === 'Cancelado') {
            return $this->reabrir($solicitud, $user);
        }

        return $this->cancelar($solicitud, $user, $motivo);
    }

    // -------------------------------------------------------------------------
    // Crear solicitud
    // -------------------------------------------------------------------------

    public function create(array $data, $user): Solicitud
    {
        if (!$user->can('solicitudes.crear')) {
            throw new AuthorizationException('No autorizado para crear solicitudes');
        }

        return DB::transaction(function () use ($data, $user) {
            $solicitud = Solicitud::create([
                'folio'        => FolioHelper::generar('SOL'),
                'empleado_id'  => $user->empleado->id,
                'area_id'      => $user->empleado->area_id,
                'proyecto_id'  => $data['proyecto_id']  ?? null,
                'fecha_inicio' => $data['fecha_inicio'] ?? null,
                'fecha_fin'    => $data['fecha_fin']    ?? null,
                'motivo'       => $data['motivo']       ?? null,
                'monto_total'  => $data['monto_total']  ?? 0,
                'estatus'      => 'Borrador',
            ]);

            $this->auditoria($solicitud, 'created', $user);

            return $solicitud;
        });
    }

    public function update(Solicitud $solicitud, array $data): Solicitud
    {
        $solicitud->update([
                'folio'        => $data['folio'] ?? $solicitud->folio,
                'empleado_id'  => $data['empleado_id'] ?? $solicitud->empleado_id,
                'area_id'      => $data['area_id'] ?? $solicitud->area_id,
                'proyecto_id'  => $data['proyecto_id'] ?? $solicitud->proyecto_id,
                'fecha_inicio' => $data['fecha_inicio'] ?? $solicitud->fecha_inicio,
                'fecha_fin'    => $data['fecha_fin'] ?? $solicitud->fecha_fin,
                'motivo'       => $data['motivo'] ?? $solicitud->motivo,
                'monto_total'  => $data['monto_total'] ?? $solicitud->monto_total,
                'estatus'      => $data['estatus'] ?? $solicitud->estatus,
        ]);

        $this->flushCache();

        return $solicitud->load('empleado', 'area');
    }

    // -------------------------------------------------------------------------
    // Agregar detalles en batch
    // -------------------------------------------------------------------------

    public function agregarDetalle(Solicitud $solicitud, array $detalles, $user): Solicitud
    {
        $esDueno = $user->empleado->id === $solicitud->empleado_id;

        if (!$esDueno && !$user->can('solicitudes.editar')) {
            throw new AuthorizationException('No autorizado para editar esta solicitud');
        }

        if ($solicitud->estatus !== 'Borrador') {
            throw new \Exception('Solo se pueden agregar detalles a solicitudes en borrador');
        }

        return DB::transaction(function () use ($solicitud, $detalles) {
            $now  = now();
            $rows = array_map(fn($d) => [
                'solicitud_id'   => $solicitud->id,
                'concepto_id'    => $d['concepto_id'],
                'monto_estimado' => $d['monto_estimado'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ], $detalles);

            SolicitudDetalle::insert($rows);

            $total = SolicitudDetalle::where('solicitud_id', $solicitud->id)
                ->sum('monto_estimado');

            $solicitud->update(['monto_total' => $total]);

            return $solicitud->fresh('detalles');
        });
    }

    // -------------------------------------------------------------------------
    // Enviar a revisión
    // -------------------------------------------------------------------------

    public function enviar(Solicitud $solicitud, $user): Solicitud
    {
        if (!$user->can('solicitudes.enviar')) {
            throw new AuthorizationException('No autorizado para enviar solicitudes');
        }

        if ($solicitud->estatus !== 'Borrador') {
            throw new \Exception('Solo solicitudes en borrador pueden enviarse');
        }

        if ($user->empleado->id !== $solicitud->empleado_id) {
            throw new AuthorizationException('No es su solicitud');
        }

        $detalles = $solicitud->detalles()->with('concepto')->get();

        if ($detalles->isEmpty()) {
            throw new \Exception('Debe agregar al menos un concepto');
        }

        $role_id = $user->roles->first()?->id;
        $bloqueantes = [];

        foreach ($detalles as $detalle) {
            $politica = app(PoliticaGastoService::class)
                ->getPoliticaAplicable($role_id, $detalle->concepto_id, now());

            if (!$politica) {
                continue;
            }

            $monto = (float) $detalle->monto_estimado;

            if ($monto > (float) $politica->monto_max && !$politica->permite_excepcion) {
                $bloqueantes[] = sprintf(
                    '%s excede el límite de %s y no permite excepciones.',
                    $detalle->concepto->nombre,
                    number_format($politica->monto_max, 2)
                );
            }
        }

        if (!empty($bloqueantes)) {
            throw new \App\Exceptions\Solicitudes\SolicitudBloqueadaException(
                implode(' | ', $bloqueantes)
            );
        }

        $solicitud->update(['estatus' => 'Pendiente']);
        $this->auditoria($solicitud, 'enviado', $user);

        return $solicitud;
    }

    // -------------------------------------------------------------------------
    // Resolver (aprobar o rechazar — endpoint unificado)
    // -------------------------------------------------------------------------

    public function resolver(Solicitud $solicitud, string $accion, ?string $motivo, $user): Solicitud
    {
        if (!$user->can('solicitudes.aprobar') && !$user->can('solicitudes.rechazar')) {
            throw new AuthorizationException('No autorizado para resolver solicitudes');
        }

        if ($user->hasRole('gerente')) {
            if ($user->empleado->area_id !== ($solicitud->empleado->area_id ?? null)) {
                throw new AuthorizationException('Solicitud fuera de tu área');
            }
        }

        if ($solicitud->estatus !== 'Pendiente') {
            throw new \Exception('Solicitud ya procesada');
        }

        if ($accion === 'rechazado') {
            if (!$user->can('solicitudes.rechazar')) {
                throw new AuthorizationException('No autorizado para rechazar');
            }

            $solicitud->update([
                'estatus'        => 'Rechazado',
                'motivo_rechazo' => $motivo,
            ]);

            $this->auditoria($solicitud, 'rechazado', $user, ['motivo_rechazo' => $motivo]);

            return $solicitud;
        }

        if (!$user->can('solicitudes.aprobar')) {
            throw new AuthorizationException('No autorizado para aprobar');
        }

        return DB::transaction(function () use ($solicitud, $user) {
            $solicitud->update(['estatus' => 'Autorizado']);

            app(SolicitudGastoService::class)->generarGastos($solicitud);

            $this->auditoria($solicitud, 'aprobado', $user);

            return $solicitud;
        });
    }

    // -------------------------------------------------------------------------
    // Cancelar
    // -------------------------------------------------------------------------

    public function cancelar(Solicitud $solicitud, $user, ?string $motivo = null): Solicitud
    {
        if (!in_array($solicitud->estatus, ['Borrador', 'Pendiente'], true)) {
            throw new \Exception(
                "No se puede cancelar una solicitud en estatus '{$solicitud->estatus}'"
            );
        }

        $esDueno    = $user->empleado?->id === $solicitud->empleado_id;
        $puedeAdmin = $user->can('solicitudes.eliminar');

        if (!$esDueno && !$puedeAdmin) {
            throw new AuthorizationException('No autorizado para cancelar esta solicitud');
        }

        $solicitud->update([
            'estatus'            => 'Cancelado',
            'motivo_cancelacion' => $motivo,
        ]);

        $this->auditoria($solicitud, 'cancelado', $user, ['motivo_cancelacion' => $motivo]);

        return $solicitud;
    }

    // -------------------------------------------------------------------------
    // Reabrir (Cancelado o Rechazado → Borrador)
    // -------------------------------------------------------------------------

    public function reabrir(Solicitud $solicitud, $user): Solicitud
    {
        if (!in_array($solicitud->estatus, ['Rechazado', 'Cancelado'], true)) {
            throw new \Exception('Solo solicitudes rechazadas o canceladas pueden reabrirse');
        }

        if ($user->empleado?->id !== $solicitud->empleado_id) {
            throw new AuthorizationException('Solo el dueño puede reabrir la solicitud');
        }

        $solicitud->update(['estatus' => 'Borrador']);

        $this->auditoria($solicitud, 'reabierto', $user);

        return $solicitud;
    }

    // -------------------------------------------------------------------------
    // Aprobar (endpoint directo sin string de acción)
    // -------------------------------------------------------------------------

    public function aprobar(Solicitud $solicitud, $user): Solicitud
    {
        if (!$user->can('solicitudes.aprobar')) {
            throw new AuthorizationException('No autorizado para aprobar solicitudes');
        }

        if ($solicitud->estatus !== 'Pendiente') {
            throw new \Exception('Solicitud no válida para aprobación');
        }

        return DB::transaction(function () use ($solicitud, $user) {
            Solicitud::lockForUpdate()->findOrFail($solicitud->id);

            if ($solicitud->gastos()->exists()) {
                throw new \Exception('La solicitud ya tiene gastos generados');
            }

            $solicitud->update(['estatus' => 'Autorizado']);

            app(SolicitudGastoService::class)->generarGastos($solicitud);

            $this->auditoria($solicitud, 'aprobado', $user);

            return $solicitud->load('gastos');
        });
    }

    // -------------------------------------------------------------------------
    // Cierre automático cuando todos los gastos están comprobados
    // -------------------------------------------------------------------------

    public function evaluarCierre(Solicitud $solicitud): void
    {
        if ($solicitud->estatus !== 'Autorizado') {
            return;
        }

        $counts = $solicitud->gastos()
            ->selectRaw("
                COUNT(*)                                        AS total,
                COUNT(*) FILTER (WHERE estatus = 'comprobado') AS comprobados
            ")
            ->first();

        if (!$counts || $counts->total === 0 || (int) $counts->total !== (int) $counts->comprobados) {
            return;
        }

        $solicitud->update(['estatus' => 'Comprobado']);

        $this->auditoria($solicitud, 'comprobado', null);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Scope por permisos — aplicado en paginate() y list()
     * para no duplicar la lógica de visibilidad.
     */
    private function aplicarScopePorPermiso($query, $user, bool $conJoin = true): void
    {
        $prefijo = $conJoin ? 'solicitudes.' : '';

        if ($user->can('solicitudes.ver.todas')) {
            return;
        }

        if ($user->hasRole('gerente') && $user->empleado?->area_id) {
            $query->where("{$prefijo}area_id", $user->empleado->area_id);
            return;
        }

        if ($user->can('solicitudes.ver.propias')) {
            $query->where("{$prefijo}empleado_id", $user->empleado->id);
            return;
        }

        $query->whereRaw('1 = 0');
    }

    /**
     * Centraliza la creación de auditorías — evita repetir
     * SolicitudAuditoria::create() en cada método.
     */
    private function auditoria(Solicitud $solicitud, string $evento, $user, array $datos = []): void
    {
        SolicitudAuditoria::create([
            'solicitud_id' => $solicitud->id,
            'evento'       => $evento,
            'actor_id'     => $user?->id ?? auth()->id(),
            'datos'        => !empty($datos) ? $datos : null,
        ]);
    }

    private function flushCache(): void
    {
        Cache::forget(self::LIST_CACHE_KEY);
        Cache::forget('proyectos.proyectos_id');
        Cache::forget('proyectos.estatus');
    }
}
