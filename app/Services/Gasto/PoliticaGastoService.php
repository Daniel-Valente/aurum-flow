<?php

namespace App\Services\Gasto;

use App\Models\PoliticaGasto;
use App\Models\PoliticaGastoAuditoria;
use App\Models\PoliticaGastoVersion;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PoliticaGastoService
{
    private const ALLOWED_SORT_COLUMNS = [
        'created_at', 'monto_max', 'vigencia_desde', 'vigencia_hasta',
    ];
    private const ALLOWED_SORT_DIRS = ['asc', 'desc'];

    public function paginate(
        ?int   $roleId       = null,
        ?int   $conceptoId   = null,
        string $tipoLimite   = '',
        string $vigencia     = '',
        string $estatus     = '',
        string $sortBy       = 'created_at',
        string $sortDir      = 'desc',
        int    $perPage      = 15,
    ): LengthAwarePaginator {
        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return PoliticaGasto::query()
            ->join('roles',     'roles.id',     '=', 'politicas_gastos.role_id')
            ->join('conceptos', 'conceptos.id', '=', 'politicas_gastos.concepto_id')
            ->select(
                'politicas_gastos.*',
                'roles.name       AS rol_nombre',
                'conceptos.nombre AS concepto_nombre',
                'conceptos.codigo AS concepto_codigo',
            )
            ->when($roleId,     fn($q) => $q->where('politicas_gastos.role_id',     $roleId))
            ->when($conceptoId, fn($q) => $q->where('politicas_gastos.concepto_id', $conceptoId))
            ->when($estatus,    fn($q) => $q->where('politicas_gastos.estatus', $estatus))
            ->when($tipoLimite, fn($q) => $q->where('politicas_gastos.tipo_limite', $tipoLimite))
            ->when($vigencia === 'Vigente', fn($q) =>
                $q->where(fn($q2) =>
                    $q2->whereNull('politicas_gastos.vigencia_desde')
                       ->orWhere('politicas_gastos.vigencia_desde', '<=', now())
                )
                ->where(fn($q2) =>
                    $q2->whereNull('politicas_gastos.vigencia_hasta')
                       ->orWhere('politicas_gastos.vigencia_hasta', '>=', now())
                )
            )
            ->when($vigencia === 'Futura', fn($q) =>
                $q->where('politicas_gastos.vigencia_desde', '>', now())
            )
            ->when($vigencia === 'Expirada', fn($q) =>
                $q->where('politicas_gastos.vigencia_hasta', '<', now())
            )
            ->when($vigencia === 'Sin vigencia', fn($q) =>
                $q->whereNull('politicas_gastos.vigencia_desde')
                  ->whereNull('politicas_gastos.vigencia_hasta')
            )
            ->orderBy("politicas_gastos.{$sortBy}", $sortDir)
            ->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Lista plana para selects / dropdowns
    // -------------------------------------------------------------------------

    public function list(?int $roleId = null): array
    {
        return PoliticaGasto::query()
            ->with(['role:id,name', 'concepto:id,nombre,codigo'])
            ->vigente()
            ->when($roleId, fn($q) => $q->where('role_id', $roleId))
            ->orderBy('created_at', 'desc')
            ->get([
                'id', 'role_id', 'concepto_id',
                'monto_max', 'tipo_limite',
                'permite_excepcion',
                'vigencia_desde', 'vigencia_hasta',
            ])
            ->toArray();
    }

    // -------------------------------------------------------------------------
    // Historial de versiones para el modal "Historial de Versiones"
    // Columnas: VERSION | LÍMITE | TIPO | VIGENCIA | ESTATUS | CREADO | ACTOR
    // -------------------------------------------------------------------------

    public function versiones(PoliticaGasto $politica): Collection
    {
        return PoliticaGastoVersion::query()
            ->where('politica_id', $politica->id)
            // JOIN para obtener el nombre del actor (columna ACTOR del modal)
            ->leftJoin('users',     'users.id',         '=', 'politicas_gastos_versiones.creado_por')
            ->leftJoin('empleados', 'empleados.user_id','=', 'users.id')
            ->select(
                'politicas_gastos_versiones.id',
                'politicas_gastos_versiones.monto_max',
                'politicas_gastos_versiones.tipo_limite',
                'politicas_gastos_versiones.permite_excepcion',
                'politicas_gastos_versiones.vigencia_desde',
                'politicas_gastos_versiones.vigencia_hasta',
                'politicas_gastos_versiones.estatus',
                'politicas_gastos_versiones.motivo',
                'politicas_gastos_versiones.created_at',
                // "Admin Aurum" como aparece en la imagen — usa nombre_completo o el name del user
                DB::raw("COALESCE(empleados.nombre_completo, users.name, 'Sistema') AS actor_nombre"),
            )
            ->orderByDesc('politicas_gastos_versiones.id')
            ->get()
            ->values()
            // Número de versión legible (#9, #8…) basado en el id de la versión
            ->map(fn($v) => tap($v, fn($v) => $v->version_numero = '#' . $v->id));
    }

    // -------------------------------------------------------------------------
    // Crear política — valida duplicado, crea versión inicial y auditoría
    // -------------------------------------------------------------------------

    public function create(array $data, $user): PoliticaGasto
    {
        // Validación FUERA de la transacción — no abre TX si ya existe
        $exists = PoliticaGasto::where('role_id',    $data['role_id'])
            ->where('concepto_id', $data['concepto_id'])
            ->where('tipo_limite', $data['tipo_limite'])
            ->vigente()
            ->exists();

        if ($exists) {
            throw new \Exception(
                'Ya existe una política vigente para este rol, concepto y tipo de límite'
            );
        }

        return DB::transaction(function () use ($data, $user) {
            $politica = PoliticaGasto::create([
                'role_id'           => $data['role_id'],
                'concepto_id'       => $data['concepto_id'],
                'tipo_limite'       => $data['tipo_limite'],
                'monto_max'         => $data['monto_max'],
                'permite_excepcion' => $data['permite_excepcion'] ?? false,
                'vigencia_desde'    => $data['vigencia_desde']    ?? null,
                'vigencia_hasta'    => $data['vigencia_hasta']    ?? null,
            ]);

            $version = PoliticaGastoVersion::create([
                'politica_id'       => $politica->id,
                'role_id'           => $data['role_id'],
                'concepto_id'       => $data['concepto_id'],
                'tipo_limite'       => $data['tipo_limite'],
                'monto_max'         => $data['monto_max'],
                'permite_excepcion' => $data['permite_excepcion'] ?? false,
                'vigencia_desde'    => $data['vigencia_desde']    ?? null,
                'vigencia_hasta'    => $data['vigencia_hasta']    ?? null,
                'creado_por'        => $user->id,
                'estatus'           => 'Aprobada',
                'motivo'            => 'Creación inicial',
            ]);

            PoliticaGastoAuditoria::create([
                'politica_id'   => $politica->id,
                'version_id'    => $version->id,
                'evento'        => 'created',
                'actor_id'      => $user->id,
                'datos_despues' => $politica->toArray(),
            ]);

            return $politica->load(['role:id,name', 'concepto:id,nombre,codigo']);
        });
    }

    // -------------------------------------------------------------------------
    // Actualizar — genera nueva entrada en el historial de versiones
    // -------------------------------------------------------------------------

    public function update(PoliticaGasto $politica, array $data, $user): PoliticaGasto
    {
        return DB::transaction(function () use ($politica, $data, $user) {
            // Captura "antes" ANTES del update — getOriginal() es poco confiable
            $antes = $politica->toArray();

            $politica->update([
                'role_id'           => $data['role_id'],
                'concepto_id'       => $data['concepto_id'],
                'tipo_limite'       => $data['tipo_limite'],
                'monto_max'         => $data['monto_max'],
                'permite_excepcion' => $data['permite_excepcion'] ?? $politica->permite_excepcion,
                'vigencia_desde'    => $data['vigencia_desde']    ?? null,
                'vigencia_hasta'    => $data['vigencia_hasta']    ?? null,
            ]);

            // Nueva versión — aparecerá en el modal como #N
            $version = PoliticaGastoVersion::create([
                'politica_id'       => $politica->id,
                'role_id'           => $data['role_id'],
                'concepto_id'       => $data['concepto_id'],
                'tipo_limite'       => $data['tipo_limite'],
                'monto_max'         => $data['monto_max'],
                'permite_excepcion' => $data['permite_excepcion'] ?? $politica->permite_excepcion,
                'vigencia_desde'    => $data['vigencia_desde']    ?? null,
                'vigencia_hasta'    => $data['vigencia_hasta']    ?? null,
                'creado_por'        => $user->id,
                'estatus'           => 'Aprobada',
                'motivo'            => $data['motivo'] ?? 'Actualización',
            ]);

            PoliticaGastoAuditoria::create([
                'politica_id'   => $politica->id,
                'version_id'    => $version->id,
                'evento'        => 'updated',
                'actor_id'      => $user->id,
                'datos_antes'   => $antes,
                // Sin fresh() — construimos "despues" con los datos ya en memoria
                'datos_despues' => array_merge($antes, [
                    'monto_max'         => $data['monto_max'],
                    'tipo_limite'       => $data['tipo_limite'],
                    'permite_excepcion' => $data['permite_excepcion'] ?? $politica->permite_excepcion,
                    'vigencia_desde'    => $data['vigencia_desde'] ?? null,
                    'vigencia_hasta'    => $data['vigencia_hasta'] ?? null,
                ]),
            ]);

            return $politica->load(['role:id,name', 'concepto:id,nombre,codigo']);
        });
    }

    // -------------------------------------------------------------------------
    // Eliminar con soft delete + auditoría
    // -------------------------------------------------------------------------

    public function delete(PoliticaGasto $politica, $user): bool
    {
        return DB::transaction(function () use ($politica, $user) {
            $antes = $politica->toArray();

            $politica->delete(); // SoftDelete

            PoliticaGastoAuditoria::create([
                'politica_id'   => $politica->id,
                'evento'        => 'deleted',
                'actor_id'      => $user->id,
                'datos_antes'   => $antes,
                'datos_despues' => null,
            ]);

            return true;
        });
    }

    // -------------------------------------------------------------------------
    // Consultas para ValidadorGastosService
    // -------------------------------------------------------------------------

    /**
     * Política vigente para un rol/concepto/fecha — validación individual.
     */
    public function getPoliticaAplicable(int $roleId, int $conceptoId, $fecha): ?PoliticaGastoVersion
    {
        return PoliticaGastoVersion::where('role_id',    $roleId)
            ->where('concepto_id', $conceptoId)
            ->where('estatus',     'Aprobada')
            ->where(fn($q) =>
                $q->whereNull('vigencia_desde')
                  ->orWhere('vigencia_desde', '<=', $fecha)
            )
            ->where(fn($q) =>
                $q->whereNull('vigencia_hasta')
                  ->orWhere('vigencia_hasta', '>=', $fecha)
            )
            ->latest()
            ->first();
    }

    /**
     * Políticas para múltiples conceptos en una sola query — evita N+1
     * en ValidadorGastosService::validarSolicitud().
     */
    public function getPoliticasBulk(int $roleId, array $conceptoIds, $fecha): Collection
    {
        return PoliticaGastoVersion::where('role_id', $roleId)
            ->whereIn('concepto_id', $conceptoIds)
            ->where('estatus', 'Aprobada')
            ->where(fn($q) =>
                $q->whereNull('vigencia_desde')
                  ->orWhere('vigencia_desde', '<=', $fecha)
            )
            ->where(fn($q) =>
                $q->whereNull('vigencia_hasta')
                  ->orWhere('vigencia_hasta', '>=', $fecha)
            )
            ->latest()
            ->get()
            ->keyBy('concepto_id'); // O(1) lookup en el loop del validador
    }
}
