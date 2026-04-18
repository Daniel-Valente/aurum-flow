<?php

namespace App\Services\Gasto;

use App\Models\Concepto;
use App\Models\Empleado;
use App\Models\PoliticaGasto;
use Carbon\Carbon;

class ValidadorGastosService
{
    public function validar(Empleado $empleado, Concepto $concepto, float $monto, Carbon $fecha): array
    {
        $politica = $this->obtenerPolitica($empleado, $concepto, $fecha);

        if (!$politica) {
            return $this->rechazado('No existe política configurada');
        }

        if ($monto < $politica->monto_max) {
            return $this->aprobado($politica);
        }

        if ($politica->permite_excepcion) {
            return $this->excepcion($politica);
        }

        return $this->rechazado('Monto excede el límite permitido');
    }

    public function obtenerPolitica($empleado, $concepto, $fecha)
    {
        return PoliticaGasto::where('role_id', $empleado->role()?->id)
            ->where('concepto_id', $concepto->id)
            ->where(function ($q) use ($fecha) {
                $q->whereNull('vigencia_desde')
                    ->orWhere('vigencia_desde', '<=', $fecha);
            })
            ->where(function ($q) use ($fecha) {
                $q->whereNull('vigencia_hasta')
                    ->orWhere('vigencia_hasta', '>=', $fecha);
            })
            ->latest()
            ->first();
    }

        private function aprobado($politica): array
    {
        return [
            'status' => 'aprobado',
            'mensaje' => 'Gasto dentro de política',
            'politica_id' => $politica->id
        ];
    }

    private function excepcion($politica): array
    {
        return [
            'status' => 'excepcion',
            'mensaje' => 'Excede límite pero permite excepción',
            'politica_id' => $politica->id
        ];
    }

    private function rechazado($mensaje): array
    {
        return [
            'status' => 'rechazado',
            'mensaje' => $mensaje,
            'politica_id' => null
        ];
    }
}
