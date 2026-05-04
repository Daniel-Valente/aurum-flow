<?php

namespace App\Services\Gasto;

use App\Models\Empleado;
use App\Models\Concepto;
use App\Models\Gasto;
use App\Models\GastoAuditoria;
use App\Models\GastoExcepcion;
use App\Models\Solicitud;
use App\Services\Auditoria\AuditoriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ValidadorGastosService
{
    /**
     * Valida un gasto individual contra la política aplicable.
     * Retorna siempre la clave 'status' (no 'estatus') para consistencia interna.
     *
     * @param Empleado $empleado  Debe venir con user.roles cargado
     * @param Concepto $concepto
     * @param float    $monto
     * @param Carbon   $fecha
     */
    public function validar(Empleado $empleado, Concepto $concepto, float $monto, Carbon $fecha): array
    {
        $roleId = $empleado->user->roles->first()?->id;

        if (!$roleId) {
            return $this->rechazado('Empleado sin rol asignado');
        }

        $politica = app(PoliticaGastoService::class)
            ->getPoliticaAplicable($roleId, $concepto->id, $fecha);

        return $this->evaluarConPolitica($politica, $monto);
    }

    /**
     * Valida todos los gastos de una solicitud en batch:
     * - 1 query para todas las políticas (sin N+1)
     * - 1 insert masivo de auditorías
     * - 1 insert masivo de excepciones
     *
     * $solicitud debe llegar con relaciones cargadas:
     *   gastos.concepto, empleado.user.roles
     */
    public function validarSolicitud(Solicitud $solicitud): void
    {
        $gastos   = $solicitud->gastos;
        $empleado = $solicitud->empleado;
        $roleId   = $empleado->user->roles->first()?->id;
        $fecha    = now();

        if ($gastos->isEmpty()) {
            return;
        }

        // Una sola query para TODAS las políticas necesarias
        $conceptoIds = $gastos->pluck('concepto_id')->unique()->values()->all();

        $politicas = $roleId
            ? app(PoliticaGastoService::class)->getPoliticasBulk($roleId, $conceptoIds, $fecha)
            : collect();

        $updatesPorEstatus = [];   // agrupa ids por estatus para updates masivos
        $excepciones       = [];   // batch insert de GastoExcepcion
        $auditorias        = [];   // batch insert de GastoAuditoria
        $now               = now();

        foreach ($gastos as $gasto) {
            $politica  = $politicas->get($gasto->concepto_id);
            $resultado = $this->evaluarConPolitica($politica, $gasto->monto);
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

        // Updates masivos agrupados por estatus — mínimas queries posibles
        foreach ($updatesPorEstatus as $estatus => $ids) {
            Gasto::whereIn('id', $ids)->update(['estatus' => $estatus]);
        }

        // Insert masivo de excepciones (si las hay)
        if (!empty($excepciones)) {
            GastoExcepcion::insert($excepciones);
        }

        // Insert masivo de auditorías
        if (!empty($auditorias)) {
            GastoAuditoria::insert($auditorias);
        }
    }

    /**
     * Valida y persiste un gasto individual (ej: al subir comprobante).
     * Para validaciones en bulk usar validarSolicitud().
     */
    public function validarGasto(Gasto $gasto): void
    {
        // Carga relaciones solo si no están en memoria
        if (!$gasto->relationLoaded('solicitud')) {
            $gasto->load(['solicitud.empleado.user.roles', 'concepto']);
        }

        $empleado  = $gasto->solicitud->empleado;
        $fecha     = Carbon::parse($gasto->fecha_gasto);
        // ✅ 'status' — clave consistente con evaluarConPolitica()
        $resultado = $this->validar($empleado, $gasto->concepto, $gasto->monto, $fecha);
        $estatus   = $resultado['status'];

        $gasto->update(['estatus' => $estatus]);

        if ($estatus === 'excepcion') {
            // firstOrCreate evita duplicados si se llama más de una vez
            GastoExcepcion::firstOrCreate(
                ['gasto_id' => $gasto->id, 'nivel' => 1],
                ['estatus'  => 'pendiente']
            );
        }

        app(AuditoriaService::class)->registrar([
            'gasto_id' => $gasto->id,
            'evento'   => 'validado',
            'despues'  => ['estatus' => $estatus],
        ]);
    }

    // -------------------------------------------------------------------------
    // Lógica de evaluación — sin tocar la base de datos
    // -------------------------------------------------------------------------

    private function evaluarConPolitica(?object $politica, float $monto): array
    {
        if (!$politica) {
            return $this->rechazado('No existe política configurada');
        }

        // ✅ <= incluye el monto exacto como aprobado (< lo rechazaba antes)
        if ($monto <= $politica->monto_max) {
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
