<?php

namespace App\Services\Gasto;

use App\Helpers\FolioHelper;
use App\Models\ComprobacionTarjeta;
use App\Models\Gasto;
use App\Models\Solicitud;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ComprobacionTarjetaService
{
    private const ALLOWED_SORT_COLUMNS = ['folio', 'fecha_inicio', 'fecha_fin', 'monto_total', 'created_at'];
    private const ALLOWED_SORT_DIRS = ['asc', 'desc'];
    private const LIST_CACHE_KEY     = 'solicitudes.list.activos';

    public function paginate(
        $user,
        string $search  = '',
        string $estatus = '',
        string $sortBy  = 'created_at',
        string $sortDir = 'desc',
        int    $perPage = 15
    ): LengthAwarePaginator {
        $sortBy  = in_array($sortBy, self::ALLOWED_SORT_COLUMNS, true) ? $sortBy : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS, true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return ComprobacionTarjeta::query()
            ->leftJoin('proyectos', 'proyectos.id', '=', 'comprobaciones_tarjeta.proyecto_id')
            ->leftJoin('empleados', 'empleados.id', '=', 'comprobaciones_tarjeta.empleado_id')
            ->select([
                'comprobaciones_tarjeta.*',
                'proyectos.nombre as proyecto_nombre',
                'empleados.nombre_completo as empleado_nombre'
            ])
            ->when(
                !$user->can('gastos.tarjeta.conciliar'),
                fn ($q) => $q->where(
                    'comprobaciones_tarjeta.empleado_id',
                    $user->empleado->id
                )
            )
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('comprobaciones_tarjeta.folio', 'ilike', "%{$search}%")
                        ->orWhere('proyectos.nombre', 'ilike', "%{$search}%")
                        ->orWhere('empleados.nombre_completo', 'ilike', "%{$search}%");
                });
            })
            ->when(
                $estatus,
                fn ($q) => $q->where('comprobaciones_tarjeta.estatus', $estatus)
            )
            ->orderBy("comprobaciones_tarjeta.$sortBy", $sortDir)
            ->paginate($perPage);
    }

    public function paginateConciliacion(
        $user,
        string $search = '',
        ?int $proyectoId = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 15
    ): LengthAwarePaginator {
        if (!$user->can('gastos.tarjeta.conciliar')) {
            throw new AuthorizationException(
                'No autorizado para conciliar una comprobación por tarjeta corporativa'
            );
        }

        $sortBy  = in_array($sortBy, self::ALLOWED_SORT_COLUMNS, true)
            ? $sortBy
            : 'created_at';

        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS, true)
            ? $sortDir
            : 'desc';

        $perPage = min($perPage, 100);

        return ComprobacionTarjeta::query()
            ->leftJoin('proyectos', 'proyectos.id', '=', 'comprobaciones_tarjeta.proyecto_id')
            ->leftJoin('empleados', 'empleados.id', '=', 'comprobaciones_tarjeta.empleado_id')
            ->select([
                'comprobaciones_tarjeta.*',
                'proyectos.nombre as proyecto_nombre',
                'empleados.nombre_completo as empleado_nombre'
            ])
            ->where('comprobaciones_tarjeta.estatus', 'en_revision')
            ->when(
                $proyectoId,
                fn ($q) => $q->where(
                    'comprobaciones_tarjeta.proyecto_id',
                    $proyectoId
                )
            )
            ->when(
                $fechaInicio && $fechaFin,
                fn ($q) => $q->where(function ($q2) use ($fechaInicio, $fechaFin) {
                    $q2->where('comprobaciones_tarjeta.fecha_inicio', '<=', $fechaFin)
                        ->where('comprobaciones_tarjeta.fecha_fin', '>=', $fechaInicio);
                })
            )
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('comprobaciones_tarjeta.folio', 'ilike', "%{$search}%")
                        ->orWhere('proyectos.nombre', 'ilike', "%{$search}%")
                        ->orWhere('empleados.nombre_completo', 'ilike', "%{$search}%");
                });
            })
            ->orderBy("comprobaciones_tarjeta.$sortBy", $sortDir)
            ->paginate($perPage);
    }

    public function create(array $data, $user): ComprobacionTarjeta
    {
        if (!$user->can('gastos.tarjeta.crear')) {
            throw new AuthorizationException('No autorizado para crear comprobaciones de tarjeta');
        }

        if (!$user->empleado->tarjeta_credito_corporativa_asignada) {
            throw new \Exception('No tienes tarjeta corporativa asignada');
        }

        return DB::transaction(function () use ($data, $user) {
            $comprobacion = ComprobacionTarjeta::create([
                'folio'        => FolioHelper::generar('CT'),
                'empleado_id'  => $user->empleado->id,
                'proyecto_id'  => $data['proyecto_id'] ?? null,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin'    => $data['fecha_fin'],
                'descripcion'  => $data['descripcion'],
                'monto_total'  => 0,
                'estatus'      => 'abierta'
            ]);

            app(AuditoriaService::class)->registrar([
                'gasto_id' => null,
                'evento'   => 'tarjeta_periodo_creado',
                'despues'  => [
                    'comprobacion_id' => $comprobacion->id,
                    'folio'           => $comprobacion->folio
                ]
            ]);

            return $comprobacion;
        });
    }

    public function update(ComprobacionTarjeta $comprobacion, array $data): ComprobacionTarjeta
    {
        $comprobacion->update([
            'folio'        => $comprobacion->folio,
            'empleado_id'  => $comprobacion->empleado_id,
            'proyecto_id'  => $data['proyecto_id'] ?? $comprobacion->proyecto_id,
            'fecha_inicio' => $data['fecha_inicio'] ?? $comprobacion->fecha_inicio,
            'fecha_fin'    => $data['fecha_fin'] ?? $comprobacion->fecha_fin,
            'descripcion'  => $data['descripcion'] ?? $comprobacion->descripcion,
        ]);

        return $comprobacion->load('proyecto');
    }

    public function agregarGasto(
        ComprobacionTarjeta $comprobacion,
        array $data,
        $user
    ): Gasto {

        if (!$user->can('gastos.tarjeta.crear')) {
            throw new AuthorizationException('No autorizado');
        }

        return DB::transaction(function () use ($comprobacion, $data, $user) {
            $gasto = Gasto::create([
                'comprobacion_tarjeta_id' => $comprobacion->id,
                'concepto_id'             => $data['concepto_id'],
                'fecha_gasto'             => $data['fecha_gasto'],
                'monto'                   => $data['monto'],
                'estatus'                 => 'pendiente',
            ]);

            $gasto = $gasto->fresh();

            app(GastoService::class)->subirComprobante($gasto, $user, $data['archivo_xml'], [
                'tipo'             => 'factura',
                'monto'            => 0,
                'fecha_gasto'      => $data['fecha_gasto'],
                'archivo_pdf_cfdi' => $data['archivo_pdf'] ?? null,
                'cfdi_compartido'  => $data['cfdi_compartido'] ?? false,
                'monto_override'   => $data['monto_override'] ?? null,
            ]);

            $this->recalcularMonto($comprobacion);
            return $gasto->fresh();
        });
    }

    public function eliminarGasto(ComprobacionTarjeta $comprobacion, Gasto $gasto, $user): void
    {
        if ($comprobacion->estatus !== 'abierta') {
            throw new \Exception('No se puede eliminar gastos de un periodo ya enviado');
        }

        if ($comprobacion->empleado_id !== $user->empleado->id) {
            throw new AuthorizationException('No es tu comprobación');
        }

        DB::transaction(function () use ($comprobacion, $gasto) {
            $gasto->comprobantes()->each(function ($c) {
                \Storage::disk('private')->delete($c->archivo);
                if ($c->archivo_pdf) {
                    \Storage::disk('private')->delete($c->archivo_pdf);
                }
                $c->delete();
            });

            $gasto->delete();
            $this->recalcularMonto($comprobacion);
        });
    }

    public function enviarARevision(ComprobacionTarjeta $comprobacion, $user): ComprobacionTarjeta
    {
        if ($comprobacion->estatus !== 'abierta') {
            throw new \Exception('Este periodo ya fue enviado');
        }

        if ($comprobacion->empleado_id !== $user->empleado->id) {
            throw new AuthorizationException('No es tu comprobación');
        }

        $gastos = Gasto::where('comprobacion_tarjeta_id', $comprobacion->id)
            ->whereNull('deleted_at')
            ->count();

        if ($gastos === 0) {
            throw new \Exception('Debes agregar al menos un gasto antes de enviar');
        }

        $comprobacion->update(['estatus' => 'en_revision']);

        app(AuditoriaService::class)->registrar([
            'gasto_id' => null,
            'evento'   => 'tarjeta_enviada_revision',
            'despues'  => ['comprobacion_id' => $comprobacion->id],
        ]);

        return $comprobacion;
    }

    public function conciliar(ComprobacionTarjeta $comprobacion, $user, string $accion, ?string $motivo = null)
    {
        if (!$user->can('gastos.tarjeta.conciliar')) {
            throw new AuthorizationException('No autorizado para conciliar');
        }

        if ($comprobacion->estatus !== 'en_revision') {
            throw new \Exception('Solo periodos en revisión pueden conciliarse');
        }

        if (!in_array($accion, ['conciliada', 'rechazada'], true)) {
            throw new \InvalidArgumentException("Acción inválida: {$accion}");
        }

        if ($accion === 'rechazada' && empty($motivo)) {
            throw new \Exception('El motivo de rechazo es obligatorio');
        }

        $comprobacion->update([
            'estatus'        => $accion,
            'motivo_rechazo' => $accion === 'rechazada' ? $motivo : null,
            'conciliado_por' => $user->id,
            'conciliado_en'  => now(),
        ]);

        if ($accion === 'conciliada') {
            Gasto::where('comprobacion_tarjeta_id', $comprobacion->id)
                ->update(['estatus' => 'comprobado']);
        }

        return $comprobacion;
    }

    public function crearExtension(array $data, $user): ComprobacionTarjeta
    {
        if (!$user->can('gastos.tarjeta.crear')) {
            throw new AuthorizationException('No autorizado');
        }

        $solicitud = Solicitud::findOrFail($data['solicitud_id']);

        if ($solicitud->empleado_id !== $user->empleado->id) {
            throw new AuthorizationException('No es tu solicitud');
        }

        if (!in_array($solicitud->estatus, ['Autorizado', 'Comprobado'], true)) {
            throw new \Exception('Solo puedes extender solicitudes autorizadas');
        }

        return DB::transaction(function () use ($data, $user, $solicitud) {
            return ComprobacionTarjeta::create([
                'folio'        => FolioHelper::generar('CT'),
                'empleado_id'  => $user->empleado->id,
                'proyecto_id'  => $solicitud->proyecto_id,
                'solicitud_id' => $solicitud->id,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin'    => $data['fecha_fin'],
                'descripcion'  => $data['descripcion'] ?? "Extensión de {$solicitud->folio}",
                'monto_total'  => 0,
                'es_extension' => true,
                'estatus'      => 'abierta'
            ]);
        });
    }

    private function recalcularMonto(ComprobacionTarjeta $comprobacion): void
    {
        $total = Gasto::where('comprobacion_tarjeta_id', $comprobacion->id)
            ->whereNull('deleted_at')
            ->sum('monto');

        $comprobacion->update(['monto_total' => $total]);
    }
}
