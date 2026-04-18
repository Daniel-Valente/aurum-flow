<?php

namespace App\Services\Gasto;

use App\Models\Concepto;
use App\Models\Empleado;
use App\Models\Gasto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\GastoExcepcion;
use App\Services\Auditoria\AuditoriaService;
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

        if (!in_array($gasto->estatus, ['aprobado', 'excepcion'])) {
            throw new \Exception('El gasto no puede ser comprobado');
        }

        // 🔥 VALIDACIÓN CENTRALIZADA
        app(ComprobanteValidator::class)->validar($gasto, $data);

        // 🔒 STORAGE PRIVADO
        $path = $file->store('comprobantes', 'private');

        $comprobante = $gasto->comprobantes()->create([
            'archivo' => $path,
            'tipo' => $data['tipo'] ?? null,
            'uuid' => $data['uuid'] ?? null,
            'monto' => $data['monto'] ?? null,
            'subido_por' => $user->id
        ]);

        // 🔥 Evaluar si ya está comprobado
        $this->evaluarComprobacion($gasto);

        app(SolicitudService::class)
            ->evaluarCierre($gasto->solicitud);

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
