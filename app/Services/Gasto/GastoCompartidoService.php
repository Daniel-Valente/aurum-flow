<?php

namespace App\Services\Gasto;

use App\Models\Gasto;
use App\Models\GastoCompartido;
use App\Services\Auditoria\AuditoriaService;
use App\Services\Solicitudes\SolicitudService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GastoCompartidoService
{
    public function marcarCompartido(
        Gasto $gasto,
        string $tipo,
        float $montoCompartido,
        ?int $empleadoReceptorId = null,
        ?string $clienteDescripcion = null,
    ): GastoCompartido
    {
        if (!in_array($tipo, ['empleado', 'cliente'], true)) {
            throw new \InvalidArgumentException("Tipo inválido: {$tipo}");
        }

        if ($tipo === 'empleado' && !$empleadoReceptorId) {
            throw new \Exception('Debes indicar el empleado con quien compartes.');
        }

        if ($montoCompartido >= $gasto->monto) {
            throw new \Exception('El monto compartido no puede ser mayor o igual al gasto total.');
        }

        if ($gasto->compartidoComo()->exists()) {
            throw new \Exception('Este gasto ya tiene un registro compartido.');
        }

        return DB::transaction(function () use ($gasto, $tipo, $montoCompartido, $empleadoReceptorId, $clienteDescripcion) {
            $compartido = GastoCompartido::create([
                'gasto_pagador_id'     => $gasto->id,
                'tipo'                 => $tipo,
                'empleado_receptor_id' => $empleadoReceptorId,
                'cliente_descripcion'  => $clienteDescripcion,
                'monto_compartido'     => $montoCompartido,
                'estatus'              => $tipo === 'cliente' ? 'aprobado_cliente' : 'pendiente',
            ]);

            app(ValidadorGastosService::class)->validarGasto(
                $gasto->fresh()->load(['solicitud.empleado.user.roles', 'concepto']),
                $montoCompartido,
            );

            app(AuditoriaService::class)->registrar([
                'gasto_id' => $gasto->id,
                'evento'   => 'gasto_carcado_compartido',
                'despues'  => [
                    'tipo'             => $tipo,
                    'monto_compartido' => $montoCompartido,
                    'receptor_id'      => $empleadoReceptorId,
                ],
            ]);

            return $compartido;
        });
    }

    public function vincularReceptor(GastoCompartido $compartido, Gasto $gastoReceptor, $user): void
    {
        if ($compartido->estatus !== 'pendiente') {
            throw new \Exception('Este gasto compartido ya fue procesado.');
        }

        if ($compartido->tipo !== 'empleado') {
            throw new \Exception('Solo gasto con empleados pueden vincularse');
        }

        if ($compartido->empleado_receptor_id !== $user->empleado->id) {
            throw new \Exception('No eres el receptor de este gasto compartido.');
        }

        $gastoOrigen = $compartido->gastoPagador;
        $mismoConcepto = $gastoReceptor->concepto_id === $gastoOrigen->concepto_id;
        $rangoFecha = $gastoReceptor->fecha_gasto?->diffInDays($gastoOrigen->fecha_gasto) <= 1;

        if (!$mismoConcepto) {
            throw new \Exception('El concepto del gasto receptor no coincide.');
        }

        DB::transaction(function () use ($compartido, $gastoReceptor) {
            $compartido->update([
                'gasto_receptor_id' => $gastoReceptor->id,
                'estatus'           => 'vinculado',
            ]);

            if ($compartido->monto_compartido >= $gastoReceptor->monto) {
                $gastoReceptor->update(['estatus' => 'comprobado']);

                App(SolicitudService::class)->evaluarCierre(
                    $gastoReceptor->solicitud
                );
            }

            app(AuditoriaService::class)->registrar([
                'gasto_id' => $gastoReceptor->id,
                'evento'   => 'gasto_vinculado_compartido',
                'despues'  => ['gasto_origen_id' => $compartido->gasto_pagador_id],
            ]);
        });
    }

    public function pendienteParaReceptor(int $empleadoId, int $conceptoId, ?string $fecha = null): Collection
    {
        return GastoCompartido::where('empleado_receptor_id', $empleadoId)
            ->where('estatus', 'pendiente')
            ->whereHas('gastoPagador', function ($q) use ($conceptoId, $fecha) {
                $q->where('concepto_id', $conceptoId);
                if ($fecha) {
                    $q->whereDate('fecha_gasto', '>=', now()->parse($fecha)->subDays(2))
                        ->whereDate('fecha_gasto', '<=', now()->parse($fecha)->addDays(2));
                }
            })
            ->with(['gastoPagador.concepto', 'gastoPagador.solicitud', 'empleadoReceptor'])
            ->get();
    }
}
