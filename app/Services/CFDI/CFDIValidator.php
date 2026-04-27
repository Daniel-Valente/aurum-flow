<?php

namespace App\Services\CFDI;

use App\Models\Gasto;

class CFDIValidator
{
    public function validar(array $cfdiData, Gasto $gasto): void
    {
        if (empty($cfdiData['uuid'])) {
            throw new \Exception('CFDI sin UUID');
        }

        // ✅ Comparación de floats con tolerancia de 1 centavo
        // != con floats es impreciso por representación binaria (0.1 + 0.2 !== 0.3)
        if (abs($cfdiData['total'] - (float) $gasto->monto) > 0.01) {
            throw new \Exception(sprintf(
                'El monto del CFDI (%.2f) no coincide con el gasto (%.2f)',
                $cfdiData['total'],
                $gasto->monto
            ));
        }

        $rfcEmpresa = config('app.rfc_empresa');

        if ($rfcEmpresa && $cfdiData['rfc_receptor'] !== strtoupper($rfcEmpresa)) {
            throw new \Exception(sprintf(
                'RFC receptor "%s" no coincide con la empresa "%s"',
                $cfdiData['rfc_receptor'],
                strtoupper($rfcEmpresa)
            ));
        }
    }
}
