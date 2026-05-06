<?php

namespace App\Services\CFDI;

use App\Models\Gasto;

class CFDIValidator
{
    public function validar(array $cfdi, Gasto $gasto): void
    {
        if (empty($cfdi['uuid'])) {
            throw new \Exception('CFDI inválido');
        }

        if (abs($cfdi['total'] - $gasto->monto) > 0.01) {
            throw new \Exception('Monto CFDI no coincide con gasto');
        }

        $rfcEmpresa = strtoupper(config('app.rfc_empresa'));

        if ($cfdi['rfc_receptor'] !== $rfcEmpresa) {
            throw new \Exception('RFC receptor incorrecto');
        }
    }
}
