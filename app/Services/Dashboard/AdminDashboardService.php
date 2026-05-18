<?php

namespace App\Services\Dashboard;

use App\Models\ActividadLog;
use App\Models\GastoComprobante;
use App\Models\GastoExcepcion;
use App\Models\PoliticaGasto;
use App\Models\Presupuesto;
use App\Models\PresupuestoAlerta;
use App\Models\PresupuestoTransferencia;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardService extends DashboardService
{
    public function getData($user): array
    {
        return [
            'kpis_estrategicos' => $this->getKpisEstrategicosReal(),
            'excepciones_n2' => $this->getExcepcionesN2(),
            'desempeno_areas' => $this->getDesempenoAreasReal(),
            'insights' => $this->getInsights(),
            'tendencias' => $this->getTendencias(),
            'salud_sistema' => $this->getSaludSistema(),
            'governance' => $this->getGovernance(),
            'presupuestos_overview' => $this->getPresupuestosOverview(),
        ];
    }

    /**
     * KPIs estratégicos CON PRESUPUESTOS REALES
     */
    private function getKpisEstrategicosReal(): array
    {
        // Obtener todos los presupuestos activos del mes
        $presupuestos = Presupuesto::tipo('empresa')
            ->where('periodo', 'mensual')
            ->vigentes()
            ->get();

        $presupuestoTotal = $presupuestos->sum('monto_total');
        $gastoTotal = $presupuestos->sum('monto_gastado');
        $comprometido = $presupuestos->sum('monto_comprometido');

        // Variación vs plan
        $variacionPlan = $presupuestoTotal > 0
            ? round((($gastoTotal - $presupuestoTotal) / $presupuestoTotal) * 100, 1)
            : 0;

        // Variación vs año anterior
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
            ? round((($gastoTotal - $gastadoAñoAnterior) / $gastadoAñoAnterior) * 100, 1)
            : 0;

        // Proyección cierre
        $proyeccion = $this->proyectarGastoMensual($gastoTotal);

        // Tendencia 3 meses
        $tendencia3Meses = $this->calcularTendenciaTrimestral();

        // Alertas activas
        $alertasCriticas = PresupuestoAlerta::pendientes()
            ->whereIn('severidad', ['danger', 'critical'])
            ->count();

        return [
            'presupuesto_total' => $presupuestoTotal,
            'gasto_total' => $gastoTotal,
            'comprometido' => $comprometido,
            'porcentaje_gastado' => $presupuestoTotal > 0
                ? round(($gastoTotal / $presupuestoTotal) * 100, 1)
                : 0,
            'variacion_plan_pct' => $variacionPlan,
            'variacion_anual_pct' => $variacionAnual,
            'proyeccion_cierre' => $proyeccion,
            'tendencia_3meses' => $tendencia3Meses,
            'alertas_criticas' => $alertasCriticas,
        ];
    }

    private function getExcepcionesN2(): array
    {
        $pendientes = GastoExcepcion::where('nivel', '2')
            ->where('estatus', 'pendiente')
            ->count();

        $totalMes = GastoExcepcion::where('nivel', '2')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $aprobadas = GastoExcepcion::where('nivel', '2')
            ->where('estatus', 'aprobado')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $tasaAprobacion = $totalMes > 0
            ? round(($aprobadas / $totalMes) * 100, 1)
            : 0;

        $montoTotal = GastoExcepcion::where('gastos_excepciones.nivel', 2)
            ->where('gastos_excepciones.estatus', 'pendiente')
            ->join('gastos', 'gastos.id', '=', 'gastos_excepciones.gasto_id')
            ->sum('gastos.monto');

        return [
            'pendientes' => $pendientes,
            'tasa_aprobacion_pct' => $tasaAprobacion,
            'monto_total' => $montoTotal,
        ];
    }

    /**
     * Desempeño por área CON PRESUPUESTOS REALES
     */
    private function getDesempenoAreasReal(): array
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
                'presupuestos.monto_total as presupuesto',
                'presupuestos.monto_gastado as gastado',
                'presupuestos.monto_comprometido as comprometido'
            )
            ->get();

        return $areas->map(function ($area) {
            $presupuesto = $area->presupuesto ?? 0; // Fallback
            $gastado = $area->gastado ?? 0;
            $comprometido = $area->comprometido ?? 0;
            $totalConsumido = $gastado + $comprometido;
            $porcentaje = $presupuesto > 0 ? round(($totalConsumido / $presupuesto) * 100, 1) : 0;

            // Eficiencia = comprobación a tiempo + tasa aprobación
            $eficiencia = $this->calcularEficienciaArea($area->id);

            // Riesgo = proyección de sobregiro
            $proyeccion = $this->proyectarGastoMensual($gastado);
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
                'eficiencia_pct' => $eficiencia,
                'riesgo' => $riesgo,
            ];
        })->toArray();
    }

    private function getInsights(): array
    {
        $insights = [];

        // 1. Detectar presupuestos en riesgo crítico
        $presupuestosCriticos = Presupuesto::activos()
            ->whereRaw('(monto_gastado + monto_comprometido) / monto_total >= 0.90')
            ->count();

        if ($presupuestosCriticos > 0) {
            $insights[] = [
                'tipo' => 'alerta',
                'titulo' => 'Presupuestos en riesgo',
                'descripcion' => "{$presupuestosCriticos} presupuesto(s) están al 90%+ de consumo con " .
                    now()->endOfMonth()->diffInDays(now()) . " días restantes",
                'recomendacion' => 'Revisar proyección con responsables de área',
                'severidad' => 'danger',
            ];
        }

        // 2. Detectar conceptos con crecimiento anormal
        $conceptosAnormales = $this->detectarConceptosConCrecimiento();
        if (!empty($conceptosAnormales)) {
            $insights[] = [
                'tipo' => 'info',
                'titulo' => 'Concepto con crecimiento significativo',
                'descripcion' => $conceptosAnormales[0]['nombre'] . ' subió ' .
                    abs($conceptosAnormales[0]['variacion']) . '% vs mes anterior',
                'recomendacion' => 'Evaluar si es estacional o requiere ajuste de políticas',
                'severidad' => 'warning',
            ];
        }

        // 3. Empleados que excedieron presupuesto
        $empleadosExcedidos = Presupuesto::tipo('empleado')
            ->activos()
            ->whereRaw('(monto_gastado + monto_comprometido) > monto_total')
            ->count();

        if ($empleadosExcedidos > 0) {
            $insights[] = [
                'tipo' => 'critico',
                'titulo' => 'Empleados que excedieron presupuesto',
                'descripcion' => "{$empleadosExcedidos} empleado(s) excedieron su límite mensual",
                'recomendacion' => 'Revisar políticas o ajustar límites individuales',
                'severidad' => 'critical',
            ];
        }

        // 4. Alertas de presupuestos próximos a vencer
        $proximosVencer = Presupuesto::activos()
            ->whereNotNull('fecha_fin')
            ->whereBetween('fecha_fin', [now(), now()->addDays(7)])
            ->count();

        if ($proximosVencer > 0) {
            $insights[] = [
                'tipo' => 'info',
                'titulo' => 'Presupuestos próximos a vencer',
                'descripcion' => "{$proximosVencer} presupuesto(s) vencen en los próximos 7 días",
                'recomendacion' => 'Planificar renovación o ajuste de presupuestos',
                'severidad' => 'warning',
            ];
        }

        // 5. Áreas con buen desempeño
        $areasExcelentes = $this->detectarAreasConMejoras();
        if (!empty($areasExcelentes)) {
            $insights[] = [
                'tipo' => 'exito',
                'titulo' => 'Área con mejora en eficiencia',
                'descripcion' => $areasExcelentes[0]['nombre'] . ' mejoró de ' .
                    $areasExcelentes[0]['antes'] . '% a ' . $areasExcelentes[0]['ahora'] . '%',
                'recomendacion' => 'Reconocer al equipo y compartir mejores prácticas',
                'severidad' => 'info',
            ];
        }

        return $insights;
    }

    private function getTendencias(): array
    {
        // Gasto mensual últimos 6 meses CON PRESUPUESTOS REALES
        $gastoMensual = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);

            $presupuestos = Presupuesto::tipo('empresa')
                ->where('periodo', 'mensual')
                ->whereBetween('fecha_inicio', [
                    $mes->copy()->startOfMonth(),
                    $mes->copy()->endOfMonth()
                ])
                ->get();

            $gasto = $presupuestos->sum('monto_gastado');
            $presupuesto = $presupuestos->sum('monto_total');

            $gastoMensual[] = [
                'mes' => $mes->format('M Y'),
                'gasto' => $gasto,
                'presupuesto' => $presupuesto,
            ];
        }

        // Top 5 conceptos con mayor crecimiento
        $topConceptos = $this->getTopConceptosCrecimiento();

        return [
            'gasto_mensual' => $gastoMensual,
            'top_conceptos_crecimiento' => $topConceptos,
        ];
    }

    private function getSaludSistema(): array
    {
        // Usuarios activos hoy
        $usuariosActivosHoy = ActividadLog::whereDate('created_at', today())
            ->distinct('user_id')
            ->count('user_id');

        $totalUsuarios = User::count();

        // Solicitudes procesadas hoy
        $solicitudesHoy = Solicitud::whereDate('created_at', today())->count();

        // Tiempo promedio respuesta (días)
        $tiempoPromedio = Solicitud::whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->where('estatus', 'Comprobado')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - created_at)) / 3600) as avg_hours')
            ->value('avg_hours');

        $diasPromedio = $tiempoPromedio ? round($tiempoPromedio / 24, 1) : 0;

        // Tasa error validación SAT
        $totalValidaciones = \App\Models\GastoComprobante::where('tipo', 'factura')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $erroresValidacion = \App\Models\GastoComprobante::where('tipo', 'factura')
            ->where('sat_status', 'no_encontrado')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $tasaError = $totalValidaciones > 0
            ? round(($erroresValidacion / $totalValidaciones) * 100, 1)
            : 0;

        // Políticas a vencer
        $politicasVencer = PoliticaGasto::whereNotNull('vigencia_hasta')
            ->whereBetween('vigencia_hasta', [now(), now()->addDays(30)])
            ->count();

        // Presupuestos activos
        $presupuestosActivos = Presupuesto::activos()->count();
        $presupuestosConAlertas = PresupuestoAlerta::pendientes()
            ->distinct('presupuesto_id')
            ->count('presupuesto_id');

        return [
            'usuarios_activos_hoy' => $usuariosActivosHoy,
            'total_usuarios' => $totalUsuarios,
            'solicitudes_hoy' => $solicitudesHoy,
            'tiempo_promedio_dias' => $diasPromedio,
            'tasa_error_sat_pct' => $tasaError,
            'politicas_vencer_30dias' => $politicasVencer,
            'presupuestos_activos' => $presupuestosActivos,
            'presupuestos_con_alertas' => $presupuestosConAlertas,
        ];
    }

    private function getGovernance(): array
    {
        // Cambios en políticas este mes
        $cambiosPoliticas = DB::table('politicas_gastos_auditoria')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $promedioCambios = DB::table('politicas_gastos_auditoria')
            ->whereBetween('created_at', [
                now()->subMonths(3)->startOfMonth(),
                now()->subMonth()->endOfMonth()
            ])
            ->select(DB::raw('COUNT(*) / 3 as promedio'))
            ->value('promedio');

        $usuariosInactivos = User::whereDoesntHave('actividadLogs', function ($q) {
            $q->where('created_at', '>=', now()->subDays(30));
        })->count();

        // Solicitudes con montos inusuales (>3σ del promedio)
        $promedioMonto = Solicitud::whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->avg('monto_total');

        $desviacionEstandar = DB::table('solicitudes')
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->selectRaw('STDDEV(monto_total) as std')
            ->value('std');

        $umbralAlto = $promedioMonto + (3 * ($desviacionEstandar ?? 0));

        $montosInusuales = Solicitud::whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->where('monto_total', '>', $umbralAlto)
            ->count();

        $accesosFueraHorario = ActividadLog::whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->whereRaw('EXTRACT(HOUR FROM created_at) >= 22 OR EXTRACT(HOUR FROM created_at) < 6')
            ->count();

        $transferencias = PresupuestoTransferencia::whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        return [
            'cambios_politicas_mes' => $cambiosPoliticas,
            'es_mayor_promedio' => $cambiosPoliticas > ($promedioCambios ?? 0),
            'usuarios_inactivos_30dias' => $usuariosInactivos,
            'solicitudes_montos_inusuales' => $montosInusuales,
            'accesos_fuera_horario' => $accesosFueraHorario,
            'transferencias_presupuesto' => $transferencias,
        ];
    }

    /**
     * Overview de presupuestos
     */
    private function getPresupuestosOverview(): array
    {
        $totalPresupuestos = Presupuesto::activos()->count();

        $porTipo = Presupuesto::activos()
            ->select('tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        $montoTotalActivo = Presupuesto::activos()->sum('monto_total');
        $montoGastado = Presupuesto::activos()->sum('monto_gastado');
        $montoComprometido = Presupuesto::activos()->sum('monto_comprometido');

        return [
            'total_activos' => $totalPresupuestos,
            'por_tipo' => $porTipo,
            'monto_total' => $montoTotalActivo,
            'monto_gastado' => $montoGastado,
            'monto_comprometido' => $montoComprometido,
            'pct_consumo_global' => $montoTotalActivo > 0
                ? round((($montoGastado + $montoComprometido) / $montoTotalActivo) * 100, 1)
                : 0,
        ];
    }

    // --- Helpers privados ---

    private function proyectarGastoMensual($gastadoActual): float
    {
        $diasTranscurridos = now()->day;
        $diasMes = now()->daysInMonth;

        if ($diasTranscurridos === 0) return $gastadoActual;

        return round(($gastadoActual / $diasTranscurridos) * $diasMes, 2);
    }

    private function calcularTendenciaTrimestral(): string
    {
        $meses = [];
        for ($i = 2; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $presupuestos = Presupuesto::tipo('empresa')
                ->where('periodo', 'mensual')
                ->whereBetween('fecha_inicio', [
                    $mes->copy()->startOfMonth(),
                    $mes->copy()->endOfMonth()
                ])
                ->get();

            $meses[] = $presupuestos->sum('monto_gastado');
        }

        if (count($meses) < 3) return '→ Sin datos suficientes';

        $variacion1 = $meses[0] > 0 ? (($meses[1] - $meses[0]) / $meses[0]) * 100 : 0;
        $variacion2 = $meses[1] > 0 ? (($meses[2] - $meses[1]) / $meses[1]) * 100 : 0;

        $promedioVariacion = ($variacion1 + $variacion2) / 2;

        if ($promedioVariacion > 3) return '↗️ +' . round($promedioVariacion, 1) . '% mensual';
        if ($promedioVariacion < -3) return '↘️ ' . round($promedioVariacion, 1) . '% mensual';
        return '→ Estable';
    }

    private function calcularEficienciaArea($areaId): float
    {
        // TODO: Implementar cálculo real basado en métricas de comprobación y aprobación
        return rand(70, 98);
    }

    private function detectarConceptosConCrecimiento(): array
    {
        // TODO: Implementar análisis comparativo mes vs mes anterior
        return [];
    }

    private function detectarAreasConMejoras(): array
    {
        // TODO: Implementar detección de mejoras mes vs mes anterior
        return [];
    }

    private function getTopConceptosCrecimiento(): array
    {
        // TODO: Implementar análisis de conceptos con mayor variación
        return [];
    }
}
