<?php

namespace App\Services\Gasto;

use App\Models\GastoComprobante;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ComprobanteValidacionService
{
    public function resolver(
        GastoComprobante $comprobante,
        $user,
        string $accion,
        ?string $comentario = null
    ): GastoComprobante {
        if (!$user->can('gastos.validar')) {
            throw new AuthorizationException('No autorizado para validar comprobantes');
        }

        if (!in_array($accion, ['aprobado', 'rechazado'], true)) {
            throw new \InvalidArgumentException("Acción inválida: {$accion}");
        }

        if ($comprobante->tipo === 'factura') {
            throw new \Exception('Las facturas se validan automáticamente con el SAT');
        }

        if ($comprobante->validacion_manual !== 'pendiente') {
            throw new \Exception('Este comprobante ya fue validado');
        }

        return DB::transaction(function () use ($comprobante, $user, $accion, $comentario) {
            $comprobante->update([
                'validacion_manual'     => $accion,
                'motivo_rechazo'        => $accion === 'rechazado' ? $comentario : null,
                'validado_por'          => $user->id,
                'validado_en'           => now(),
            ]);

            app(AuditoriaService::class)->registrar([
                'gasto_id' => $comprobante->gasto_id,
                'evento'   => "comprobante_{$accion}",
                'despues'  => [
                    'comprobante_id' => $comprobante->id,
                    'motivo'         => $comentario,
                ],
            ]);

            // ✅ CRÍTICO: Re-evaluar el gasto completo
            $gasto = $comprobante->gasto->fresh(['comprobantes']);
            app(GastoService::class)->evaluarComprobacion($gasto);

            return $comprobante;
        });
    }
}
