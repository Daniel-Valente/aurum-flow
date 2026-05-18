<?php

namespace App\Services\Dashboard;

use App\Models\GastoComprobante;
use App\Models\GastoExcepcion;
use App\Models\Presupuesto;
use App\Models\PresupuestoAlerta;
use App\Models\Solicitud;
use Illuminate\Support\Facades\DB;

class FinanzasDashboardService extends DashboardService
{
    public function getData($user): array
    {
        return [
            'alertas_fiscales' => $this->getAlertasFiscales(),
            'panorama_financiero' => $this->getPanoramaFinancieroReal(),
            'compliance_fiscal' => $this->getComplianceFiscal(),
            'analisis_areas' => $this->getAnalisisPorAreaReal(),
            'tarjetas_corporativas' => $this->getTarjetasCorporativas(),
            'metricas_operacion' => $this->getMetricasOperacion(),
            'auditoria' => $this->getAuditoria(),
            'presupuestos_criticos' => $this->getPresupuestosCriticos(),
        ];
    }

    private function getAlertasFiscales(): array
    {
        // CFDIs pendientes validación SAT
        $cfdisPendientes = GastoComprobante::where('tipo', 'factura')
            ->whereIn('sat_status', ['pendiente', null])
            ->count();

        // CFDIs cancelados
        $cfdisCancelados = GastoComprobante::where('tipo', 'factura')
            ->where('sat_status', 'cancelado')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        // Tarjetas sin conciliar
        // TODO: Implementar cuando exista módulo de tarjetas
        $tarjetasSinConciliar = 0;

        // Validación manual pendiente
        $validacionManualPendiente = GastoComprobante::where('tipo', 'pdf')
            ->where('validacion_manual', 'pendiente')
            ->count();

        // Excepciones N2
        $excepcionesN2 = $this->getExcepcionesPendientes('2');

        // Presupuestos en alerta
        $presupuestosAlerta = PresupuestoAlerta::pendientes()
            ->whereIn('severidad', ['danger', 'critical'])
            ->count();

        return [
            'cfdis_pendientes_sat' => $cfdisPendientes,
            'cfdis_cancelados' => $cfdisCancelados,
            'tarjetas_sin_conciliar' => $tarjetasSinConciliar,
            'validacion_manual_pendiente' => $validacionManualPendiente,
            'excepciones_n2' => $excepcionesN2,
            'presupuestos_alerta' => $presupuestosAlerta,
        ];
    }

    /**
     * Panorama financiero CON PRESUPUESTOS REALES
     */
    private function getPanoramaFinancieroReal(): array
    {
        // Obtener presupuestos activos de EMPRESA del mes actual
        $presupuestos = Presupuesto::tipo('empresa')
            ->where('periodo', 'mensual')
            ->vigentes()
            ->get();

        $presupuestoTotal = $presupuestos->sum('monto_total');
        $gastado = $presupuestos->sum('monto_gastado');
        $comprometido = $presupuestos->sum('monto_comprometido');
        $disponibleReal = $presupuestos->sum('monto_disponible');

        // Por comprobar (gastos aprobados sin comprobante)
        $porComprobar = DB::table('gastos')
            ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
            ->where('gastos.estatus', 'aprobado')
            ->whereBetween('solicitudes.created_at', [$this->fechaInicio, $this->fechaFin])
            ->sum('gastos.monto');

        $proyeccion = $this->proyectarGastoMensual($gastado);

        // Variación vs mismo mes año anterior
        $mesAnterior = now()->subYear();
        $presupuestosAñoAnterior = Presupuesto::tipo('empresa')
            ->where('periodo', 'mensual')
            ->whereBetween('fecha_inicio', [
                $mesAnterior->startOfMonth(),
                $mesAnterior->endOfMonth()
            ])
            ->get();

        $gastadoAñoAnterior = $presupuestosAñoAnterior->sum('monto_gastado');

        $variacionAnual = $gastadoAñoAnterior > 0
            ? round((($gastado - $gastadoAñoAnterior) / $gastadoAñoAnterior) * 100, 1)
            : 0;

        // Alertas de presupuestos
        $alertasCriticas = PresupuestoAlerta::pendientes()
            ->whereIn('tipo', ['critico', 'agotado', 'excedido'])
            ->count();

        return [
            'presupuesto_total' => $presupuestoTotal,
            'gastado' => $gastado,
            'porcentaje_gastado' => $presupuestoTotal > 0
                ? round(($gastado / $presupuestoTotal) * 100, 1)
                : 0,
            'comprometido' => $comprometido,
            'por_comprobar' => $porComprobar,
            'disponible_real' => $disponibleReal,
            'proyeccion_cierre' => $proyeccion,
            'variacion_anual_pct' => $variacionAnual,
            'alertas_criticas' => $alertasCriticas,
            'tiene_presupuestos' => $presupuestoTotal > 0,
        ];
    }

    private function getComplianceFiscal(): array
    {
        $totalCfdis = GastoComprobante::where('tipo', 'factura')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        if ($totalCfdis === 0) {
            return [
                'total' => 0,
                'vigentes' => 0,
                'cancelados' => 0,
                'sin_validar' => 0,
                'pct_vigentes' => 0,
                'pct_cancelados' => 0,
                'pct_sin_validar' => 0,
                'top_rfcs' => [],
            ];
        }

        $vigentes = GastoComprobante::where('tipo', 'factura')
            ->where('sat_status', 'vigente')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $cancelados = GastoComprobante::where('tipo', 'factura')
            ->where('sat_status', 'cancelado')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $sinValidar = GastoComprobante::where('tipo', 'factura')
            ->whereIn('sat_status', ['pendiente', null])
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        // Top RFCs con más operaciones
        $topRfcs = GastoComprobante::where('tipo', 'factura')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->whereNotNull('emisor_rfc')
            ->select(
                'emisor_rfc',
                'emisor_nombre',
                DB::raw('COUNT(*) as total_cfdis'),
                DB::raw('SUM(monto) as monto_total')
            )
            ->groupBy('emisor_rfc', 'emisor_nombre')
            ->orderByDesc('total_cfdis')
            ->limit(3)
            ->get()
            ->map(fn($r) => [
                'rfc' => $r->emisor_rfc,
                'nombre' => $r->emisor_nombre ?? $r->emisor_rfc,
                'cfdis' => $r->total_cfdis,
                'monto' => $r->monto_total,
            ]);

        return [
            'total' => $totalCfdis,
            'vigentes' => $vigentes,
            'cancelados' => $cancelados,
            'sin_validar' => $sinValidar,
            'pct_vigentes' => round(($vigentes / $totalCfdis) * 100, 1),
            'pct_cancelados' => round(($cancelados / $totalCfdis) * 100, 1),
            'pct_sin_validar' => round(($sinValidar / $totalCfdis) * 100, 1),
            'top_rfcs' => $topRfcs,
        ];
    }

    /**
     * Análisis por área CON PRESUPUESTOS REALES
     */
    private function getAnalisisPorAreaReal(): array
    {
        $areas = DB::table('areas')
            ->leftJoin('presupuestos', function ($join) {
                $join->on('presupuestos.area_id', '=', 'areas.id')
                    ->where('presupuestos.tipo', 'area')
                    ->where('presupuestos.periodo', 'mensual')
                    ->where('presupuestos.estatus', 'activo')
                    ->whereBetween('presupuestos.fecha_inicio', [
                        now()->startOfMonth(),
                        now()->endOfMonth()
                    ]);
            })
            ->select(
                'areas.id',
                'areas.nombre',
                'presupuestos.id as presupuesto_id',
                'presupuestos.monto_total',
                'presupuestos.monto_gastado',
                'presupuestos.monto_comprometido',
                'presupuestos.monto_disponible'
            )
            ->get();

        return $areas->map(function ($area) {
            $presupuesto = $area->monto_total ?? 0; // Fallback si no hay presupuesto
            $gastado = $area->monto_gastado ?? 0;
            $comprometido = $area->monto_comprometido ?? 0;
            $porcentaje = $presupuesto > 0 ? round((($gastado + $comprometido) / $presupuesto) * 100, 1) : 0;

            // Proyección
            $diasTranscurridos = now()->day;
            $diasMes = now()->daysInMonth;
            $proyeccion = $diasTranscurridos > 0
                ? round(($gastado / $diasTranscurridos) * $diasMes, 2)
                : $gastado;

            // Riesgo
            $porcentajeProyectado = $presupuesto > 0 ? ($proyeccion / $presupuesto) * 100 : 0;
            $riesgo = match (true) {
                $porcentajeProyectado > 100 => 'critico',
                $porcentajeProyectado > 90 => 'alerta',
                default => 'normal',
            };

            return [
                'area' => $area->nombre,
                'presupuesto' => $presupuesto,
                'gastado' => $gastado,
                'comprometido' => $comprometido,
                'porcentaje' => $porcentaje,
                'proyeccion' => $proyeccion,
                'riesgo' => $riesgo,
                'tiene_presupuesto' => $area->presupuesto_id !== null,
            ];
        })->toArray();
    }

    private function getTarjetasCorporativas(): array
    {
        // TODO: Implementar cuando exista módulo de tarjetas corporativas
        return [
            'total_gastado' => 0,
            'conciliado' => 0,
            'pendiente_conciliar' => 0,
            'pct_conciliado' => 0,
            'movimientos_sin_conciliar' => 0,
        ];
    }

    private function getMetricasOperacion(): array
    {
        $totalSolicitudes = Solicitud::whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        // Tiempo promedio de proceso (desde creación hasta comprobación)
        $tiempoPromedio = Solicitud::whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->where('estatus', 'Comprobado')
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - created_at)) / 3600) as avg_hours')
            ->value('avg_hours');

        $diasPromedio = $tiempoPromedio ? round($tiempoPromedio / 24, 1) : 0;

        // Tasa de aprobación global
        $aprobadas = Solicitud::whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->whereIn('estatus', ['Autorizado', 'Comprobado'])
            ->count();

        $tasaAprobacion = $totalSolicitudes > 0
            ? round(($aprobadas / $totalSolicitudes) * 100, 1)
            : 0;

        // Excepciones N2 rechazadas
        $totalExcepcionesN2 = GastoExcepcion::where('nivel', '2')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $rechazadasN2 = GastoExcepcion::where('nivel', '2')
            ->where('estatus', 'rechazado')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $pctRechazadasN2 = $totalExcepcionesN2 > 0
            ? round(($rechazadasN2 / $totalExcepcionesN2) * 100, 1)
            : 0;

        // Gasto promedio por solicitud
        $gastoPromedio = $totalSolicitudes > 0
            ? Solicitud::whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->avg('monto_total')
            : 0;

        // Solicitudes con uso de presupuesto
        $conPresupuesto = Solicitud::whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->whereNotNull('presupuesto_id')
            ->count();

        $pctConPresupuesto = $totalSolicitudes > 0
            ? round(($conPresupuesto / $totalSolicitudes) * 100, 1)
            : 0;

        return [
            'solicitudes_procesadas' => $totalSolicitudes,
            'tiempo_promedio_dias' => $diasPromedio,
            'tasa_aprobacion_pct' => $tasaAprobacion,
            'excepciones_n2_rechazadas_pct' => $pctRechazadasN2,
            'gasto_promedio' => round($gastoPromedio, 2),
            'pct_con_presupuesto' => $pctConPresupuesto,
        ];
    }

    private function getAuditoria(): array
    {
        // Solicitudes con posibles gastos duplicados
        $duplicadosSospechosos = DB::table('gastos as g1')
            ->join('gastos as g2', function ($join) {
                $join->on('g1.concepto_id', '=', 'g2.concepto_id')
                    ->on('g1.monto', '=', 'g2.monto')
                    ->on('g1.fecha_gasto', '=', 'g2.fecha_gasto')
                    ->whereColumn('g1.id', '<', 'g2.id');
            })
            ->join('solicitudes as s1', 's1.id', '=', 'g1.solicitud_id')
            ->join('solicitudes as s2', 's2.id', '=', 'g2.solicitud_id')
            ->whereColumn('s1.empleado_id', '=', 's2.empleado_id')
            ->whereBetween('g1.created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        // Empleados con 3+ excepciones consecutivas
        $empleadosConExcepcionesRecurrentes = DB::table('gastos_excepciones')
            ->join('gastos', 'gastos.id', '=', 'gastos_excepciones.gasto_id')
            ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->whereBetween('gastos_excepciones.created_at', [$this->fechaInicio, $this->fechaFin])
            ->select('empleados.id')
            ->groupBy('empleados.id')
            ->havingRaw('COUNT(gastos_excepciones.id) >= 3')
            ->count();

        // Proveedores nuevos esta semana
        $proveedoresNuevos = GastoComprobante::query()
            ->join('gastos', 'gastos.id', '=', 'gasto_comprobantes.gasto_id')
            ->where('gasto_comprobantes.tipo', 'factura')
            ->whereBetween('gasto_comprobantes.created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->distinct()
            ->count('gastos.rfc_proveedor');

        // Presupuestos excedidos
        $presupuestosExcedidos = Presupuesto::activos()
            ->whereRaw('monto_gastado + monto_comprometido > monto_total')
            ->count();

        // CFDIs con monto inusual (>3 desviaciones estándar)
        $promedioMonto = GastoComprobante::where('tipo', 'factura')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->avg('monto');

        $desviacion = DB::table('gasto_comprobantes')
            ->where('tipo', 'factura')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->selectRaw('STDDEV(monto) as std')
            ->value('std');

        $umbralAlto = $promedioMonto + (3 * ($desviacion ?? 0));

        $cfdisInusuales = GastoComprobante::where('tipo', 'factura')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->where('monto', '>', $umbralAlto)
            ->count();

        return [
            'duplicados_sospechosos' => $duplicadosSospechosos,
            'empleados_excepciones_recurrentes' => $empleadosConExcepcionesRecurrentes,
            'proveedores_nuevos_semana' => $proveedoresNuevos,
            'presupuestos_excedidos' => $presupuestosExcedidos,
            'cfdis_inusuales' => $cfdisInusuales,
        ];
    }

    /**
     * Presupuestos en estado crítico
     */
    private function getPresupuestosCriticos(): array
    {
        return Presupuesto::activos()
            ->with(['area', 'empleado', 'proyecto'])
            ->whereRaw('(monto_gastado + monto_comprometido) / monto_total >= 0.90')
            ->orderByRaw('(monto_gastado + monto_comprometido) / monto_total DESC')
            ->limit(5)
            ->get()
            ->map(function ($p) {
                return [
                    'codigo' => $p->codigo,
                    'tipo' => $p->tipo,
                    'nombre' => $p->nombre,
                    'entidad' => $this->getNombreEntidad($p),
                    'porcentaje' => $p->porcentaje_consumido,
                    'disponible' => $p->monto_disponible,
                    'severidad' => $p->getSeveridad(),
                    'dias_restantes' => $p->dias_restantes,
                ];
            })
            ->toArray();
    }

    private function getNombreEntidad($presupuesto): string
    {
        return match ($presupuesto->tipo) {
            'area' => $presupuesto->area?->nombre ?? 'Área',
            'empleado' => $presupuesto->empleado?->nombre_completo ?? 'Empleado',
            'proyecto' => $presupuesto->proyecto?->nombre ?? 'Proyecto',
            'empresa' => 'Empresa',
            default => 'N/A',
        };
    }

    private function proyectarGastoMensual($gastadoActual): float
    {
        $diasTranscurridos = now()->day;
        $diasMes = now()->daysInMonth;

        if ($diasTranscurridos === 0) return $gastadoActual;

        return round(($gastadoActual / $diasTranscurridos) * $diasMes, 2);
    }
}
