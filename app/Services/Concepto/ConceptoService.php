<?php

namespace App\Services\Concepto;

use App\Models\Concepto;

class ConceptoService
{
    public function create(array $data)
    {
        return Concepto::create($data);
    }

    public function update(Concepto $concepto, array $data)
    {
        $concepto->update($data);
        return $concepto;
    }

    public function delete(Concepto $concepto)
    {
        $concepto->delete();
        return true;
    }
}
