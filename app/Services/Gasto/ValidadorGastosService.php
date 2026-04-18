<?php

namespace App\Services\Gasto;

use App\Models\Concepto;
use App\Models\Empleado;
use App\Models\Gasto;
use App\Models\GastoExcepcion;
use App\Models\PoliticaGasto;
use App\Models\Solicitud;
use App\Services\Auditoria\AuditoriaService;
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

    public function validarSolicitud(Solicitud $solicitud)
    {
        foreach ($solicitud->gastos as $gasto) {
            $this->validarGasto($gasto);
        }
    }

    public function validarGasto(Gasto $gasto): void
    {
        $empleado = $gasto->solicitud->empleado;
        $concepto = $gasto->concepto;
        $monto = $gasto->monto;
        $fecha = Carbon::parse($gasto->fecha_gasto);

        $resultado = $this->validar($empleado, $concepto, $monto, $fecha);

        if ($resultado['estatus'] === 'aprobado') {

            $gasto->update([
                'estatus' => 'aprobado'
            ]);
        } elseif ($resultado['estatus'] === 'rechazado') {

            $gasto->update([
                'estatus' => 'rechazado'
            ]);
        } elseif ($resultado['estatus'] === 'excepcion') {

            $gasto->update([
                'estatus' => 'excepcion'
            ]);

            GastoExcepcion::create([
                'gasto_id' => $gasto->id,
                'nivel' => 1,
                'estatus' => 'pendiente'
            ]);
        }

        app(AuditoriaService::class)->registrar([
            'gasto_id' => $gasto->id,
            'evento' => 'validado',
            'datos_despues' => $gasto->fresh()->toArray()
        ]);
    }
}
