<?php

namespace App\Services\Gasto;

use App\Models\Empleado;
use App\Models\Concepto;
use App\Models\ConfiguracionEmpresa;
use App\Models\Gasto;
use App\Models\GastoAuditoria;
use App\Models\GastoExcepcion;
use App\Models\Solicitud;
use App\Services\Auditoria\AuditoriaService;
use Carbon\Carbon;

class ValidadorGastosService
{
    public function validar(Empleado $empleado, Gasto $gasto, Concepto $concepto, float $monto, Carbon $fecha): array
    {
        $roleId = $empleado->user->roles->first()?->id;

        if (!$roleId) {
            return $this->rechazado('Empleado sin rol asignado');
        }

        $politica = app(PoliticaGastoService::class)
            ->getPoliticaAplicable($roleId, $concepto->id, $fecha);

        return $this->evaluarConPolitica($politica, $monto, $gasto);
    }

    public function validarSolicitud(Solicitud $solicitud): void
    {
        $gastos   = $solicitud->gastos;
        $empleado = $solicitud->empleado;
        $roleId   = $empleado->user->roles->first()?->id;
        $fecha    = now();

        if ($gastos->isEmpty()) {
            return;
        }

        $conceptoIds = $gastos->pluck('concepto_id')->unique()->values()->all();

        $politicas = $roleId
            ? app(PoliticaGastoService::class)->getPoliticasBulk($roleId, $conceptoIds, $fecha)
            : collect();

        $updatesPorEstatus = [];
        $excepciones       = [];
        $auditorias        = [];
        $now               = now();

        foreach ($gastos as $gasto) {
            $politica  = $politicas->get($gasto->concepto_id);
            $resultado = $this->evaluarConPolitica($politica, $gasto->monto, $gasto);
            $estatus   = $resultado['status'];

            $updatesPorEstatus[$estatus][] = $gasto->id;

            if ($estatus === 'excepcion') {
                $excepciones[] = [
                    'gasto_id'   => $gasto->id,
                    'nivel'      => 1,
                    'estatus'    => 'pendiente',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $auditorias[] = [
                'gasto_id'      => $gasto->id,
                'excepcion_id'  => null,
                'evento'        => 'validado',
                'actor_id'      => auth()->id(),
                'origen'        => 'sistema',
                'datos_antes'   => null,
                'datos_despues' => json_encode(['estatus' => $estatus]),
                'created_at'    => $now,
            ];
        }

        foreach ($updatesPorEstatus as $estatus => $ids) {
            Gasto::whereIn('id', $ids)->update(['estatus' => $estatus]);
        }

        if (!empty($excepciones)) {
            GastoExcepcion::insert($excepciones);
        }

        if (!empty($auditorias)) {
            GastoAuditoria::insert($auditorias);
        }
    }

    public function validarGasto(Gasto $gasto, float $montoCompartidoDescontar = 0): void
    {
        if (!$gasto->relationLoaded('solicitud')) {
            $gasto->load(['solicitud.empleado.user.roles', 'concepto']);
        }

        $config        = ConfiguracionEmpresa::actual();
        $empleado      = $gasto->empleado;
        $fecha_inicio  = $gasto->solicitud?->fecha_inicio ?? $gasto->comprobacionTarjeta?->fecha_inicio;
        $fecha_fin     = $gasto->solicitud?->fecha_fin ?? $gasto->comprobacionTarjeta?->fecha_fin;
        $fecha         = Carbon::parse($gasto->fecha_gasto);
        $montoEfectivo = max(0, (float) $gasto->monto - $montoCompartidoDescontar);

        $roleId   = $empleado->user->roles->first()?->id;
        $politica = $roleId
            ? app(PoliticaGastoService::class)->getPoliticaAplicable($roleId, $gasto->concepto_id, $fecha)
            : null;
        $monto_max = $politica->monto_max;

        if($politica->tipo_limite === 'Diario' && $fecha_inicio && $fecha_fin) {
            $duracion = $fecha_inicio->diffInDays($fecha_fin) + 1;
            $monto_max *= $duracion;
        }

        if (
            $politica
            && $politica->permite_propina
            && $politica->propina_max_porcentaje > 0
            && $montoEfectivo > (float) $monto_max
        ) {
            $propinaMaxima = (float) $monto_max
                * ((float) $politica->propina_max_porcentaje / 100);

            $excedente = $montoEfectivo - (float) $monto_max;

            if ($excedente <= $propinaMaxima) {
                if ($config->propina_auto_aprueba) {
                    $gasto->update(['estatus' => 'aprobado']);

                    app(AuditoriaService::class)->registrar([
                        'gasto_id' => $gasto->id,
                        'evento'   => 'auto_aprobado_propina',
                        'despues'  => [
                            'monto_efectivo'  => $montoEfectivo,
                            'monto_max'       => $monto_max,
                            'excedente'       => $excedente,
                            'propina_max'     => $propinaMaxima,
                            'porcentaje_max'  => $politica->propina_max_porcentaje,
                            'monto_compartido_descontado' => $montoCompartidoDescontar,
                        ],
                    ]);
                    return;
                }
            }
        }

        $resultado = $this->validar($empleado, $gasto, $gasto->concepto, $montoEfectivo, $fecha);
        $estatus   = $resultado['status'];

        $gasto->update(['estatus' => $estatus]);

        if ($estatus === 'excepcion') {
            GastoExcepcion::firstOrCreate(
                ['gasto_id' => $gasto->id, 'nivel' => 1],
                ['estatus'  => 'pendiente']
            );
        }

        app(AuditoriaService::class)->registrar([
            'gasto_id' => $gasto->id,
            'evento'   => 'validado',
            'despues'  => [
                'estatus'                    => $estatus,
                'monto_real'                 => $gasto->monto,
                'monto_efectivo'             => $montoEfectivo,
                'monto_compartido_descontado'=> $montoCompartidoDescontar,
            ],
        ]);
    }

    private function evaluarConPolitica(?object $politica, float $monto, ?object $gasto): array
    {
        if (!$politica) {
            return $this->rechazado('No existe política configurada');
        }

        $fecha_inicio  = $gasto->solicitud?->fecha_inicio ?? $gasto->comprobacionTarjeta?->fecha_inicio;
        $fecha_fin     = $gasto->solicitud?->fecha_fin ?? $gasto->comprobacionTarjeta?->fecha_fin;
        $monto_max     = $politica->monto_max;

        if ($politica->tipo_limite === 'Diario' && $fecha_inicio && $fecha_fin) {
            $duracion = $fecha_inicio
                ->diffInDays($fecha_fin) + 1;

            $monto_max *= $duracion;
        }

        if ($monto <= $monto_max) {
            return $this->aprobado($politica);
        }

        if ($politica->permite_excepcion) {
            return $this->excepcion($politica);
        }

        return $this->rechazado('Monto excede el límite permitido');
    }

    private function aprobado(object $politica): array
    {
        return [
            'status'      => 'aprobado',
            'mensaje'     => 'Gasto dentro de política',
            'politica_id' => $politica->id,
        ];
    }

    private function excepcion(object $politica): array
    {
        return [
            'status'      => 'excepcion',
            'mensaje'     => 'Excede límite pero permite excepción',
            'politica_id' => $politica->id,
        ];
    }

    private function rechazado(string $mensaje): array
    {
        return [
            'status'      => 'rechazado',
            'mensaje'     => $mensaje,
            'politica_id' => null,
        ];
    }
}
