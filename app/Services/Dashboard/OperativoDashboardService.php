<?php

namespace App\Services\Dashboard;

use App\Models\GastoExcepcion;
use App\Models\Presupuesto;
use App\Models\Solicitud;

class OperativoDashboardService extends DashboardService
{
    public function getData($user): array
    {
        $empleado = $user->empleado;
        $roleId = $user->roles->first()?->id;

        // Acciones pendientes
        $solicitudesRechazadas = Solicitud::where('empleado_id', $empleado->id)
            ->where('estatus', 'Rechazado')
            ->count();

        $borradores = Solicitud::where('empleado_id', $empleado->id)
            ->where('estatus', 'Borrador')
            ->count();

        $gastosSinComprobar = $this->getGastosSinComprobar($user->id);
        $comprobantesRechazados = $this->getComprobantesRechazados($user->id);

        // Presupuesto mensual REAL
        $presupuesto = $this->getPresupuestoActual($empleado->id);

        // Resumen rápido
        $solicitudesMes = Solicitud::where('empleado_id', $empleado->id)
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();

        $autorizadas = Solicitud::where('empleado_id', $empleado->id)
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin])
            ->where('estatus', 'Autorizado')
            ->count();

        $enRevision = Solicitud::where('empleado_id', $empleado->id)
            ->where('estatus', 'Pendiente')
            ->count();

        // Últimas solicitudes
        $ultimasSolicitudes = Solicitud::where('empleado_id', $empleado->id)
            ->with(['detalles.concepto'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'folio' => $s->folio,
                'concepto' => $s->detalles->first()?->concepto->nombre ?? 'Varios',
                'monto' => $s->monto_total,
                'estatus' => $s->estatus,
                'fecha' => $s->created_at->format('d/m/Y'),
            ]);

        return [
            'acciones_pendientes' => [
                'rechazadas' => $solicitudesRechazadas,
                'borradores' => $borradores,
                'gastos_sin_comprobar' => $gastosSinComprobar['total'],
                'proximos_vencer' => $gastosSinComprobar['proximos_vencer'],
                'comprobantes_rechazados' => $comprobantesRechazados,
            ],
            'presupuesto' => $presupuesto,
            'resumen' => [
                'solicitudes_mes' => $solicitudesMes,
                'autorizadas' => $autorizadas,
                'en_revision' => $enRevision,
                'pct_comprobacion' => $this->calcularPctComprobacion($empleado->id),
                'excepciones_rechazadas' => $this->getExcepcionesRechazadas($empleado->id),
            ],
            'ultimas_solicitudes' => $ultimasSolicitudes,
            'tip_del_dia' => $this->getTipDelDia(),
        ];
    }

    private function getPresupuestoActual(int $empleadoId): array
    {
        $presupuesto = Presupuesto::tipo('empleado')
            ->empleado($empleadoId)
            ->vigentes()
            ->orderByDesc('fecha_inicio')
            ->first();

        if (!$presupuesto) {
            return [
                'total' => 0,
                'gastado' => 0,
                'disponible' => 0,
                'porcentaje' => 0,
                'proyeccion' => 0,
                'dias_restantes' => now()->endOfMonth()->diffInDays(now()),
                'tiene_presupuesto' => false,
            ];
        }

        // Calcular proyección
        $diasTranscurridos = now()->day;
        $diasMes = now()->daysInMonth;
        $proyeccion = $diasTranscurridos > 0
            ? ($presupuesto->monto_gastado / $diasTranscurridos) * $diasMes
            : 0;

        return [
            'total' => $presupuesto->monto_total,
            'gastado' => $presupuesto->monto_gastado,
            'comprometido' => $presupuesto->monto_comprometido,
            'disponible' => $presupuesto->monto_disponible,
            'porcentaje' => $presupuesto->porcentaje_consumido,
            'proyeccion' => round($proyeccion, 2),
            'dias_restantes' => $presupuesto->dias_restantes,
            'tiene_presupuesto' => true,
            'severidad' => $presupuesto->getSeveridad(),
        ];
    }

    private function calcularPctComprobacion($empleadoId): float
    {
        $totalGastos = Solicitud::where('solicitudes.empleado_id', $empleadoId)
            ->whereBetween('solicitudes.created_at', [$this->fechaInicio, $this->fechaFin])
            ->join('gastos', 'gastos.solicitud_id', '=', 'solicitudes.id')
            ->count();

        if ($totalGastos === 0) return 100;

        $totalGastos = Solicitud::where('solicitudes.empleado_id', $empleadoId)
            ->whereBetween('solicitudes.created_at', [$this->fechaInicio, $this->fechaFin])
            ->join('gastos', 'gastos.solicitud_id', '=', 'solicitudes.id')
            ->count();

        return round(($comprobados / $totalGastos) * 100, 1);
    }

    private function getExcepcionesRechazadas($empleadoId): int
    {
        return GastoExcepcion::query()
            ->join('gastos', 'gastos.id', '=', 'gastos_excepciones.gasto_id')
            ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
            ->where('solicitudes.empleado_id', $empleadoId)
            ->where('gastos_excepciones.estatus', 'rechazado')
            ->whereBetween('gastos_excepciones.created_at', [$this->fechaInicio, $this->fechaFin])
            ->count();
    }

    private function getTipDelDia(): string
    {
        $tips = [
            'Recuerda: Los gastos mayores a $500 requieren CFDI. Pide factura en el momento.',
            'Guarda todos tus tickets. Puedes fotografiarlos con tu celular y subirlos después.',
            'Los gastos deben comprobarse dentro de 30 días después del viaje.',
            'Si excedes tu presupuesto, justifica el gasto para que tu manager lo apruebe.',
            'Revisa tu presupuesto antes de solicitar viáticos para evitar rechazos.',
        ];

        return $tips[array_rand($tips)];
    }
}
