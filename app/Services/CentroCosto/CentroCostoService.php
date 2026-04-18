<?php

namespace App\Services\CentroCosto;

use App\Models\CentroCosto;

class CentroCostoService
{
    public function create(array $data)
    {
        return CentroCosto::create($data);
    }

    public function update(CentroCosto $centroCosto, array $data)
    {
        $centroCosto->update($data);
        return $centroCosto;
    }

    public function delete(CentroCosto $centroCosto)
    {
        $centroCosto->delete();
        return true;
    }
}
