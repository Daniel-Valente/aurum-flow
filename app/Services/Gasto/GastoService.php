<?php

namespace App\Services\Gasto;

use App\Jobs\ValidarCFDIJob;
use App\Models\Concepto;
use App\Models\Empleado;
use App\Models\Gasto;
use App\Models\GastoComprobante;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\GastoExcepcion;
use App\Services\Auditoria\AuditoriaService;
use App\Services\CFDI\CFDIService;
use App\Services\Gasto\Validadores\ComprobanteValidator;
use App\Services\Solicitudes\SolicitudService;
use Illuminate\Auth\Access\AuthorizationException;

class GastoService
{
    public function crear(array $data)
    {
        return DB::transaction(function () use ($data) {

            $empleado = Empleado::with('user.roles')->findOrFail($data['empleado_id']);
            $concepto = Concepto::findOrFail($data['concepto_id']);

            $validador = app(ValidadorGastosService::class);

            $resultado = $validador->validar(
                $empleado,
                $concepto,
                $data['monto'],
                Carbon::parse($data['fecha_gasto'])
            );

            if ($resultado['status'] === 'rechazado') {
                return [
                    'error' => true,
                    'mensaje' => $resultado['mensaje']
                ];
            }

            $gasto = Gasto::create([
                'solicitud_id' => $data['solicitud_id'],
                'concepto_id' => $data['concepto_id'],
                'fecha_gasto' => $data['fecha_gasto'],
                'monto' => $data['monto'],
                'uuid_factura' => $data['uuid_factura'] ?? null,
                'estatus' => $resultado['status'],
            ]);

            app(AuditoriaService::class)->registrar([
                'gasto_id' => $gasto->id,
                'evento' => 'creado',
                'despues' => $gasto->toArray()
            ]);

            if ($resultado['status'] === 'excepcion') {
                GastoExcepcion::create([
                    'gasto_id' => $gasto->id,
                    'nivel' => 1,
                    'estatus' => 'pendiente'
                ]);

                app(AuditoriaService::class)->registrar([
                    'gasto_id' => $gasto->id,
                    'evento' => 'excepcion_creada'
                ]);
            }

            return [
                'error' => false,
                'gasto' => $gasto,
                'mensaje' => $resultado['mensaje']
            ];
        });
    }

    public function subirComprobante(Gasto $gasto, $user, $file, array $data)
    {
        if (!$user->can('gastos.subir.comprobante')) {
            throw new AuthorizationException('No autorizado');
        }

        $path = $file->store('comprobantes', 'private');

        $tipo = $data['tipo'];

        $cfdiData = null;

        if ($tipo === 'factura') {

            $cfdiData = app(CFDIService::class)->procesar($file, $gasto);

            if (GastoComprobante::where('uuid', $cfdiData['uuid'])->exists()) {
                throw new \Exception('CFDI ya registrado');
            }
        }

        $comprobante = $gasto->comprobantes()->create([
            'archivo' => $path,
            'tipo' => $tipo,
            'uuid' => $cfdiData['uuid'] ?? null,
            'monto' => $cfdiData['total'] ?? $data['monto'],
            'subido_por' => $user->id,
            'sat_status' => $tipo === 'factura' ? 'pendiente' : null,
            'validacion_manual' => $tipo === 'pdf' ? 'pendiente' : null,
            'meta_cfdi' => $cfdiData,
        ]);

        if ($tipo === 'factura') {
            dispatch(new ValidarCFDIJob(
                $comprobante->id,
                $cfdiData
            ));
        }

        $this->evaluarComprobacion($gasto);

        return $comprobante;
    }

    protected function evaluarComprobacion(Gasto $gasto)
    {
        $totalComprobado = $gasto->comprobantes()->sum('monto');

        if ($totalComprobado >= $gasto->monto) {
            $gasto->update([
                'estatus' => 'comprobado'
            ]);
        }
    }
}
