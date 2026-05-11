<?php

namespace App\Services\Gasto;

use App\Models\PoliticaGasto;
use App\Models\PoliticaGastoAuditoria;
use App\Models\PoliticaGastoVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PoliticaGastoService
{
    private const ALLOWED_SORT_COLUMNS = [
        'created_at', 'monto_max', 'vigencia_desde', 'vigencia_hasta',
    ];
    private const ALLOWED_SORT_DIRS  = ['asc', 'desc'];
    private const LIST_CACHE_KEY     = 'politicas.list.activas';
    private const LIST_CACHE_TTL     = 600; // 10 min

    // -------------------------------------------------------------------------
    // Listado paginado con joins para nombre de rol y concepto
    // -------------------------------------------------------------------------

    public function paginate(
        ?int   $roleId      = null,
        ?int   $conceptoId  = null,
        string $tipoLimite  = '',
        string $vigencia    = '',
        string $estatus     = '',
        string $sortBy      = 'created_at',
        string $sortDir     = 'desc',
        int    $perPage     = 15,
    ): LengthAwarePaginator {
        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return PoliticaGasto::query()
            ->join('roles',     'roles.id',     '=', 'politicas_gastos.role_id')
            ->join('conceptos', 'conceptos.id', '=', 'politicas_gastos.concepto_id')
            ->select(
                'politicas_gastos.*',
                'roles.name        AS rol_nombre',
                'conceptos.nombre  AS concepto_nombre',
                'conceptos.codigo  AS concepto_codigo',
            )
            ->when($roleId,     fn($q) => $q->where('politicas_gastos.role_id',     $roleId))
            ->when($conceptoId, fn($q) => $q->where('politicas_gastos.concepto_id', $conceptoId))
            ->when($estatus !== '', fn($q) =>
                $q->where('politicas_gastos.estatus', filter_var($estatus, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $estatus)
            )
            ->when($tipoLimite, fn($q) =>
                $q->where('politicas_gastos.tipo_limite', $tipoLimite)
            )
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
    // Lista plana para selects / dropdowns (con cache)
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
                'monto_libre', 'monto_comprobante', 'monto_factura',
                'valida_sat', 'acumulable_dia', 'propina_max_porcentaje',
                'permite_excepcion', 'permite_propina',
                'vigencia_desde', 'vigencia_hasta',
            ])
            ->toArray();
    }

    // -------------------------------------------------------------------------
    // Historial de versiones (modal "Historial de Versiones")
    // Columnas: # | LÍMITE | TIPO | VIGENCIA | MONTOS | ESTATUS | ACTOR
    // -------------------------------------------------------------------------

    public function versiones(PoliticaGasto $politica): Collection
    {
        return PoliticaGastoVersion::query()
            ->where('politica_id', $politica->id)
            ->leftJoin('users',     'users.id',          '=', 'politicas_gastos_versiones.creado_por')
            ->leftJoin('empleados', 'empleados.user_id', '=', 'users.id')
            ->select(
                'politicas_gastos_versiones.id',
                'politicas_gastos_versiones.monto_max',
                'politicas_gastos_versiones.tipo_limite',
                'politicas_gastos_versiones.monto_libre',
                'politicas_gastos_versiones.monto_comprobante',
                'politicas_gastos_versiones.monto_factura',
                'politicas_gastos_versiones.valida_sat',
                'politicas_gastos_versiones.acumulable_dia',
                'politicas_gastos_versiones.permite_excepcion',
                'politicas_gastos_versiones.vigencia_desde',
                'politicas_gastos_versiones.vigencia_hasta',
                'politicas_gastos_versiones.permite_propina',
                'politicas_gastos_versiones.propina_max_porcentaje',
                'politicas_gastos_versiones.estatus',
                'politicas_gastos_versiones.motivo',
                'politicas_gastos_versiones.created_at',
                DB::raw("COALESCE(empleados.nombre_completo, users.name, 'Sistema') AS actor_nombre"),
            )
            ->orderByDesc('politicas_gastos_versiones.id')
            ->get()
            ->values()
            ->map(fn($v) => tap($v, fn($v) => $v->version_numero = '#' . $v->id));
    }

    // -------------------------------------------------------------------------
    // Crear — valida duplicado, crea versión inicial y auditoría
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
            throw new \App\Exceptions\PoliticaDuplicadaException(
                'Ya existe una política vigente para este rol, concepto y tipo de límite.'
            );
        }

        return DB::transaction(function () use ($data, $user) {
            $campos = $this->camposDesdeData($data);

            $politica = PoliticaGasto::create($campos);

            $version = PoliticaGastoVersion::create(array_merge($campos, [
                'politica_id' => $politica->id,
                'creado_por'  => $user->id,
                'estatus'     => 'Aprobada',
                'approved_at' => now(),
                'motivo'      => 'Creación inicial',
            ]));

            PoliticaGastoAuditoria::create([
                'politica_id'   => $politica->id,
                'version_id'    => $version->id,
                'evento'        => 'created',
                'actor_id'      => $user->id,
                'origen'        => $data['origen'] ?? 'manual',
                'datos_antes'   => null,
                'datos_despues' => $politica->toArray(),
            ]);

            $this->flushCache();

            return $politica->load(['role:id,name', 'concepto:id,nombre,codigo']);
        });
    }

    // -------------------------------------------------------------------------
    // Actualizar — genera nueva entrada en el historial de versiones
    // -------------------------------------------------------------------------

    public function update(PoliticaGasto $politica, array $data, $user): PoliticaGasto
    {
        return DB::transaction(function () use ($politica, $data, $user) {
            // Captura "antes" ANTES del update — getOriginal() es poco confiable después del save
            $antes = $politica->toArray();

            $campos = $this->camposDesdeData($data, $politica);

            $politica->update($campos);

            $version = PoliticaGastoVersion::create(array_merge($campos, [
                'politica_id' => $politica->id,
                'creado_por'  => $user->id,
                'estatus'     => 'Aprobada',
                'approved_at' => now(),
                'motivo'      => $data['motivo'] ?? 'Actualización',
            ]));

            PoliticaGastoAuditoria::create([
                'politica_id'   => $politica->id,
                'version_id'    => $version->id,
                'evento'        => 'updated',
                'actor_id'      => $user->id,
                'origen'        => $data['origen'] ?? 'manual',
                'datos_antes'   => $antes,
                'datos_despues' => array_merge($antes, $campos),
            ]);

            $this->flushCache();

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

            $politica->delete($politica->id); // SoftDelete

            PoliticaGastoAuditoria::create([
                'politica_id'   => $politica->id,
                'version_id'    => null,
                'evento'        => 'deleted',
                'actor_id'      => $user->id,
                'origen'        => 'manual',
                'datos_antes'   => $antes,
                'datos_despues' => null,
            ]);

            $this->flushCache();

            return true;
        });
    }

    // -------------------------------------------------------------------------
    // Toggle estatus — CORREGIDO: evento 'status_changed' (no 'deleted')
    // -------------------------------------------------------------------------

    public function toggleEstatus(PoliticaGasto $politica, $user): PoliticaGasto
    {
        $antes = $politica->toArray();

        $politica->update(['estatus' => !$politica->estatus]);

        PoliticaGastoAuditoria::create([
            'politica_id'   => $politica->id,
            'version_id'    => null,
            'evento'        => 'status_changed', // ← corregido (antes decía 'deleted')
            'actor_id'      => $user->id,
            'origen'        => 'manual',
            'datos_antes'   => $antes,
            'datos_despues' => ['estatus' => $politica->estatus],
        ]);

        $this->flushCache();

        return $politica->fresh();
    }

    // -------------------------------------------------------------------------
    // Consultas para ValidadorGastosService
    // -------------------------------------------------------------------------

    /**
     * Versión aprobada y vigente para un rol/concepto en una fecha — validación individual.
     * Retorna la versión más reciente que aplique.
     */
    public function getPoliticaAplicable(int $roleId, int $conceptoId, $fecha): ?PoliticaGastoVersion
    {
        return PoliticaGastoVersion::where('role_id', $roleId)
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
     *
     * Retorna Collection indexada por concepto_id para lookup O(1).
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
            ->keyBy('concepto_id');
    }

    /**
     * Determina el nivel de documento requerido para un monto dado.
     * Delega al modelo para mantener la lógica centralizada.
     *
     * @return 'ninguno'|'comprobante'|'cfdi'
     */
    public function nivelDocumentoRequerido(PoliticaGasto $politica, float $monto): string
    {
        return $politica->evaluarComprobacion($monto);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Construye el array de campos comunes para create y update.
     * Cuando $politica no es null, usa sus valores actuales como fallback.
     */
    private function camposDesdeData(array $data, ?PoliticaGasto $politica = null): array
    {
        return [
            'role_id'                => $data['role_id'],
            'concepto_id'            => $data['concepto_id'],
            'tipo_limite'            => $data['tipo_limite'],

            'monto_max'              => $data['monto_max'],

            // Tramos documentales — null = tramo no configurado para ese nivel
            'monto_libre'            => $data['monto_libre']       ?? ($politica?->monto_libre       ?? null),
            'monto_comprobante'      => $data['monto_comprobante'] ?? ($politica?->monto_comprobante ?? null),
            'monto_factura'          => $data['monto_factura']     ?? ($politica?->monto_factura     ?? null),
            'propina_max_porcentaje' => $data['propina_max_porcentaje']     ?? ($politica?->monto_factura     ?? null),

            'valida_sat'             => $data['valida_sat']        ?? ($politica?->valida_sat        ?? false),
            'acumulable_dia'         => $data['acumulable_dia']    ?? ($politica?->acumulable_dia    ?? true),
            'permite_excepcion'      => $data['permite_excepcion'] ?? ($politica?->permite_excepcion ?? false),
            'permite_propina'        => $data['permite_propina'] ?? ($politica?->propina_max_porcentaje ?? false),

            'vigencia_desde'         => $data['vigencia_desde']    ?? null,
            'vigencia_hasta'         => $data['vigencia_hasta']    ?? null,
        ];
    }

    private function flushCache(): void
    {
        Cache::forget(self::LIST_CACHE_KEY);
    }
}
