<?php

namespace App\Services\Proyecto;

use App\Models\Proyecto;

class ProyectoService
{
    public function create(array $data)
    {
        return Proyecto::create($data);
    }

    public function update(Proyecto $proyecto, array $data)
    {
        $proyecto->update($data);
        return $proyecto;
    }

    public function delete(Proyecto $proyecto)
    {
        $proyecto->delete();
        return true;
    }
}
