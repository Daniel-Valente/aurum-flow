<?php

namespace App\Models\Traits;

trait EvaluaComprobacion
{
    public function evaluarComprobacion(float $monto): string
    {
        if ($monto > (float) $this->monto_max) {
            return 'excede';
        }

        $sinTramos = $this->monto_libre       === null
                  && $this->monto_comprobante === null
                  && $this->monto_factura     === null;

        if ($sinTramos) {
            return 'ninguno';
        }

        if ($this->monto_libre !== null && $monto <= (float) $this->monto_libre) {
            return 'ninguno';
        }

        if ($this->monto_comprobante !== null && $monto <= (float) $this->monto_comprobante) {
            return 'ticket';
        }

        if ($this->monto_factura !== null) {
            return 'cfdi';
        }

        return 'ticket';
    }
}
