<?php

namespace App\Services\Dashboard;

use App\Models\GastoExcepcion;
use App\Models\Presupuesto;
use App\Models\Solicitud;
use Illuminate\Support\Facades\DB;

class ManagerDashboardService extends DashboardService
{
    public function getData($user): array
    {
        $empleado = $user->empleado;
        $areaId = $empleado->area_id;

        // Cola de aprobaciones
        $pendientesAprobacion = Solicitud::query()
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->where('empleados.area_id', $areaId)
            ->where('solicitudes.estatus', 'Pendiente')
            ->count();

        $excepcionesN1 = $this->getExcepcionesPendientes('1', null, $areaId);

        // Salud del área CON PRESUPUESTO REAL
        $saludArea = $this->getSaludAreaReal($areaId);

        // Eficiencia operativa
        $eficiencia = $this->getEficienciaOperativa($areaId);

        // Comparativa vs otras áreas
        $comparativa = $this->getComparativaAreas($areaId);

        // Actividad reciente
        $actividadReciente = $this->getActividadReciente($areaId);

        return [
            'aprobaciones' => [
                'pendientes' => $pendientesAprobacion,
                'excepciones_n1' => $excepcionesN1,
                'dias_vencimiento' => 2, // SLA de 2 días
            ],
            'salud_area' => $saludArea,
            'eficiencia' => $eficiencia,
            'comparativa' => $comparativa,
            'actividad_reciente' => $actividadReciente,
        ];
    }

    /**
     * Salud del área CON PRESUPUESTO REAL
     */
    private function getSaludAreaReal($areaId): array
    {
        // Buscar presupuesto del área vigente
        $presupuesto = Presupuesto::tipo('area')
            ->area($areaId)
            ->where('periodo', 'mensual')
            ->vigentes()
            ->first();

        if (!$presupuesto) {
            // Si no hay presupuesto, calcular desde solicitudes
            $gastado = Solicitud::query()
                ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
                ->where('empleados.area_id', $areaId)
                ->whereBetween('solicitudes.created_at', [$this->fechaInicio, $this->fechaFin])
                ->whereIn('solicitudes.estatus', ['Autorizado', 'Comprobado'])
                ->sum('solicitudes.monto_total');

            $presupuestoTotal = 0; // Mock si no existe presupuesto formal

            return [
                'presupuesto' => $presupuestoTotal,
                'gastado' => $gastado,
                'comprometido' => 0,
                'disponible' => $presupuestoTotal - $gastado,
                'porcentaje' => $presupuestoTotal > 0
                    ? round(($gastado / $presupuestoTotal) * 100, 1)
                    : 0,
                'proyeccion' => $this->proyectarGastoMensual($gastado),
                'tiene_presupuesto' => false,
                'top_gastadores' => $this->getTopGastadores($areaId),
                'top_conceptos' => $this->getTopConceptos($areaId, $gastado),
            ];
        }

        $proyeccion = $this->proyectarGastoMensual($presupuesto->monto_gastado);

        return [
            'presupuesto' => $presupuesto->monto_total,
            'gastado' => $presupuesto->monto_gastado,
            'comprometido' => $presupuesto->monto_comprometido,
            'disponible' => $presupuesto->monto_disponible,
            'porcentaje' => $presupuesto->porcentaje_consumido,
            'proyeccion' => $proyeccion,
            'tiene_presupuesto' => true,
            'severidad' => $presupuesto->getSeveridad(),
            'dias_restantes' => $presupuesto->dias_restantes,
            'top_gastadores' => $this->getTopGastadores($areaId),
            'top_conceptos' => $this->getTopConceptos($areaId, $presupuesto->monto_gastado),
        ];
    }

    private function getTopGastadores($areaId): array
    {
        return Solicitud::query()
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->where('empleados.area_id', $areaId)
            ->whereBetween('solicitudes.created_at', [$this->fechaInicio, $this->fechaFin])
            ->whereIn('solicitudes.estatus', ['Autorizado', 'Comprobado'])
            ->select(
                'empleados.id',
                'empleados.nombre_completo',
                DB::raw('SUM(solicitudes.monto_total) as total_gastado')
            )
            ->groupBy('empleados.id', 'empleados.nombre_completo')
            ->orderByDesc('total_gastado')
            ->limit(3)
            ->get()
            ->map(function ($e) {
                // Buscar presupuesto individual
                $presupuestoIndividual = Presupuesto::tipo('empleado')
                    ->empleado($e->id)
                    ->where('periodo', 'mensual')
                    ->vigentes()
                    ->first();

                $pctPresupuesto = 0;
                if ($presupuestoIndividual && $presupuestoIndividual->monto_total > 0) {
                    $pctPresupuesto = round(($e->total_gastado / $presupuestoIndividual->monto_total) * 100, 1);
                }

                return [
                    'nombre' => $e->nombre_completo,
                    'gastado' => $e->total_gastado,
                    'pct_presupuesto' => $pctPresupuesto,
                ];
            })
            ->toArray();
    }

    private function getTopConceptos($areaId, $totalGastado): array
    {
        return Solicitud::query()
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->join('solicitud_detalles', 'solicitud_detalles.solicitud_id', '=', 'solicitudes.id')
            ->join('conceptos', 'conceptos.id', '=', 'solicitud_detalles.concepto_id')
            ->where('empleados.area_id', $areaId)
            ->whereBetween('solicitudes.created_at', [$this->fechaInicio, $this->fechaFin])
            ->select(
                'conceptos.nombre',
                DB::raw('SUM(solicitud_detalles.monto_estimado) as total')
            )
            ->groupBy('conceptos.id', 'conceptos.nombre')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(fn($c) => [
                'concepto' => $c->nombre,
                'monto' => $c->total,
                'pct' => $totalGastado > 0 ? round(($c->total / $totalGastado) * 100, 1) : 0,
            ])
            ->toArray();
    }

    private function getEficienciaOperativa($areaId): array
    {
        $tiempoPromedio = Solicitud::query()
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->join('solicitud_aprobaciones', 'solicitud_aprobaciones.solicitud_id', '=', 'solicitudes.id')
            ->where('empleados.area_id', $areaId)
            ->whereBetween('solicitud_aprobaciones.created_at', [$this->fechaInicio, $this->fechaFin])
            ->where('solicitud_aprobaciones.accion', 'aprobado')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (solicitud_aprobaciones.created_at - solicitudes.created_at)) / 3600) as avg_hours')
            ->value('avg_hours');

        $diasPromedio = $tiempoPromedio ? round($tiempoPromedio / 24, 1) : 0;

        $totalSolicitudes = Solicitud::query()
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->where('empleados.area_id', $areaId)
            ->whereBetween('solicitudes.created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $rechazadas = Solicitud::query()
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->where('empleados.area_id', $areaId)
            ->where('solicitudes.estatus', 'Rechazado')
            ->whereBetween('solicitudes.created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $tasaRechazo = $totalSolicitudes > 0 ? round(($rechazadas / $totalSolicitudes) * 100, 1) : 0;

        $gastosTotal = DB::table('gastos')
            ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->where('empleados.area_id', $areaId)
            ->whereBetween('gastos.created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $gastosATiempo = DB::table('gastos')
            ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->where('empleados.area_id', $areaId)
            ->where('gastos.estatus', 'comprobado')
            ->whereBetween('gastos.created_at', [$this->fechaInicio, $this->fechaFin])
            ->whereRaw('(gastos.updated_at::date - gastos.created_at::date) <= 30')->whereRaw('gastos.updated_at <= gastos.created_at + interval \'30 days\'')
            ->count();

        $pctComprobacion = $gastosTotal > 0 ? round(($gastosATiempo / $gastosTotal) * 100, 1) : 100;

        $totalExcepcionesN1 = GastoExcepcion::query()
            ->join('gastos', 'gastos.id', '=', 'gastos_excepciones.gasto_id')
            ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->where('empleados.area_id', $areaId)
            ->where('gastos_excepciones.nivel', '1')
            ->whereBetween('gastos_excepciones.created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $excepcionesRechazadasN1 = GastoExcepcion::query()
            ->join('gastos', 'gastos.id', '=', 'gastos_excepciones.gasto_id')
            ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->where('empleados.area_id', $areaId)
            ->where('gastos_excepciones.nivel', '1')
            ->where('gastos_excepciones.estatus', 'rechazado')
            ->whereBetween('gastos_excepciones.created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $pctExcepcionesRechazadas = $totalExcepcionesN1 > 0
            ? round(($excepcionesRechazadasN1 / $totalExcepcionesN1) * 100, 1)
            : 0;

        return [
            'tiempo_aprobacion_dias' => $diasPromedio,
            'tasa_rechazo_pct' => $tasaRechazo,
            'comprobacion_tiempo_pct' => $pctComprobacion,
            'excepciones_rechazadas_pct' => $pctExcepcionesRechazadas,
        ];
    }

    private function getComparativaAreas($areaIdActual): array
    {
        $areas = DB::table('areas')
            ->leftJoin('empleados', 'empleados.area_id', '=', 'areas.id')
            ->leftJoin('solicitudes', 'solicitudes.empleado_id', '=', 'empleados.id')
            ->leftJoin('presupuestos', function ($join) {
                $join->on('presupuestos.area_id', '=', 'areas.id')
                    ->where('presupuestos.tipo', 'area')
                    ->where('presupuestos.periodo', 'mensual')
                    ->where('presupuestos.estatus', 'activo');
            })
            ->whereBetween('solicitudes.created_at', [$this->fechaInicio, $this->fechaFin])
            ->whereIn('solicitudes.estatus', ['Autorizado', 'Comprobado'])
            ->select(
                'areas.id',
                'areas.nombre',
                DB::raw('COALESCE(MAX(presupuestos.monto_total), 250000) as presupuesto'),
                DB::raw('COALESCE(MAX(presupuestos.monto_gastado), SUM(solicitudes.monto_total)) as gastado')
            )
            ->groupBy('areas.id', 'areas.nombre')
            ->get()
            ->map(function ($area) use ($areaIdActual) {
                $pct = $area->presupuesto > 0
                    ? round(($area->gastado / $area->presupuesto) * 100, 1)
                    : 0;

                return [
                    'area' => $area->id === $areaIdActual ? 'Tu área' : $area->nombre,
                    'pct' => $pct,
                ];
            })
            ->toArray();

        $promedio = collect($areas)->avg('pct');
        $areas[] = ['area' => 'Promedio empresa', 'pct' => round($promedio, 1)];

        return $areas;
    }

    private function getActividadReciente($areaId): array
    {
        return Solicitud::query()
            ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
            ->where('empleados.area_id', $areaId)
            ->orderByDesc('solicitudes.created_at')
            ->limit(5)
            ->get(['solicitudes.*', 'empleados.nombre_completo'])
            ->map(fn($s) => [
                'tipo' => $this->getTipoActividad($s),
                'empleado' => $s->nombre_completo,
                'folio' => $s->folio,
                'monto' => $s->monto_total,
                'tiempo' => $s->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    private function getTipoActividad($solicitud): string
    {
        return match ($solicitud->estatus) {
            'Pendiente' => 'envió solicitud',
            'Autorizado' => 'fue autorizada',
            'Rechazado' => 'fue rechazada',
            'Comprobado' => 'comprobó gastos',
            default => 'actualizó solicitud',
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
