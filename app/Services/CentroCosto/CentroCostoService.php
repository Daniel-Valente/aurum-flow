<?php

namespace App\Services\CentroCosto;

use App\Helpers\FolioHelper;
use App\Models\CentroCosto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CentroCostoService
{
    private const ALLOWED_SORT_COLUMNS = ['nombre', 'codigo', 'created_at', 'estatus'];
    private const ALLOWED_SORT_DIRS    = ['asc', 'desc'];
    private const LIST_CACHE_KEY       = 'centros_costos.list.activos';
    private const LIST_CACHE_TTL       = 600;

    public function paginate(
        string $search  = '',
        string $estatus = '',
        string $sortBy  = 'created_at',
        string $sortDir = 'desc',
        int    $perPage = 15,
    ): LengthAwarePaginator {
        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return CentroCosto::query()
            ->when($search, fn($q) =>
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('codigo', 'ilike', "%{$search}%")
                  ->orWhere('cuenta_contable', 'ilike', "%{$search}%")
            )
            ->when($estatus !== '', fn($q) => $q->where('estatus', $estatus))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    public function list(): array
    {
        return Cache::remember(self::LIST_CACHE_KEY, self::LIST_CACHE_TTL, fn() =>
            CentroCosto::where('estatus', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo', 'cuenta_contable'])
                ->toArray()
        );
    }

    public function create(array $data): CentroCosto
    {
        $centroCosto = CentroCosto::create([
            'nombre'  => $data['nombre'],
            'cuenta_contable' => $data['cuenta_contable'],
            'codigo'  => FolioHelper::generar('CECO'),
            'estatus' => $data['estatus'] ?? true,
        ]);

        $this->flushCache();

        return $centroCosto;
    }

    public function update(CentroCosto $centroCosto, array $data): CentroCosto
    {
        $centroCosto->update([
            'nombre'  => $data['nombre'],
            'cuenta_contable' => $data['cuenta_contable'] ?? $centroCosto->cuenta_contable,
            'estatus' => $data['estatus'] ?? $centroCosto->estatus,
        ]);

        $this->flushCache();

        return $centroCosto;
    }

    public function delete(CentroCosto $centroCosto): bool
    {
        $centroCosto->delete();
        $this->flushCache();

        return true;
    }

    public function toggleEstatus(CentroCosto $centroCosto): CentroCosto
    {
        $centroCosto->update(['estatus' => !$centroCosto->estatus]);
        $this->flushCache();

        return $centroCosto->fresh();
    }

    private function flushCache(): void
    {
        Cache::forget(self::LIST_CACHE_KEY);
    }
}
