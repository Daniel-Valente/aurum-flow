<?php

namespace App\Services\Gasto;

use App\Jobs\ValidarCFDIJob;
use App\Models\Gasto;
use App\Models\GastoExcepcion;
use App\Models\Solicitud;
use App\Services\Auditoria\AuditoriaService;
use App\Services\CFDI\CFDIService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class GastoService
{
    public function crear(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Carga relaciones necesarias para el validador en una sola query
            $empleado = \App\Models\Empleado::with('user.roles')
                ->findOrFail($data['empleado_id']);
            $concepto = \App\Models\Concepto::findOrFail($data['concepto_id']);

            $resultado = app(ValidadorGastosService::class)->validar(
                $empleado,
                $concepto,
                (float) $data['monto'],
                \Carbon\Carbon::parse($data['fecha_gasto'])
            );

            if ($resultado['status'] === 'rechazado') {
                return [
                    'error'   => true,
                    'mensaje' => $resultado['mensaje'],
                ];
            }

            $gasto = Gasto::create([
                'solicitud_id' => $data['solicitud_id'],
                'concepto_id'  => $data['concepto_id'],
                'fecha_gasto'  => $data['fecha_gasto'],
                'monto'        => $data['monto'],
                'uuid_factura' => $data['uuid_factura'] ?? null,
                'estatus'      => $resultado['status'],
            ]);

            app(AuditoriaService::class)->registrar([
                'gasto_id' => $gasto->id,
                'evento'   => 'creado',
                'despues'  => $gasto->toArray(),
            ]);

            if ($resultado['status'] === 'excepcion') {
                GastoExcepcion::create([
                    'gasto_id' => $gasto->id,
                    'nivel'    => 1,
                    'estatus'  => 'pendiente',
                ]);

                app(AuditoriaService::class)->registrar([
                    'gasto_id' => $gasto->id,
                    'evento'   => 'excepcion_creada',
                ]);
            }

            return [
                'error'   => false,
                'gasto'   => $gasto,
                'mensaje' => $resultado['mensaje'],
            ];
        });
    }

    public function subirComprobante(Gasto $gasto, $user, $file, array $data): \App\Models\GastoComprobante
    {
        if (!$user->can('gastos.subir.comprobante')) {
            throw new AuthorizationException('No autorizado');
        }

        $tipo = $data['tipo'];

        // Valida tipo antes de procesar
        if (!in_array($tipo, ['factura', 'pdf', 'recibo'], true)) {
            throw new \InvalidArgumentException('Tipo de comprobante no válido');
        }

        $path    = $file->store('comprobantes', 'private');
        $cfdiData = null;

        if ($tipo === 'factura') {
            $cfdiData = app(CFDIService::class)->procesar($file, $gasto);

            if (\App\Models\GastoComprobante::where('uuid', $cfdiData['uuid'])->exists()) {
                throw new \Exception('CFDI ya registrado');
            }
        }

        $comprobante = $gasto->comprobantes()->create([
            'archivo'            => $path,
            'tipo'               => $tipo,
            'uuid'               => $cfdiData['uuid']  ?? null,
            'monto'              => $cfdiData['total'] ?? $data['monto'],
            'subido_por'         => $user->id,
            'sat_status'         => $tipo === 'factura' ? 'pendiente' : null,
            'validacion_manual'  => $tipo === 'pdf'     ? 'pendiente' : null,
            'meta_cfdi'          => $cfdiData,
        ]);

        if ($tipo === 'factura') {
            dispatch(new ValidarCFDIJob($comprobante->id, $cfdiData));
        }

        $this->evaluarComprobacion($gasto);

        return $comprobante;
    }

    protected function evaluarComprobacion(Gasto $gasto): void
    {
        // Recarga el monto comprobado desde DB para evitar usar colección cacheada
        $totalComprobado = $gasto->comprobantes()->sum('monto');

        if ($totalComprobado >= $gasto->monto) {
            $gasto->update(['estatus' => 'comprobado']);
        }
    }
}
