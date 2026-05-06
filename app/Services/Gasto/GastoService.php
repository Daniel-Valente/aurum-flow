<?php

namespace App\Services\Gasto;

use App\Jobs\ValidarCFDIJob;
use App\Models\Gasto;
use App\Models\GastoComprobante;
use App\Services\Auditoria\AuditoriaService;
use App\Services\CFDI\CFDIService;
use App\Services\Solicitudes\SolicitudService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GastoService
{
    public function registrarMontoReal(Gasto $gasto, float $montoReal, $user): Gasto
    {
        if (!$user->can('gastos.editar')) {
            throw new AuthorizationException('No autorizado para registrar gastos');
        }

        if (!in_array($gasto->estatus, ['pendiente', 'aprobado'])) {
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

            app(ValidadorGastosService::class)->validarGasto(
                $gasto->fresh()->load(['solicitud.empleado.user.roles', 'concepto'])
            );

            return $gasto->fresh();
        });
    }

    public function subirComprobante(Gasto $gasto, $user, $file, array $data): GastoComprobante
    {
        if (!$user->can('gastos.subir.comprobante')) {
            throw new AuthorizationException('No autorizado');
        }

        $tipo = $data['tipo'];

        if (!in_array($tipo, ['factura', 'pdf', 'recibo'], true)) {
            throw new \InvalidArgumentException('Tipo de comprobante no válido');
        }

        $empleado = $gasto->solicitud->empleado;
        $concepto = $gasto->concepto;
        $folio    = $gasto->solicitud->folio;
        $fecha    = now()->format('Ymd');
        $ts       = now()->format('His');
        $slug     = fn(string $s) => str($s)->slug()->toString();

        $nombre = implode('_', [
            $slug($empleado->nombre_completo),
            $folio,
            $slug($concepto->nombre),
            $fecha,
            $ts
        ]);

        $ext  = $file->getClientOriginalExtension();
        $path = $file->storeAs("comprobantes/{$fecha}", "{$nombre}.{$ext}", 'private');

        $cfdiData = null;

        if($tipo === 'factura') {
            try {
                $cfdiData = app(CFDIService::class)->parse($file, $gasto);
            } catch (\Exception  $e) {
                Storage::disk('private')->delete($path);
                throw $e;
            }

            if (GastoComprobante::where('uuid', $cfdiData['uuid'])->exists()) {
                Storage::disk('private')->delete($path);
                throw new \Exception('Este CFDI ya fue registrado en otra solicitud.');
            }
        }

        $montoComprobante = $tipo === 'factura'
            ? $cfdiData['total']
            : (float) $data['monto'];

        $pathPdf = null;
        if ($tipo === 'factura' && !empty($data['archivo_pdf_cfdi'])) {
            $pdfFile = $data['archivo_pdf_cfdi'];
            $pathPdf = $pdfFile->storeAs("comprobantes/{$fecha}", "{$nombre}_factura.pdf", 'private');
        }

        $comprobante = $gasto->comprobantes()->create([
            'archivo'           => $path,
            'archivo_pdf'       => $pathPdf,                          // ← nuevo
            'tipo'              => $tipo,
            'uuid'              => $cfdiData['uuid'] ?? null,
            'monto'             => $montoComprobante,
            'fecha_gasto'       => $data['fecha_gasto'],
            'subido_por'        => $user->id,
            'sat_status'        => $tipo === 'factura' ? ($cfdiData['estado_sat'] ?? 'pendiente') : null,
            'validacion_manual' => in_array($tipo, ['pdf', 'recibo']) ? 'pendiente' : 'aprobado',
            'meta_cfdi'         => $cfdiData,
        ]);

        if ($tipo === 'factura' && $comprobante->sat_status === 'pendiente') {
            dispatch(new ValidarCFDIJob($comprobante->id, $cfdiData));
        }

        $this->actualizarMontoYValidar($gasto, $user);

        app(AuditoriaService::class)->registrar([
            'gasto_id' => $gasto->id,
            'evento'   => 'comprobante_subido',
            'despues'  => ['tipo' => $tipo, 'monto' => $comprobante->monto, 'uuid' => $comprobante->uuid],
        ]);

        if($tipo === 'factura' && $cfdiData && $cfdiData['estado_sat'] === 'Vigente') {
            $gasto->update(['fecha_gasto' => $data['fecha_gasto'],'estatus' => 'Comprobado']);
        }

        return $comprobante;
    }

    public function descargarArchivo(GastoComprobante $comprobante): BinaryFileResponse
    {
        $user = auth()->user();

        if (
            $comprobante->gasto->solicitud->empleado->user_id !== $user->id &&
            !$user->can('gastos.ver.todos')
        ) {
            throw new AuthorizationException('No tienes permiso para ver este archivo.');
        }

        $path = $comprobante->archivo; // Ruta relativa guardada en BD

        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'El archivo no existe en el almacenamiento privado.');
        }

       return response()->file(Storage::disk('private')->path($path));
    }

    public function evaluarComprobacion(Gasto $gasto): void
    {
        // Solo evaluar gastos que ya pasaron validación de política
        if (!in_array($gasto->estatus, ['aprobado', 'excepcion', 'comprobado'], true)) {
            return;
        }

        // ✅ Total de comprobantes VÁLIDOS (aprobados o sin validación manual para CFDIs vigentes)
        $totalComprobado = $gasto->comprobantes()
            ->where(function ($q) {
                $q->where('validacion_manual', 'aprobado')
                ->orWhere(function ($q2) {
                    // CFDIs sin validación manual que pasaron validación SAT
                    $q2->whereNull('validacion_manual')
                        ->whereIn('sat_status', ['vigente', null]);
                });
            })
            ->sum('monto');

        // ✅ Si cubre el monto → COMPROBADO
        if ($totalComprobado >= $gasto->monto) {
            if ($gasto->estatus !== 'comprobado') {
                $gasto->update(['estatus' => 'comprobado']);

                app(AuditoriaService::class)->registrar([
                    'gasto_id' => $gasto->id,
                    'evento'   => 'comprobado',
                    'despues'  => ['total_comprobado' => $totalComprobado],
                ]);
            }

            // ✅ Evaluar cierre de la solicitud completa
            app(SolicitudService::class)->evaluarCierre($gasto->solicitud);
            return;
        }

        // ✅ Si tiene comprobantes pero NO cubre → volver a APROBADO para permitir reintento
        $tieneComprobantes = $gasto->comprobantes()->count() > 0;
        $hayRechazados = $gasto->comprobantes()->where('validacion_manual', 'rechazado')->exists();

        if ($tieneComprobantes && ($totalComprobado < $gasto->monto || $hayRechazados)) {
            if ($gasto->estatus === 'comprobado') {
                // Si estaba comprobado pero rechazaron uno → DESBLOQUEAR
                $gasto->update(['estatus' => 'aprobado']);

                app(AuditoriaService::class)->registrar([
                    'gasto_id' => $gasto->id,
                    'evento'   => 'gasto_desbloqueado_por_rechazo',
                    'despues'  => [
                        'mensaje' => 'Comprobante rechazado, gasto desbloqueado para reintento',
                        'total_valido' => $totalComprobado,
                        'requerido' => $gasto->monto,
                    ],
                ]);
            }
        }
    }

    private function actualizarMontoYValidar(Gasto $gasto, $user): Gasto
    {
        return DB::transaction(function () use ($gasto, $user) {
            $gasto->refresh()->load('comprobantes');

            $totalComprobantes = $gasto->comprobantes->sum('monto');

            if ($totalComprobantes > 0) {
                $antes = $gasto->monto;
                $gasto->update(['monto' => $totalComprobantes]);

                app(AuditoriaService::class)->registrar([
                    'gasto_id' => $gasto->id,
                    'evento'   => 'monto_acumulado',
                    'antes'    => ['monto' => $antes],
                    'despues'  => ['monto' => $totalComprobantes],
                ]);
            }

            app(ValidadorGastosService::class)->validarGasto(
                $gasto->fresh()->load(['solicitud.empleado.user.roles', 'concepto'])
            );

            return $gasto->fresh();
        });
    }
}
