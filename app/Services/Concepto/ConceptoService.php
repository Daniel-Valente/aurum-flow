<?php

namespace App\Services\Concepto;

use App\Helpers\FolioHelper;
use App\Models\Concepto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class ConceptoService
{
    private const ALLOWED_SORT_COLUMNS = [
        'nombre', 'codigo', 'categoria', 'tipo_aplicacion',
        'orden', 'created_at',
    ];
    private const ALLOWED_SORT_DIRS = ['asc', 'desc'];
    private const LIST_CACHE_TTL    = 600; // 10 min

    // -------------------------------------------------------------------------
    // Listado paginado
    // -------------------------------------------------------------------------

    public function paginate(
        string $search         = '',
        string $tipoAplicacion = '',
        string $estatus        = '',
        string $categoria      = '',
        string $vigencia       = '',
        ?int   $rolId          = null,
        string $sortBy         = 'orden',
        string $sortDir        = 'asc',
        int    $perPage        = 15,
    ): LengthAwarePaginator {
        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'orden';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'asc';
        $perPage = min($perPage, 100);

        return Concepto::query()
            ->with('roles')
            ->when($search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('nombre', 'ilike', "%{$search}%")
                       ->orWhere('codigo', 'ilike', "%{$search}%")
                )
            )
            ->when($tipoAplicacion, fn($q) =>
                $q->where('tipo_aplicacion', $tipoAplicacion)
            )
            ->when($estatus !== '', fn($q) =>
                $q->where('estatus', $estatus)
            )
            ->when($categoria, fn($q) =>
                $q->where('categoria', $categoria)
            )
            ->when($vigencia === 'vigentes', fn($q) =>
                $q->where(fn($q2) =>
                    $q2->whereNull('vigencia_desde')
                       ->orWhere('vigencia_desde', '<=', now())
                )
                ->where(fn($q2) =>
                    $q2->whereNull('vigencia_hasta')
                       ->orWhere('vigencia_hasta', '>=', now())
                )
            )
            ->when($vigencia === 'no_vigentes', fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('vigencia_desde', '>', now())
                       ->orWhere('vigencia_hasta', '<', now())
                )
            )
            ->when($rolId, fn($q) =>
                $q->whereHas('roles', fn($r) => $r->where('roles.id', $rolId))
            )
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Lista plana para selects / dropdowns (cacheada)
    // -------------------------------------------------------------------------

    public function list(?int $rolId = null): array
    {
        $cacheKey = 'conceptos.list.' . ($rolId ?? 'todos');

        return Cache::remember($cacheKey, self::LIST_CACHE_TTL, fn() =>
            Concepto::query()
                ->with('roles')
                ->where('estatus', true)
                ->vigente()
                ->when($rolId, fn($q) =>
                    $q->where(fn($q2) =>
                        // Conceptos asignados a este rol O sin restricción de rol
                        $q2->whereHas('roles', fn($r) => $r->where('roles.id', $rolId))
                           ->orWhereDoesntHave('roles')
                    )
                )
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get([
                    'id', 'codigo', 'nombre', 'categoria',
                    'tipo_aplicacion', 'aplica_iva', 'tope_referencia',
                    'vigencia_desde', 'vigencia_hasta',
                ])
                ->toArray()
        );
    }

    // -------------------------------------------------------------------------
    // Crear
    // -------------------------------------------------------------------------

    public function create(array $data): Concepto
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $concepto = Concepto::create([
            'nombre'          => $data['nombre'],
            'codigo'          => FolioHelper::generar('CONC'),
            'categoria'       => $data['categoria']       ?? null,
            'tipo_aplicacion' => $data['tipo_aplicacion'] ?? 'Diario',
            'descripcion'     => $data['descripcion']     ?? null,
            'orden'           => $data['orden']           ?? 0,

            // Naturaleza fiscal del concepto (no regla por rol)
            'aplica_iva'      => $data['aplica_iva']      ?? true,
            'tope_referencia' => $data['tope_referencia'] ?? null,

            'vigencia_desde'  => $data['vigencia_desde']  ?? null,
            'vigencia_hasta'  => $data['vigencia_hasta']  ?? null,
            'estatus'         => $data['estatus']         ?? true,
        ]);

        if (!empty($roles)) {
            $ids = Role::whereIn('name', $roles)->pluck('id');
            $concepto->roles()->sync($ids);
        }

        $this->flushCache();

        return $concepto->load('roles');
    }

    // -------------------------------------------------------------------------
    // Actualizar
    // -------------------------------------------------------------------------

    public function update(Concepto $concepto, array $data): Concepto
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $concepto->update([
            'nombre'          => $data['nombre'],
            'codigo'          => strtoupper(trim($data['codigo'])),
            'categoria'       => $data['categoria']       ?? $concepto->categoria,
            'tipo_aplicacion' => $data['tipo_aplicacion'] ?? $concepto->tipo_aplicacion,
            'descripcion'     => $data['descripcion']     ?? $concepto->descripcion,
            'orden'           => $data['orden']           ?? $concepto->orden,

            'aplica_iva'      => $data['aplica_iva']      ?? $concepto->aplica_iva,
            'tope_referencia' => $data['tope_referencia'] ?? $concepto->tope_referencia,

            'vigencia_desde'  => $data['vigencia_desde']  ?? $concepto->vigencia_desde,
            'vigencia_hasta'  => $data['vigencia_hasta']  ?? $concepto->vigencia_hasta,
            'estatus'         => $data['estatus']         ?? $concepto->estatus,
        ]);

        // sync vacío = desvincula todos los roles si se envía array vacío
        $ids = Role::whereIn('name', $roles)->pluck('id');
        $concepto->roles()->sync($ids);

        $this->flushCache();

        return $concepto->load('roles');
    }

    // -------------------------------------------------------------------------
    // Eliminar
    // -------------------------------------------------------------------------

    public function delete(Concepto $concepto): bool
    {
        $concepto->roles()->sync([]);
        $concepto->delete($concepto->id);

        $this->flushCache();

        return true;
    }

    // -------------------------------------------------------------------------
    // Toggle estatus
    // -------------------------------------------------------------------------

    public function toggle(Concepto $concepto): Concepto
    {
        $concepto->update(['estatus' => !$concepto->estatus]);
        $this->flushCache();

        return $concepto->fresh();
    }

    // -------------------------------------------------------------------------
    // Catálogos auxiliares para filtros
    // -------------------------------------------------------------------------

    public function categorias(): array
    {
        return Cache::remember('conceptos.categorias', self::LIST_CACHE_TTL, fn() =>
            Concepto::whereNotNull('categoria')
                ->where('categoria', '!=', '')
                ->distinct()
                ->orderBy('categoria')
                ->pluck('categoria')
                ->toArray()
        );
    }

    // -------------------------------------------------------------------------
    // Cache
    // -------------------------------------------------------------------------

    private function flushCache(): void
    {
        Cache::forget('conceptos.list.todos');
        Cache::forget('conceptos.categorias');

        Role::pluck('id')->each(fn($id) =>
            Cache::forget("conceptos.list.{$id}")
        );
    }
}

