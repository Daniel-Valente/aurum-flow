<?php

namespace App\Services\PoliticaGasto;

use App\Models\PoliticaGasto;

class PoliticaGastoService
{
    public function create(array $data)
    {
        $exists = PoliticaGasto::where('role_id', $data['role_id'])
            ->where('concepto_id', $data['concepto_id'])
            ->where('tipo_limite', $data['tipo_limite'])
            ->vigente()
            ->exists();

        if ($exists) {
            throw new \Exception('Ya existe una política vigente para este rol y concepto');
        }

        return PoliticaGasto::create($data);
    }

    public function update(PoliticaGasto $politica, array $data)
    {
        $politica->update($data);
        return $politica;
    }

    public function delete(PoliticaGasto $politica)
    {
        return $politica->delete();
    }

    public function getPoliticaAplicable($roleId, $conceptoId)
    {
        return PoliticaGasto::where('role_id', $roleId)
            ->where('concepto_id', $conceptoId)
            ->vigente()
            ->latest('vigencia_desde')
            ->first();
    }
}
