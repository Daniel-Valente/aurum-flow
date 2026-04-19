<?php

namespace App\Services\CFDI;

use App\Models\Gasto;

class CFDIValidator
{
    public function validar(array $cfdiData, Gasto $gasto): void
    {
        // UUID obligatorio
        if (empty($cfdiData['uuid'])) {
            throw new \Exception('CFDI sin UUID');
        }

        // monto coincide
        if ($cfdiData['total'] != $gasto->monto) {
            throw new \Exception('El monto del CFDI no coincide con el gasto');
        }

        // 🔥 opcional: validar RFC empresa
        $rfcEmpresa = config('app.rfc_empresa');

        if ($rfcEmpresa && $cfdiData['rfc_receptor'] !== $rfcEmpresa) {
            throw new \Exception('RFC receptor no coincide con la empresa');
        }
    }
}
