<?php

namespace App\Services\Solicitudes;

use App\Exceptions\Solicitudes\SolicitudBloqueadaException;
use App\Helpers\FolioHelper;
use App\Models\Solicitud;
use App\Models\SolicitudAprobacion;
use App\Models\SolicitudAuditoria;
use App\Models\SolicitudDetalle;
use App\Services\Auditoria\ActividadLogService;
use App\Services\Gasto\PoliticaGastoService;
use App\Services\Presupuesto\PresupuestoService;
use Cache;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SolicitudService
{
    public function __construct(
        private PresupuestoService $presupuestoService,
        private ActividadLogService $actividadLog
    ) {}

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

    public function paginate(
        $user,
        string  $search       = '',
        string  $estatus      = '',
        string  $cumplimiento = '',
        ?int    $proyectoId   = null,
        ?string $fechaInicio  = null,
        ?string $fechaFin     = null,
        string  $sortBy       = 'created_at',
        string  $sortDir      = 'desc',
        int     $perPage      = 15,
    ): LengthAwarePaginator {

        $sortBy  = in_array($sortBy, self::ALLOWED_SORT_COLUMNS, true) ? $sortBy : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS, true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return Solicitud::query()
            ->leftJoin('proyectos', 'proyectos.id', '=', 'solicitudes.proyecto_id')
            ->leftJoin('areas', 'areas.id', '=', 'solicitudes.area_id')
            ->select([
                'solicitudes.*',
                'proyectos.nombre as proyecto_nombre',
                'proyectos.codigo as proyecto_codigo',
                'areas.nombre as area_nombre',
            ])
            ->selectSub(function ($q) {
                $q->from('gastos as g')
                    ->selectRaw('COALESCE(SUM(g.monto),0)')
                    ->whereColumn('g.solicitud_id', 'solicitudes.id')
                    ->whereNull('g.deleted_at')
                    ->whereIn('g.estatus', ['aprobado', 'pendiente', 'excepcion']);
            }, 'monto_aprobable')
            ->selectSub(function ($q) {
                $q->from('gastos as g')
                    ->selectRaw('COALESCE(SUM(g.monto),0)')
                    ->whereColumn('g.solicitud_id', 'solicitudes.id')
                    ->whereNull('g.deleted_at')
                    ->where('g.estatus', 'comprobado');
            }, 'monto_comprobado')
            ->selectSub(function ($q) {
                $q->selectRaw("
                    CASE WHEN EXISTS (
                        SELECT 1
                        FROM gastos_excepciones ge2
                        JOIN gastos g2 ON g2.id = ge2.gasto_id
                        WHERE g2.solicitud_id = solicitudes.id
                        AND ge2.nivel = 1
                        AND ge2.estatus = 'aprobado'
                    )
                    THEN 1 ELSE 0 END
                ");
            }, 'excepciones_n1')
            ->selectSub(function ($q) {
                $q->selectRaw("
                    CASE WHEN EXISTS (
                        SELECT 1
                        FROM gastos_excepciones ge2
                        JOIN gastos g2 ON g2.id = ge2.gasto_id
                        WHERE g2.solicitud_id = solicitudes.id
                        AND ge2.nivel = 2
                        AND ge2.estatus = 'aprobado'
                    )
                    THEN 1 ELSE 0 END
                ");
            }, 'excepciones_n2')
            ->withCumplimiento()
            ->where('solicitudes.empleado_id', $user->empleado->id)
            ->when($search, fn ($q) =>
                $q->where(function ($q2) use ($search) {
                    $q2->where('solicitudes.folio', 'ilike', "%{$search}%")
                    ->orWhere('solicitudes.motivo', 'ilike', "%{$search}%")
                    ->orWhere('proyectos.nombre', 'ilike', "%{$search}%");
                })
            )
            ->when(
                $estatus && in_array($estatus, self::ESTATUS_VALIDOS, true),
                fn ($q) => $q->where('solicitudes.estatus', $estatus)
            )
            ->when($proyectoId, fn ($q) =>
                $q->where('solicitudes.proyecto_id', $proyectoId)
            )
            ->when($fechaInicio, fn ($q) =>
                $q->whereDate('solicitudes.fecha_inicio', '>=', $fechaInicio)
            )
            ->when($fechaFin, fn ($q) =>
                $q->whereDate('solicitudes.fecha_fin', '<=', $fechaFin)
            )
            ->filterCumplimiento($cumplimiento)
            ->orderBy("solicitudes.{$sortBy}", $sortDir)

            ->paginate($perPage);
    }

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

        $sortBy  = in_array($sortBy, self::ALLOWED_SORT_COLUMNS, true) ? $sortBy : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS, true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        $roleId = $user->roles->first()?->id;

        return Solicitud::query()
            ->leftJoin('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->leftJoin('proyectos', 'proyectos.id', '=', 'solicitudes.proyecto_id')
            ->leftJoin('areas', 'areas.id', '=', 'solicitudes.area_id')
            ->select([
                'solicitudes.*',
                'empleados.nombre_completo as empleado_nombre',
                'proyectos.nombre as proyecto_nombre',
                'areas.nombre as area_nombre',
            ])
            ->where('solicitudes.estatus', 'Pendiente')
            ->when(
                $user->hasRole('manager') && $user->empleado?->area_id,
                fn ($q) => $q->where('solicitudes.area_id', $user->empleado->area_id)
            )
            ->when(
                $user->empleado?->id,
                fn ($q) => $q->where('solicitudes.empleado_id', '!=', $user->empleado->id)
            )
            ->whereNotExists(function ($q) use ($roleId) {
                $q->select(DB::raw(1))
                    ->from('solicitud_aprobaciones')
                    ->whereColumn('solicitud_id', 'solicitudes.id')
                    ->where('role_id', $roleId);
            })
            ->when($search, fn ($q) =>
                $q->where(function ($q2) use ($search) {
                    $q2->where('solicitudes.folio', 'ilike', "%{$search}%")
                    ->orWhere('empleados.nombre_completo', 'ilike', "%{$search}%");
                })
            )
            ->when($proyectoId, fn ($q) =>
                $q->where('solicitudes.proyecto_id', $proyectoId)
            )
            ->when($areaId && $user->hasRole('admin'), fn ($q) =>
                $q->where('solicitudes.area_id', $areaId)
            )
            ->orderBy("solicitudes.{$sortBy}", $sortDir)
            ->paginate($perPage);
    }

    public function paginateAutorizados(
        $user,
        string  $search       = '',
        string  $cumplimiento = '',
        ?int    $proyectoId   = null,
        ?string $fechaInicio  = null,
        ?string $fechaFin     = null,
        string  $sortBy       = 'created_at',
        string  $sortDir      = 'desc',
        int     $perPage      = 15,
    ): LengthAwarePaginator {

        $sortBy  = in_array($sortBy, self::ALLOWED_SORT_COLUMNS, true) ? $sortBy : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS, true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return Solicitud::query()
            ->leftJoin('proyectos', 'proyectos.id', '=', 'solicitudes.proyecto_id')
            ->leftJoin('areas', 'areas.id', '=', 'solicitudes.area_id')
            ->select([
                'solicitudes.*',
                'proyectos.nombre as proyecto_nombre',
                'proyectos.codigo as proyecto_codigo',
                'areas.nombre as area_nombre',
            ])
            ->selectSub(function ($q) {
                $q->from('gastos as g')
                    ->selectRaw('COALESCE(SUM(g.monto),0)')
                    ->whereColumn('g.solicitud_id', 'solicitudes.id')
                    ->whereNull('g.deleted_at')
                    ->whereIn('g.estatus', ['aprobado', 'pendiente', 'excepcion']);
            }, 'monto_aprobable')
            ->selectSub(function ($q) {
                $q->from('gastos as g')
                    ->selectRaw('COALESCE(SUM(g.monto),0)')
                    ->whereColumn('g.solicitud_id', 'solicitudes.id')
                    ->whereNull('g.deleted_at')
                    ->where('g.estatus', 'comprobado');
            }, 'monto_comprobado')
            ->selectSub(function ($q) {
                $q->from('gastos_excepciones as ge')
                    ->join('gastos as g', 'g.id', '=', 'ge.gasto_id')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('g.solicitud_id', 'solicitudes.id')
                    ->where('ge.nivel', 1)
                    ->where('ge.estatus', 'pendiente');
            }, 'excepciones_n1')
            ->selectSub(function ($q) {
                $q->from('gastos_excepciones as ge')
                    ->join('gastos as g', 'g.id', '=', 'ge.gasto_id')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('g.solicitud_id', 'solicitudes.id')
                    ->where('ge.nivel', 2)
                    ->where('ge.estatus', 'pendiente');
            }, 'excepciones_n2')
            ->withCumplimiento()
            ->where('solicitudes.empleado_id', $user->empleado->id)
            ->where('solicitudes.estatus', 'Autorizado')
            ->when($search, fn ($q) =>
                $q->where(function ($q2) use ($search) {
                    $q2->where('solicitudes.folio', 'ilike', "%{$search}%")
                    ->orWhere('solicitudes.motivo', 'ilike', "%{$search}%")
                    ->orWhere('proyectos.nombre', 'ilike', "%{$search}%");
                })
            )
            ->when($proyectoId, fn ($q) =>
                $q->where('solicitudes.proyecto_id', $proyectoId)
            )
            ->when($fechaInicio, fn ($q) =>
                $q->whereDate('solicitudes.fecha_inicio', '>=', $fechaInicio)
            )
            ->when($fechaFin, fn ($q) =>
                $q->whereDate('solicitudes.fecha_fin', '<=', $fechaFin)
            )
            ->filterCumplimiento($cumplimiento)
            ->orderBy("solicitudes.{$sortBy}", $sortDir)
            ->paginate($perPage);
    }

    public function list($user): array
    {
        $query = Solicitud::query()
            ->whereIn('estatus', ['Borrador', 'Pendiente'])
            ->orderBy('folio')
            ->toRawSql();

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

    public function solicitudesExtension($user): array
    {
        $query = Solicitud::query()
            ->whereIn('estatus', ['Autorizado', 'Comprobado'])
            ->orderBy('folio');

        if ($user->can('solicitudes.ver.todas')) {
        } elseif ($user->hasRole('gerente') && $user->empleado?->area_id) {
            $query->where('area_id', $user->empleado->area_id);
        } elseif ($user->can('solicitudes.ver.propias')) {
            $query->where('empleado_id', $user->empleado->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query
            ->get(['id', 'folio', 'estatus', 'monto_total'])
            ->toArray();
    }

    public function toggleCancelacion(Solicitud $solicitud, $user, ?string $motivo = null): Solicitud
    {
        if ($solicitud->estatus === 'Cancelado') {
            return $this->reabrir($solicitud, $user);
        }

        return $this->cancelar($solicitud, $user, $motivo);
    }

    public function create(array $data, $user): Solicitud
    {
        if (!$user->can('solicitudes.crear')) {
            throw new AuthorizationException('No autorizado para crear solicitudes');
        }

        return DB::transaction(function () use ($data, $user) {
            $empleado = $user->empleado;
            $montoTotal = collect($data['detalles'])->sum('monto_estimado');
            $folio = FolioHelper::generar('SOL');

            $presupuestoTemp = new Solicitud([
                'empleado_id' => $empleado->id,
                'proyecto_id' => $data['proyecto_id'] ?? null,
                'created_at' => now(),
            ]);
            $presupuesto = $this->presupuestoService->obtenerPresupuestoAplicable($presupuestoTemp);

            $solicitud = Solicitud::create([
                'folio'        => $folio,
                'empleado_id'  => $empleado->id,
                'empresa_id' => $empleado->empresa_id,
                'area_id'      => $empleado->area_id,
                'proyecto_id'  => $data['proyecto_id']  ?? null,
                'presupuesto_id' => $presupuesto?->id,
                'fecha_inicio' => $data['fecha_inicio'] ?? null,
                'fecha_fin'    => $data['fecha_fin']    ?? null,
                'motivo'       => $data['motivo']       ?? null,
                'monto_total'  => $montoTotal,
                'estatus'      => 'Borrador',
            ]);

            $this->auditoria($solicitud, 'created', $user);

            $this->actividadLog->registrar([
                'user' => $user,
                'evento' => 'created',
                'modulo' => 'solicitudes',
                'entidad' => $solicitud,
                'entidad_descripcion' => "Solicitud {$solicitud->folio}",
                'datos_despues' => $solicitud->toArray(),
            ]);

            return $solicitud->load(['detalles.concepto', 'empleado', 'proyecto', 'presupuesto']);
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
            $montoEfectivo = $monto;
            $monto_max = $politica->monto_max;

            if ($detalle->requiere_extension_tarjeta && $detalle->monto_extension_tarjeta > 0) {
                $montoEfectivo = $monto - (float) $detalle->monto_extension_tarjeta;
            }

            if($politica->tipo_limite === 'Diario' && $solicitud?->fecha_inicio && $solicitud?->fecha_fin) {
                $duracion = $solicitud->fecha_inicio->diffInDays($solicitud->fecha_fin) + 1;
                $monto_max *= $duracion;
            }

            if ($montoEfectivo > (float) $monto_max && !$politica->permite_excepcion) {
                $bloqueantes[] = sprintf(
                    '%s excede el límite de %s y no permite excepciones ni extensión de tarjeta.',
                    $detalle->concepto->nombre,
                    number_format($monto_max, 2)
                );
            }
        }

        if (!empty($bloqueantes)) {
            throw new SolicitudBloqueadaException(
                implode(' | ', $bloqueantes)
            );
        }

        try {
        app(PresupuestoService::class)->validarDisponibilidad($solicitud);
        } catch (\App\Exceptions\Presupuesto\SaldoInsuficienteException $e) {
            throw new \App\Exceptions\Solicitudes\SolicitudBloqueadaException($e->getMessage());
        }

        $solicitud->update(['estatus' => 'Pendiente']);
        app(PresupuestoService::class)->comprometerMonto($solicitud);
        $this->auditoria($solicitud, 'enviado', $user);

        return $solicitud;
    }

    public function enviarJustificaciones(Solicitud $solicitud, $justificaciones): Solicitud
    {
        return DB::transaction(function () use ($justificaciones, $solicitud) {
            // Persiste las justificaciones
            foreach ($justificaciones as $detalleId => $texto) {
                SolicitudDetalle::where('id', $detalleId)
                    ->where('solicitud_id', $solicitud->id)
                    ->update(['justificacion_exceso' => $texto]);
            }

            return $solicitud;
        });
    }

    // -------------------------------------------------------------------------
    // Resolver (aprobar o rechazar — endpoint unificado)
    // -------------------------------------------------------------------------

    public function resolver(Solicitud $solicitud, string $accion, ?string $motivo, $user): Solicitud
    {
        if (!$user->can('solicitudes.aprobar') && !$user->can('solicitudes.rechazar')) {
            throw new AuthorizationException('No autorizado para resolver solicitudes');
        }

        // Manager: scope de área
        if ($user->hasRole('manager')) {
            $areaEmpleado = $solicitud->empleado->area_id ?? null;
            if ($user->empleado?->area_id !== $areaEmpleado) {
                throw new AuthorizationException('Solicitud fuera de tu área');
            }
        }

        // ✅ Delega toda la lógica al SolicitudAprobacionService
        $resultado = app(SolicitudAprobacionService::class)
            ->resolver($solicitud, $user, $accion, $motivo);

        // Recarga para devolver el estatus actualizado
        return $solicitud->fresh();
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
        app(PresupuestoService::class)->liberarCompromiso($solicitud);
        $this->auditoria($solicitud, 'cancelado', $user, ['motivo_cancelacion' => $motivo]);

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

        // ✅ Reutiliza resolver() con acción 'aprobado'
        return $this->resolver($solicitud, 'aprobado', null, $user);
    }

    // -------------------------------------------------------------------------
    // Cierre automático cuando todos los gastos están comprobados
    // -------------------------------------------------------------------------

    public function evaluarCierre(Solicitud $solicitud): void
    {
        // Solo evaluar solicitudes autorizadas o ya comprobadas
        if (!in_array($solicitud->estatus, ['Autorizado', 'Comprobado'], true)) {
            return;
        }

        $counts = $solicitud->gastos()
            ->selectRaw("
                COUNT(*) AS total,
                COUNT(*) FILTER (WHERE estatus = 'comprobado') AS comprobados,
                COUNT(*) FILTER (WHERE estatus IN ('pendiente', 'aprobado', 'excepcion')) AS enProgreso,
                COUNT(*) FILTER (WHERE estatus = 'rechazado') AS rechazados
            ")
            ->first();

        if (!$counts || $counts->total === 0) {
            return;
        }

        // ✅ Si hay gastos pendientes/aprobados/excepción → NO cerrar
        if ((int) $counts->enProgreso > 0) {
            return;
        }

        // ✅ TODOS los gastos están en estado terminal (comprobados o rechazados)
        // Solo cerrar si al menos uno fue comprobado (evita cerrar solicitud totalmente rechazada)
        if ((int) $counts->total === ((int) $counts->comprobados + (int) $counts->rechazados)
            && (int) $counts->comprobados > 0
        ) {
            if ($solicitud->estatus !== 'Comprobado') {
                $solicitud->update(['estatus' => 'Comprobado']);

                if ($solicitud->presupuesto_id) {
                    $montoReal = $solicitud->gastos()
                        ->where('estatus', 'comprobado')
                        ->sum('monto');

                    $this->presupuestoService->registrarGasto(
                        $solicitud->presupuesto,
                        $montoReal,
                        $solicitud,
                        auth()->user()
                    );
                }

                $this->actividadLog->registrar([
                    'user' => auth()->user(),
                    'evento' => 'updated',
                    'modulo' => 'solicitudes',
                    'entidad' => $solicitud,
                    'entidad_descripcion' => "Solicitud {$solicitud->folio} completada automáticamente",
                    'metadatos' => [
                        'gastos_comprobados' => $counts->comprobados,
                    ],
                    'es_sensible' => true,
                ]);

                $this->auditoria($solicitud, 'comprobado_automatico', null, [
                    'total_gastos' => $counts->total,
                    'comprobados'  => $counts->comprobados,
                    'rechazados'   => $counts->rechazados,
                ]);
            }
        }
    }

    public function reabrir(Solicitud $solicitud, $user): Solicitud
    {
        if (!in_array($solicitud->estatus, ['Rechazado', 'Cancelado'], true)) {
            throw new \Exception('Solo solicitudes rechazadas o canceladas pueden reabrirse');
        }

        if ($user->empleado?->id !== $solicitud->empleado_id) {
            throw new AuthorizationException('Solo el dueño puede reabrir la solicitud');
        }

        return DB::transaction(function () use ($solicitud, $user) {
            $solicitud->update([
                'estatus'        => 'Borrador',
                'motivo_rechazo' => null,
            ]);

            SolicitudAprobacion::where('solicitud_id', $solicitud->id)->delete();
            app(PresupuestoService::class)->liberarCompromiso($solicitud);
            $this->auditoria($solicitud, 'reabierto', $user);

            return $solicitud;
        });
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
