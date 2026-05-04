<?php

namespace App\Services\Gasto;

use App\Jobs\ValidarCFDIJob;
use App\Models\Gasto;
use App\Models\GastoComprobante;
use App\Models\GastoExcepcion;
use App\Models\Solicitud;
use App\Services\Auditoria\AuditoriaService;
use App\Services\CFDI\CFDIService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class GastoService
{
    // -------------------------------------------------------------------------
    // Registrar el monto real — step 3 (comprobación)
    // El gasto ya existe en 'pendiente' desde generarGastos()
    // Aquí el empleado confirma cuánto gastó realmente
    // Después de esto → validarGasto() evalúa política y crea excepción si aplica
    // -------------------------------------------------------------------------

    public function registrarMontoReal(Gasto $gasto, float $montoReal, $user): Gasto
    {
        if (!$user->can('gastos.editar')) {
            throw new AuthorizationException('No autorizado para registrar gastos');
        }

        if ($gasto->estatus !== 'pendiente') {
            throw new \Exception('Este gasto ya fue procesado y no puede modificarse');
        }

        if ($gasto->solicitud->empleado->user_id !== $user->id) {
            throw new AuthorizationException('No es tu gasto');
        }

        return DB::transaction(function () use ($gasto, $montoReal) {
            $antes = $gasto->monto;

            $gasto->update(['monto' => $montoReal]);

            app(AuditoriaService::class)->registrar([
                'gasto_id' => $gasto->id,
                'evento'   => 'monto_real_registrado',
                'antes'    => ['monto' => $antes],
                'despues'  => ['monto' => $montoReal],
            ]);

            // Aquí nacen las excepciones si el monto real excede la política
            // validarGasto() actualiza el estatus del gasto y crea GastoExcepcion si aplica
            app(ValidadorGastosService::class)->validarGasto(
                $gasto->fresh()->load(['solicitud.empleado.user.roles', 'concepto'])
            );

            return $gasto->fresh();
        });
    }

    // -------------------------------------------------------------------------
    // Subir comprobante
    // Requiere que el monto real ya esté registrado (estatus != 'pendiente')
    // factura (XML) → CFDIParser → ValidarCFDIJob async (SAT)
    // pdf / recibo  → validacion_manual = 'pendiente' (cola manual)
    // -------------------------------------------------------------------------

    public function subirComprobante(Gasto $gasto, $user, $file, array $data): GastoComprobante
    {
        if (!$user->can('gastos.subir.comprobante')) {
            throw new AuthorizationException('No autorizado');
        }

        $tipo = $data['tipo'];

        if (!in_array($tipo, ['factura', 'pdf', 'recibo'], true)) {
            throw new \InvalidArgumentException('Tipo de comprobante no válido');
        }

        // Debe registrar monto real antes de subir comprobante
        if ($gasto->estatus === 'pendiente') {
            throw new \Exception('Registra el monto real del gasto antes de subir el comprobante');
        }

        $path     = $file->store('comprobantes', 'private');
        $cfdiData = null;

        if ($tipo === 'factura') {
            $cfdiData = app(CFDIService::class)->procesar($file, $gasto);

            // UUID único global — previene reusar la misma factura en otra solicitud
            if (GastoComprobante::where('uuid', $cfdiData['uuid'])->exists()) {
                throw new \Exception('Este CFDI ya fue registrado en otra solicitud');
            }
        }

        $comprobante = $gasto->comprobantes()->create([
            'archivo'           => $path,
            'tipo'              => $tipo,
            'uuid'              => $cfdiData['uuid']  ?? null,
            'monto'             => $cfdiData['total'] ?? $data['monto'],
            'subido_por'        => $user->id,
            'sat_status'        => $tipo === 'factura'                        ? 'pendiente' : null,
            'validacion_manual' => in_array($tipo, ['pdf', 'recibo'])         ? 'pendiente' : null,
            'meta_cfdi'         => $cfdiData,
        ]);

        if ($tipo === 'factura') {
            dispatch(new ValidarCFDIJob($comprobante->id, $cfdiData));
        }

        app(AuditoriaService::class)->registrar([
            'gasto_id' => $gasto->id,
            'evento'   => 'comprobante_subido',
            'despues'  => [
                'tipo'  => $tipo,
                'monto' => $comprobante->monto,
                'uuid'  => $comprobante->uuid,
            ],
        ]);

        $this->evaluarComprobacion($gasto);

        return $comprobante;
    }

    // -------------------------------------------------------------------------
    // Evalúa si el total comprobado cubre el monto real del gasto
    // Solo cuenta comprobantes válidos (no rechazados manualmente)
    // -------------------------------------------------------------------------

    public function evaluarComprobacion(Gasto $gasto): void
    {
        // Solo gastos que pasaron validación de política
        if (!in_array($gasto->estatus, ['aprobado', 'excepcion'], true)) {
            return;
        }

        $totalComprobado = $gasto->comprobantes()
            ->where(fn($q) =>
                $q->whereNull('validacion_manual')
                  ->orWhere('validacion_manual', 'aprobado')
            )
            ->sum('monto');

        if ($totalComprobado >= $gasto->monto) {
            $gasto->update(['estatus' => 'comprobado']);

            app(AuditoriaService::class)->registrar([
                'gasto_id' => $gasto->id,
                'evento'   => 'comprobado',
                'despues'  => ['total_comprobado' => $totalComprobado],
            ]);
        }
    }
}
