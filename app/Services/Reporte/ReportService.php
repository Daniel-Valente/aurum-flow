<?php

namespace App\Services\Reporte;

use App\Models\Gasto;
use App\Models\User;

class ReportService
{
    public function dashboard(User $user, array $filters = [])
    {
        $query = Gasto::with(['concepto', 'solicitud.proyecto', 'solicitud.empleado']);

        $this->aplicarFiltros($query, $filters);
        $this->aplicarScopePorRol($query, $user);

        return [
            'kpis' => $this->kpis($query),
            'conceptos' => $this->gastosPorConcepto($query),
            'proyectos' => $this->gastosPorProyecto($query),
            'timeline' => $this->timeline($query),
        ];
    }

    protected function kpis($query)
    {
        return [
            'total_gastado' => (clone $query)->sum('monto'),
            'num_gastos' => (clone $query)->count(),
            'num_solicitudes' => (clone $query)->distinct('solicitud_id')->count(),
        ];
    }

    protected function gastosPorConcepto($query)
    {
        return (clone $query)
            ->selectRaw('concepto_id, SUM(monto) as total')
            ->groupBy('concepto_id')
            ->with('concepto:id,nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    protected function gastosPorProyecto($query)
    {
        return (clone $query)
            ->selectRaw('solicitud_id, SUM(monto) as total')
            ->groupBy('solicitud_id')
            ->with('solicitud.proyecto:id,nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    protected function timeline($query)
    {
        return (clone $query)
            ->selectRaw('DATE(fecha_gasto) as fecha, SUM(monto) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
    }

    protected function aplicarFiltros($query, array $filters)
    {
        $query->when($filters['fecha_inicio'] ?? null, fn($q, $v) => $q->whereDate('fecha_gasto', '>=', $v));
        $query->when($filters['fecha_fin'] ?? null, fn($q, $v) => $q->whereDate('fecha_gasto', '<=', $v));
        $query->when($filters['proyecto_id'] ?? null, fn($q, $v) => $q->whereHas('solicitud', fn($qq) => $qq->where('proyecto_id', $v)));
        $query->when($filters['concepto_id'] ?? null, fn($q, $v) => $q->where('concepto_id', $v));
    }

    protected function aplicarScopePorRol($query, User $user)
    {
        if ($user->hasRoleName('admin')) {
            return $query;
        }

        if ($user->hasRoleName('gerente')) {
            return $query->whereHas('solicitud.empleado', function ($q) use ($user) {
                $q->where('area_id', $user->empleado->area_id);
            });
        }

        return $query->whereHas('solicitud', function ($q) use ($user) {
            $q->where('empleado_id', $user->empleado->id);
        });
    }
}
