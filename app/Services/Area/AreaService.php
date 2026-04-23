<?php

namespace App\Services\Area;

use App\Models\Area;

class AreaService
{
    public function paginate(string $search = '', string $estatus = '', int $perPage = 15)
    {
        return Area::query()
            ->when($search, fn($q) => $q->where('nombre', 'like', "%{$search}%")
                ->orWhere('codigo', 'like', "%{$search}%"))
            ->when($estatus !== '', fn($q) => $q->where('estatus', $estatus))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Area
    {
        return Area::create($data);
    }

    public function update(Area $area, array $data): Area
    {
        $area->update($data);
        return $area;
    }

    public function delete(Area $area): bool
    {
        $area->delete();
        return true;
    }

    public function toggleEstatus(Area $area): Area
    {
        $area->update(['estatus' => !$area->estatus]);
        return $area->fresh();
    }
}
