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
        'nombre', 'codigo', 'categoria',
        'created_at',
    ];
    private const ALLOWED_SORT_DIRS = ['asc', 'desc'];
    private const LIST_CACHE_TTL    = 600; // 10 min

    public function paginate(
        string $search         = '',
        string $estatus        = '',
        string $categoria      = '',
        string $vigencia       = '',
        string $sortBy         = 'nombre',
        string $sortDir        = 'asc',
        int    $perPage        = 15,
    ): LengthAwarePaginator {
        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'nombre';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'asc';
        $perPage = min($perPage, 100);

        return Concepto::query()
            ->when($search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('nombre', 'ilike', "%{$search}%")
                       ->orWhere('codigo', 'ilike', "%{$search}%")
                )
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
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    public function list(): array
    {

        $this->flushCache();
        $cacheKey = 'conceptos.list';

        return Cache::remember($cacheKey, self::LIST_CACHE_TTL, fn() =>
            Concepto::query()
                ->where('estatus', true)
                ->vigente()
                ->orderBy('nombre')
                ->get([
                    'id', 'codigo', 'nombre', 'categoria',
                    'aplica_ieps', 'aplica_iva', 'aplica_ish',
                    'vigencia_desde', 'vigencia_hasta',
                ])
                ->toArray()
        );
    }

    public function create(array $data): Concepto
    {
        $concepto = Concepto::create([
            'nombre'          => $data['nombre'],
            'codigo'          => FolioHelper::generar('CONC'),
            'categoria'       => $data['categoria']       ?? null,
            'descripcion'     => $data['descripcion']     ?? null,

            'aplica_iva'      => $data['aplica_iva']      ?? true,
            'aplica_ish'      => $data['aplica_ish']      ?? false,
            'aplica_ieps'     => $data['aplica_ieps']      ?? false,

            'vigencia_desde'  => $data['vigencia_desde']  ?? null,
            'vigencia_hasta'  => $data['vigencia_hasta']  ?? null,
            'estatus'         => $data['estatus']         ?? true,
        ]);

        $this->flushCache();

        return $concepto->refresh();
    }

    public function update(Concepto $concepto, array $data): Concepto
    {
        $concepto->update([
            'nombre'          => $data['nombre'],
            'codigo'          => $concepto->codigo,
            'categoria'       => $data['categoria']       ?? $concepto->categoria,
            'descripcion'     => $data['descripcion']     ?? $concepto->descripcion,

            'aplica_iva'      => $data['aplica_iva']      ?? $concepto->aplica_iva,
            'aplica_ieps'     => $data['aplica_ieps']      ?? $concepto->aplica_ieps,
            'aplica_ish'      => $data['aplica_ish']      ?? $concepto->aplica_ish,

            'vigencia_desde'  => $data['vigencia_desde']  ?? $concepto->vigencia_desde,
            'vigencia_hasta'  => $data['vigencia_hasta']  ?? $concepto->vigencia_hasta,
            'estatus'         => $data['estatus']         ?? $concepto->estatus,
        ]);

        $this->flushCache();

        return $concepto->refresh();
    }

    public function delete(Concepto $concepto): bool
    {
        $concepto->delete($concepto->id);

        $this->flushCache();

        return true;
    }

    public function toggle(Concepto $concepto): Concepto
    {
        $concepto->update(['estatus' => !$concepto->estatus]);
        $this->flushCache();

        return $concepto->fresh();
    }

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

    private function flushCache(): void
    {
        Cache::forget('conceptos.list.todos');
        Cache::forget('conceptos.list');
        Cache::forget('conceptos.categorias');
    }
}

