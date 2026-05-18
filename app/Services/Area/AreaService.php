<?php

namespace App\Services\Area;

use App\Models\Area;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class AreaService
{
    private const ALLOWED_SORT_COLUMNS = ['nombre', 'codigo', 'created_at', 'estatus'];
    private const ALLOWED_SORT_DIRS    = ['asc', 'desc'];
    private const LIST_CACHE_KEY       = 'areas.list.activas';
    private const LIST_CACHE_TTL       = 600; // 10 min

    public function paginate(
        string $search  = '',
        string $estatus = '',
        string $sortBy  = 'created_at',
        string $sortDir = 'desc',
        int    $perPage = 15,
    ): LengthAwarePaginator {
        // Whitelist para evitar SQL injection en ORDER BY
        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return Area::query()
            ->join('empresas','empresas.id',  '=', 'areas.empresa_id')
            ->select(
                'areas.*',
                'empresas.nombre as empresa_nombre'
            )
            ->when($search, fn($q) =>
                // ILIKE nativo de PostgreSQL — sin LOWER(), usa índice trgm
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('codigo', 'ilike', "%{$search}%")
            )
            ->when($estatus !== '', fn($q) => $q->where('estatus', $estatus))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    public function list(): array
    {
        return Cache::remember(self::LIST_CACHE_KEY, self::LIST_CACHE_TTL, fn() =>
            Area::where('estatus', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo'])
                ->toArray()
        );
    }

    public function create(array $data): Area
    {
        $area = Area::create([
            'nombre'     => $data['nombre'],
            'codigo'     => $data['codigo'],
            'empresa_id' => $data['empresa_id'],
            'estatus'    => $data['estatus'] ?? true,
        ]);

        $this->flushCache();

        return $area;
    }

    public function update(Area $area, array $data): Area
    {
        $area->update([
            'nombre'     => $data['nombre'],
            'codigo'     => $data['codigo'],
            'empresa_id' => $data['empresa_id'],
            'estatus'    => $data['estatus'] ?? $area->estatus,
        ]);

        $this->flushCache();

        return $area;
    }

    public function delete(Area $area): bool
    {
        $area->delete();
        $this->flushCache();

        return true;
    }

    public function toggleEstatus(Area $area): Area
    {
        $area->update(['estatus' => !$area->estatus]);
        $this->flushCache();

        return $area->fresh();
    }

    private function flushCache(): void
    {
        Cache::forget(self::LIST_CACHE_KEY);
    }
}
