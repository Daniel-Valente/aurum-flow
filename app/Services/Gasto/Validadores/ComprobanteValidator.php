<?php

namespace App\Services\Gasto\Validadores;

use App\Models\Gasto;

class ComprobanteValidator
{
    public function validar(Gasto $gasto, array $data): void
    {
        $concepto = $gasto->concepto;

        // 🔥 requiere factura
        if ($concepto->requiere_factura && empty($data['uuid'])) {
            throw new \Exception('Este concepto requiere factura (UUID)');
        }

        // 🔥 requiere comprobante
        if ($concepto->requiere_comprobante && empty($data['archivo'])) {
            throw new \Exception('Debe subir comprobante');
        }

        // 🔥 monto no puede exceder
        if (!empty($data['monto']) && $data['monto'] > $gasto->monto) {
            throw new \Exception('El monto excede el gasto');
        }
    }
}
