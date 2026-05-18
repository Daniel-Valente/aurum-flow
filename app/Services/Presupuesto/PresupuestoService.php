<?php

namespace App\Services\Presupuesto;

use App\Exceptions\Presupuesto\SaldoInsuficienteException;
use App\Models\Presupuesto;
use App\Models\PresupuestoMovimiento;
use App\Models\PresupuestoAlerta;
use App\Models\PresupuestoSolicitud;
use App\Models\PresupuestoTransferencia;
use App\Models\Solicitud;
use App\Services\Auditoria\ActividadLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;

class PresupuestoService
{
    public function __construct(
        private ActividadLogService $actividadLog
    ) {}

    private const ALLOWED_SORT_COLUMNS = [
        'codigo',
        'nombre',
        'tipo',
        'monto_total',
        'periodo',
        'fecha_inicio',
        'fecha_fin',
        'estatus',
        'created_at',
    ];
    private const ALLOWED_SORT_DIRS = ['asc', 'desc'];

    public function paginate(
        $user,
        string  $search    = '',
        string  $tipo      = '',
        string  $estatus   = '',
        string  $periodo   = '',
        ?int    $empresaId = null,
        ?int    $areaId    = null,
        ?int    $empleadoId = null,
        ?int    $proyectoId = null,
        string  $sortBy    = 'created_at',
        string  $sortDir   = 'desc',
        int     $perPage   = 15,
    ): LengthAwarePaginator {
        if (!$user->can('presupuestos.ver')) {
            throw new AuthorizationException('No autorizado');
        }

        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return Presupuesto::query()
            ->with(['empresa:id,nombre', 'area:id,nombre', 'empleado:id,nombre_completo', 'proyecto:id,nombre,codigo'])
            ->withCount(['alertas' => fn($q) => $q->where('resuelto', false)])
            ->when(
                $search,
                fn($q) =>
                $q->where(function ($q2) use ($search) {
                    $q2->where('codigo', 'ilike', "%{$search}%")
                        ->orWhere('nombre', 'ilike', "%{$search}%")
                        ->orWhere('descripcion', 'ilike', "%{$search}%");
                })
            )
            ->when($tipo,       fn($q) => $q->where('tipo',        $tipo))
            ->when($estatus,    fn($q) => $q->where('estatus',     $estatus))
            ->when($periodo,    fn($q) => $q->where('periodo',     $periodo))
            ->when($empresaId,  fn($q) => $q->where('empresa_id',  $empresaId))
            ->when($areaId,     fn($q) => $q->where('area_id',     $areaId))
            ->when($empleadoId, fn($q) => $q->where('empleado_id', $empleadoId))
            ->when($proyectoId, fn($q) => $q->where('proyecto_id', $proyectoId))
            ->when(
                !$user->can('presupuestos.ver.todos') && $user->hasRole('manager'),
                fn($q) => $q->where(function ($q2) use ($user) {
                    $q2->where('area_id', $user->empleado?->area_id)
                        ->orWhere('empleado_id', $user->empleado?->id);
                })
            )
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    public function list(?string $tipo = null, bool $soloActivos = true): array
    {
        return Presupuesto::query()
            ->when($soloActivos, fn($q) => $q->vigentes())
            ->when($tipo, fn($q) => $q->where('tipo', $tipo))
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre', 'tipo', 'monto_total', 'monto_gastado', 'monto_comprometido'])
            ->toArray();
    }

    public function create(array $data, $user): Presupuesto
    {
        if (!$user->can('presupuestos.crear')) {
            throw new AuthorizationException('No autorizado para crear presupuestos');
        }

        $this->validarEntidadPorTipo($data);
        $this->validarSolapamiento($data);

        return DB::transaction(function () use ($data, $user) {
            if (empty($data['codigo'])) {
                $data['codigo'] = $this->generarCodigo($data['tipo']);
            }

            $presupuesto = Presupuesto::create(array_merge($data, [
                'creado_por'         => $user->id,
                'monto_gastado'      => 0,
                'monto_comprometido' => 0,
                'estatus'            => $data['estatus'] ?? 'borrador',
            ]));

            PresupuestoMovimiento::create([
                'presupuesto_id'     => $presupuesto->id,
                'tipo'               => 'ajuste_incremento',
                'monto'              => $presupuesto->monto_total,
                'saldo_gastado'      => 0,
                'saldo_comprometido' => 0,
                'origen_type'        => Presupuesto::class,
                'origen_id'          => $presupuesto->id,
                'saldo_disponible'   => $presupuesto->monto_total,
                'concepto'           => 'Presupuesto inicial',
                'actor_id'           => $user->id,
                'fecha_movimiento'   => now(),
            ]);

            $this->actividadLog->registrar([
                'user'                => $user,
                'evento'              => 'created',
                'modulo'              => 'presupuestos',
                'entidad'             => $presupuesto,
                'entidad_descripcion' => "Presupuesto {$presupuesto->codigo}",
                'datos_despues'       => $presupuesto->toArray(),
                'es_sensible'         => true,
            ]);

            return $presupuesto->load(['empresa', 'area', 'empleado', 'proyecto']);
        });
    }

    public function update(Presupuesto $presupuesto, array $data, $user): Presupuesto
    {
        if (!$user->can('presupuestos.editar')) {
            throw new AuthorizationException('No autorizado');
        }

        if (in_array($presupuesto->estatus, ['agotado', 'vencido', 'cancelado'], true)) {
            throw new \Exception("No se puede editar un presupuesto en estatus '{$presupuesto->estatus}'.");
        }

        $deltasMonto = isset($data['monto_total'])
            ? (float) $data['monto_total'] - (float) $presupuesto->monto_total
            : 0;

        return DB::transaction(function () use ($presupuesto, $data, $user, $deltasMonto) {
            $antes = $presupuesto->toArray();

            $presupuesto->update($data);

            if (abs($deltasMonto) > 0.001) {
                $tipo = $deltasMonto > 0 ? 'ajuste_incremento' : 'ajuste_decremento';
                $fresh = $presupuesto->fresh();

                PresupuestoMovimiento::create([
                    'presupuesto_id'     => $presupuesto->id,
                    'tipo'               => $tipo,
                    'monto'              => abs($deltasMonto),
                    'saldo_gastado'      => $fresh->monto_gastado,
                    'saldo_comprometido' => $fresh->monto_comprometido,
                    'origen_type'        => Presupuesto::class,
                    'origen_id'          => $presupuesto->id,
                    'saldo_disponible'   => $fresh->monto_disponible,
                    'concepto'           => 'Ajuste por edición del presupuesto',
                    'actor_id'           => $user->id,
                    'fecha_movimiento'   => now(),
                ]);

                $this->verificarUmbrales($presupuesto->fresh());
            }

            $this->actividadLog->registrar([
                'user'                => $user,
                'evento'              => 'updated',
                'modulo'              => 'presupuestos',
                'entidad'             => $presupuesto,
                'entidad_descripcion' => "Presupuesto {$presupuesto->codigo}",
                'datos_antes'         => $antes,
                'datos_despues'       => $presupuesto->fresh()->toArray(),
                'es_sensible'         => true,
            ]);

            return $presupuesto->fresh()->load(['empresa', 'area', 'empleado', 'proyecto']);
        });
    }

    public function aprobar(Presupuesto $presupuesto, $user): Presupuesto
    {
        if (!$user->can('presupuestos.aprobar')) {
            throw new AuthorizationException('No autorizado para aprobar presupuestos');
        }

        if ($presupuesto->estatus !== 'borrador') {
            throw new \Exception('Solo presupuestos en borrador pueden aprobarse.');
        }

        return DB::transaction(function () use ($presupuesto, $user) {
            $presupuesto->update([
                'estatus'      => 'activo',
                'aprobado_por' => $user->id,
                'aprobado_en'  => now(),
            ]);

            $this->actividadLog->registrar([
                'user'                => $user,
                'evento'              => 'approved',
                'modulo'              => 'presupuestos',
                'entidad'             => $presupuesto,
                'entidad_descripcion' => "Presupuesto {$presupuesto->codigo} aprobado",
                'es_sensible'         => true,
            ]);

            return $presupuesto->fresh();
        });
    }

    public function cancelar(Presupuesto $presupuesto, $user, string $motivo): Presupuesto
    {
        if (!$user->can('presupuestos.cancelar')) {
            throw new AuthorizationException('No autorizado');
        }

        if ($presupuesto->monto_comprometido > 0) {
            throw new \Exception(
                "No se puede cancelar. Hay {$presupuesto->monto_comprometido} comprometidos en solicitudes pendientes."
            );
        }

        $presupuesto->update(['estatus' => 'cancelado']);

        $this->actividadLog->registrar([
            'user'                => $user,
            'evento'              => 'cancelled',
            'modulo'              => 'presupuestos',
            'entidad'             => $presupuesto,
            'entidad_descripcion' => "Presupuesto {$presupuesto->codigo} cancelado",
            'metadatos'           => ['motivo' => $motivo],
            'es_sensible'         => true,
        ]);

        return $presupuesto->fresh();
    }

    public function ajustar(Presupuesto $presupuesto, float $monto, string $motivo, $user): Presupuesto
    {
        if (!$user->can('presupuestos.ajustar')) {
            throw new AuthorizationException('No autorizado para ajustar presupuestos');
        }

        if ($presupuesto->estatus !== 'activo') {
            throw new \Exception('Solo se pueden ajustar presupuestos activos.');
        }

        $nuevoTotal = (float) $presupuesto->monto_total + $monto;

        if ($nuevoTotal < (float) $presupuesto->monto_gastado) {
            throw new \Exception(
                'El monto ajustado no puede ser menor al monto ya gastado ('
                    . number_format($presupuesto->monto_gastado, 2) . ').'
            );
        }

        return DB::transaction(function () use ($presupuesto, $monto, $motivo, $user, $nuevoTotal) {
            $presupuesto->update(['monto_total' => $nuevoTotal]);
            $fresh = $presupuesto->fresh();

            PresupuestoMovimiento::create([
                'presupuesto_id'     => $presupuesto->id,
                'tipo'               => $monto > 0 ? 'ajuste_incremento' : 'ajuste_decremento',
                'monto'              => abs($monto),
                'saldo_gastado'      => $fresh->monto_gastado,
                'saldo_comprometido' => $fresh->monto_comprometido,
                'origen_type'        => Presupuesto::class,
                'origen_id'          => $presupuesto->id,
                'saldo_disponible'   => $fresh->monto_disponible,
                'concepto'           => $motivo,
                'actor_id'           => $user->id,
                'fecha_movimiento'   => now(),
            ]);

            $this->verificarUmbrales($fresh);
            $this->actualizarEstatus($fresh);

            $this->actividadLog->registrar([
                'user'                => $user,
                'evento'              => 'updated',
                'modulo'              => 'presupuestos',
                'entidad'             => $presupuesto,
                'entidad_descripcion' => "Ajuste en {$presupuesto->codigo}: " . ($monto > 0 ? '+' : '') . number_format($monto, 2),
                'metadatos'           => ['motivo' => $motivo, 'delta' => $monto],
                'es_sensible'         => true,
            ]);

            return $fresh;
        });
    }

    public function solicitarTransferencia(
        Presupuesto $origen,
        Presupuesto $destino,
        float $monto,
        string $motivo,
        $user
    ): PresupuestoTransferencia {
        if (!$user->can('presupuestos.transferir')) {
            throw new AuthorizationException('No autorizado');
        }

        if ($origen->id === $destino->id) {
            throw new \Exception('El origen y destino no pueden ser el mismo presupuesto.');
        }

        if ((float) $origen->monto_disponible < $monto) {
            throw new SaldoInsuficienteException(
                "El presupuesto origen no tiene saldo suficiente. Disponible: {$origen->monto_disponible}"
            );
        }

        $transferencia = PresupuestoTransferencia::create([
            'presupuesto_origen_id'  => $origen->id,
            'presupuesto_destino_id' => $destino->id,
            'monto'                  => $monto,
            'motivo'                 => $motivo,
            'estatus'                => 'pendiente',
            'solicitado_por'         => $user->id,
            'solicitado_en'          => now(),
        ]);

        return $transferencia;
    }

    public function aprobarTransferencia(PresupuestoTransferencia $transferencia, $user, ?string $comentario = null): PresupuestoTransferencia
    {
        if (!$user->can('presupuestos.aprobar')) {
            throw new AuthorizationException('No autorizado');
        }

        if ($transferencia->estatus !== 'pendiente') {
            throw new \Exception('Solo transferencias pendientes pueden aprobarse.');
        }

        return DB::transaction(function () use ($transferencia, $user, $comentario) {
            $origen  = $transferencia->presupuestoOrigen;
            $destino = $transferencia->presupuestoDestino;
            $monto   = (float) $transferencia->monto;
            $now     = now();

            if ((float) $origen->monto_disponible < $monto) {
                throw new SaldoInsuficienteException('El presupuesto origen ya no tiene saldo suficiente.');
            }

            $origen->decrement('monto_total', $monto);
            $destino->increment('monto_total', $monto);

            foreach (
                [
                    ['presupuesto' => $origen,  'tipo' => 'transferencia_out'],
                    ['presupuesto' => $destino, 'tipo' => 'transferencia_in'],
                ] as $lado
            ) {
                $p = $lado['presupuesto']->fresh();
                PresupuestoMovimiento::create([
                    'presupuesto_id'     => $p->id,
                    'tipo'               => $lado['tipo'],
                    'monto'              => $monto,
                    'saldo_gastado'      => $p->monto_gastado,
                    'saldo_comprometido' => $p->monto_comprometido,
                    'saldo_disponible'   => $p->monto_disponible,
                    'origen_type'        => PresupuestoTransferencia::class,
                    'origen_id'          => $transferencia->id,
                    'concepto'           => $transferencia->motivo,
                    'actor_id'           => $user->id,
                    'fecha_movimiento'   => $now,
                ]);
            }

            $transferencia->aprobar($user, $comentario);

            return $transferencia->fresh();
        });
    }

    public function presupuestosAplicables(Solicitud $solicitud): \Illuminate\Support\Collection
    {
        $empleado = $solicitud->empleado;
        $modo     = $empleado->presupuesto_modo ?? 'cascada';

        $base = Presupuesto::where('estatus', 'activo')
            ->where('activo', true)
            ->where('fecha_inicio', '<=', $solicitud->fecha_inicio ?? now())
            ->where('fecha_fin', '>=', $solicitud->fecha_fin ?? now());

        return match ($modo) {
            'propio'   => (clone $base)
                ->where('tipo', 'empleado')
                ->where('empleado_id', $empleado->id)
                ->get(),

            'proyecto' => (clone $base)
                ->where('tipo', 'proyecto')
                ->where('proyecto_id', $solicitud->proyecto_id)
                ->get(),

            'area'     => (clone $base)
                ->where('tipo', 'area')
                ->where('area_id', $empleado->area_id)
                ->get(),

            default    => (clone $base)->where(function ($q) use ($empleado, $solicitud) {
                $q->where(
                    fn($q2) =>
                    $q2->where('tipo', 'empleado')
                        ->where('empleado_id', $empleado->id)
                )
                    ->orWhere(
                        fn($q2) =>
                        $q2->where('tipo', 'proyecto')
                            ->where('proyecto_id', $solicitud->proyecto_id)
                            ->whereNotNull('proyecto_id')
                    )
                    ->orWhere(
                        fn($q2) =>
                        $q2->where('tipo', 'area')
                            ->where('area_id', $empleado->area_id)
                    )
                    ->orWhere('tipo', 'empresa');
            })->get(),
        };
    }

    public function validarDisponibilidad(Solicitud $solicitud): void
    {
        $presupuestos = $this->presupuestosAplicables($solicitud);
        $monto        = (float) $solicitud->monto_total;

        foreach ($presupuestos as $p) {
            if ((float) $p->monto_disponible < $monto) {
                throw new SaldoInsuficienteException(
                    sprintf(
                        'Presupuesto "%s" insuficiente. Disponible: $%s — Solicitado: $%s.',
                        $p->nombre,
                        number_format($p->monto_disponible, 2),
                        number_format($monto, 2)
                    )
                );
            }
        }
    }

    public function comprometerMonto(Solicitud $solicitud): void
    {
        $presupuestos = $this->presupuestosAplicables($solicitud);
        $monto        = (float) $solicitud->monto_total;

        if ($presupuestos->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($presupuestos, $solicitud, $monto) {
            foreach ($presupuestos as $p) {
                PresupuestoSolicitud::updateOrCreate(
                    ['presupuesto_id' => $p->id, 'solicitud_id' => $solicitud->id],
                    ['monto_comprometido' => $monto, 'estatus' => 'comprometido']
                );

                $p->increment('monto_comprometido', $monto);

                PresupuestoMovimiento::create([
                    'presupuesto_id'     => $p->id,
                    'tipo'               => 'compromiso',
                    'monto'              => $monto,
                    'saldo_gastado'      => $p->fresh()->monto_gastado,
                    'saldo_comprometido' => $p->fresh()->monto_comprometido,
                    'saldo_disponible'   => $p->fresh()->monto_disponible,
                    'origen_type'        => Solicitud::class,
                    'origen_id'          => $solicitud->id,
                    'concepto'           => "Compromiso: {$solicitud->folio}",
                    'actor_id'           => auth()->id(),
                    'fecha_movimiento'   => now(),
                ]);

                $this->verificarUmbrales($p->fresh());
            }
        });
    }

    public function confirmarConsumo(Solicitud $solicitud): void
    {
        $relaciones = PresupuestoSolicitud::where('solicitud_id', $solicitud->id)
            ->where('estatus', 'comprometido')
            ->with('presupuesto')
            ->get();

        if ($relaciones->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($relaciones, $solicitud) {
            foreach ($relaciones as $rel) {
                $p     = $rel->presupuesto;
                $monto = (float) $rel->monto_comprometido;

                $p->decrement('monto_comprometido', $monto);
                $p->increment('monto_gastado', $monto);
                $fresh = $p->fresh();

                $rel->update(['monto_consumido' => $monto, 'estatus' => 'consumido']);

                PresupuestoMovimiento::create([
                    'presupuesto_id'     => $p->id,
                    'tipo'               => 'gasto',
                    'monto'              => $monto,
                    'saldo_gastado'      => $fresh->monto_gastado,
                    'saldo_comprometido' => $fresh->monto_comprometido,
                    'saldo_disponible'   => $fresh->monto_disponible,
                    'origen_type'        => Solicitud::class,
                    'origen_id'          => $solicitud->id,
                    'concepto'           => "Autorización: {$solicitud->folio}",
                    'actor_id'           => auth()->id(),
                    'fecha_movimiento'   => now(),
                ]);

                $this->verificarUmbrales($fresh);
                $this->actualizarEstatus($fresh);
            }
        });
    }

    public function liberarCompromiso(Solicitud $solicitud): void
    {
        $relaciones = PresupuestoSolicitud::where('solicitud_id', $solicitud->id)
            ->where('estatus', 'comprometido')
            ->with('presupuesto')
            ->get();

        if ($relaciones->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($relaciones, $solicitud) {
            foreach ($relaciones as $rel) {
                $p     = $rel->presupuesto;
                $monto = (float) $rel->monto_comprometido;

                $p->decrement('monto_comprometido', $monto);
                $fresh = $p->fresh();

                $rel->update(['monto_comprometido' => 0, 'estatus' => 'liberado']);

                PresupuestoMovimiento::create([
                    'presupuesto_id'     => $p->id,
                    'tipo'               => 'liberacion',
                    'monto'              => $monto,
                    'saldo_gastado'      => $fresh->monto_gastado,
                    'saldo_comprometido' => $fresh->monto_comprometido,
                    'saldo_disponible'   => $fresh->monto_disponible,
                    'origen_type'        => Solicitud::class,
                    'origen_id'          => $solicitud->id,
                    'concepto'           => "Liberación: {$solicitud->folio}",
                    'actor_id'           => auth()->id(),
                    'fecha_movimiento'   => now(),
                ]);

                if ($fresh->estatus === 'agotado') {
                    $p->update(['estatus' => 'activo']);
                }
            }
        });
    }

    public function resolverAlerta(PresupuestoAlerta $alerta, string $resolucion, $user): void
    {
        if (!$user->can('presupuestos.ver')) {
            throw new AuthorizationException('No autorizado');
        }

        $alerta->resolver($resolucion);
    }

    public function detalle(Presupuesto $presupuesto): array
    {
        $presupuesto->load([
            'empresa:id,nombre',
            'area:id,nombre',
            'empleado:id,nombre_completo',
            'proyecto:id,nombre,codigo',
            'creadoPor:id,name',
            'aprobadoPor:id,name',
        ]);

        $movimientos = $presupuesto->movimientos()
            ->with('actor:id,name')
            ->limit(20)
            ->get();

        $alertasActivas = $presupuesto->alertas()
            ->pendientes()
            ->orderByDesc('created_at')
            ->get();

        $solicitudesActivas = PresupuestoSolicitud::where('presupuesto_id', $presupuesto->id)
            ->whereIn('estatus', ['comprometido', 'consumido'])
            ->with('solicitud:id,folio,estatus,monto_total,empleado_id')
            ->get();

        return [
            'presupuesto'        => $presupuesto,
            'monto_total'        => (float) $presupuesto->monto_total,
            'monto_gastado'      => (float) $presupuesto->monto_gastado,
            'monto_comprometido' => (float) $presupuesto->monto_comprometido,
            'monto_disponible'   => (float) $presupuesto->monto_disponible,
            'pct_consumido'      => $presupuesto->porcentaje_consumido,
            'severidad'          => $presupuesto->getSeveridad(),
            'dias_restantes'     => $presupuesto->dias_restantes,
            'movimientos'        => $movimientos,
            'alertas_activas'    => $alertasActivas,
            'solicitudes'        => $solicitudesActivas,
        ];
    }

    public function resumenParaSolicitud(Solicitud $solicitud): array
    {
        $presupuestos = $this->presupuestosAplicables($solicitud);

        if ($presupuestos->isEmpty()) {
            return ['sin_presupuesto' => true, 'presupuestos' => []];
        }

        return [
            'sin_presupuesto' => false,
            'presupuestos'    => $presupuestos->map(fn($p) => [
                'id'                 => $p->id,
                'nombre'             => $p->nombre,
                'tipo'               => $p->tipo,
                'periodo'            => $p->periodo,
                'monto_total'        => (float) $p->monto_total,
                'monto_gastado'      => (float) $p->monto_gastado,
                'monto_comprometido' => (float) $p->monto_comprometido,
                'monto_disponible'   => (float) $p->monto_disponible,
                'pct_usado'          => $p->porcentaje_consumido,
                'severidad'          => $p->getSeveridad(),
                'suficiente'         => (float) $p->monto_disponible >= (float) $solicitud->monto_total,
            ])->toArray(),
        ];
    }

    private function validarEntidadPorTipo(array $data): void
    {
        match ($data['tipo']) {
            'empresa'  => $this->requerirCampo($data, 'empresa_id',  'El tipo empresa requiere empresa_id'),
            'area'     => $this->requerirCampo($data, 'area_id',     'El tipo área requiere area_id'),
            'empleado' => $this->requerirCampo($data, 'empleado_id', 'El tipo empleado requiere empleado_id'),
            'proyecto' => $this->requerirCampo($data, 'proyecto_id', 'El tipo proyecto requiere proyecto_id'),
            default    => throw new \InvalidArgumentException("Tipo de presupuesto inválido: {$data['tipo']}"),
        };
    }

    private function requerirCampo(array $data, string $campo, string $mensaje): void
    {
        if (empty($data[$campo])) {
            throw new \Exception($mensaje);
        }
    }

    private function validarSolapamiento(array $data, ?int $exceptId = null): void
    {
        $query = Presupuesto::where('tipo',     $data['tipo'])
            ->where('periodo',  $data['periodo'])
            ->whereIn('estatus', ['borrador', 'activo'])
            ->where('fecha_inicio', '<=', $data['fecha_fin'])
            ->where('fecha_fin',    '>=', $data['fecha_inicio'])
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId));

        match ($data['tipo']) {
            'empresa'  => $query->where('empresa_id',  $data['empresa_id']),
            'area'     => $query->where('area_id',     $data['area_id']),
            'empleado' => $query->where('empleado_id', $data['empleado_id']),
            'proyecto' => $query->where('proyecto_id', $data['proyecto_id']),
        };

        if ($query->exists()) {
            throw new \Exception(
                'Ya existe un presupuesto activo del mismo tipo y período que se solapa con las fechas indicadas.'
            );
        }
    }

    private function verificarUmbrales(Presupuesto $p): void
    {
        $pct = $p->porcentaje_consumido;

        if ($pct >= $p->critico_porcentaje) {
            $this->crearAlertaSiNoExiste(
                $p,
                'critico',
                'danger',
                'Presupuesto crítico',
                "El presupuesto {$p->codigo} está al {$pct}% de consumo."
            );
        } elseif ($pct >= $p->alerta_porcentaje) {
            $this->crearAlertaSiNoExiste(
                $p,
                'alerta',
                'warning',
                'Presupuesto en alerta',
                "El presupuesto {$p->codigo} alcanzó el {$pct}% de consumo."
            );
        }

        if ($pct >= 100) {
            $this->crearAlertaSiNoExiste(
                $p,
                'agotado',
                'critical',
                'Presupuesto agotado',
                "El presupuesto {$p->codigo} se ha agotado."
            );
        }

        if ($p->dias_restantes !== null && $p->dias_restantes <= 7 && $p->dias_restantes >= 0) {
            $this->crearAlertaSiNoExiste(
                $p,
                'proximo_vencer',
                'warning',
                'Presupuesto próximo a vencer',
                "El presupuesto {$p->codigo} vence en {$p->dias_restantes} días."
            );
        }
    }

    private function crearAlertaSiNoExiste(
        Presupuesto $p,
        string $tipo,
        string $severidad,
        string $titulo,
        string $mensaje
    ): void {
        $existe = PresupuestoAlerta::where('presupuesto_id', $p->id)
            ->where('tipo', $tipo)
            ->where('resuelto', false)
            ->exists();

        if (!$existe) {
            PresupuestoAlerta::create([
                'presupuesto_id'      => $p->id,
                'tipo'                => $tipo,
                'severidad'           => $severidad,
                'titulo'              => $titulo,
                'mensaje'             => $mensaje,
                'porcentaje_consumido' => $p->porcentaje_consumido,
                'monto_disponible'    => $p->monto_disponible,
                'dias_restantes'      => $p->dias_restantes,
            ]);
        }
    }

    private function actualizarEstatus(Presupuesto $p): void
    {
        if ($p->estaVencido()) {
            $p->update(['estatus' => 'vencido']);
        } elseif ($p->estaAgotado()) {
            $p->update(['estatus' => 'agotado']);
        }
    }

    private function generarCodigo(string $tipo): string
    {
        $prefijo = match ($tipo) {
            'empresa'  => 'PRE-EMP',
            'area'     => 'PRE-ARE',
            'empleado' => 'PRE-IND',
            'proyecto' => 'PRE-PRY',
            default    => 'PRE',
        };

        $año = now()->year;

        $ultimo = Presupuesto::where('codigo', 'like', "{$prefijo}-{$año}-%")
            ->orderByDesc('id')
            ->value('codigo');

        $numero = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

        return sprintf('%s-%d-%04d', $prefijo, $año, $numero);
    }
}
