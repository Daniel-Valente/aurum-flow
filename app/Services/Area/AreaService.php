<?php

namespace App\Services\Area;

use App\Models\Area;

class AreaService
{
    public function create(array $data)
    {
        return Area::create($data);
    }

    public function update(Area $area, array $data)
    {
        $area->update($data);
        return $area;
    }

    public function delete(Area $area)
    {
        $area->delete();
        return true;
    }
}
