<?php

namespace App\Services\Reporte;

use App\Models\Gasto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    private const ALLOWED_SORT_DIRS = ['asc', 'desc'];

    public function dashboard(User $user, array $filters = []): array
    {
        $base = Gasto::query();

        $this->aplicarFiltros($base, $filters);
        $this->aplicarScopePorRol($base, $user);

        // ✅ Un solo SELECT con los tres aggregates — sin 3 queries separadas
        $kpis = (clone $base)
            ->selectRaw('
                COALESCE(SUM(monto), 0)         AS total_gastado,
                COUNT(*)                         AS num_gastos,
                COUNT(DISTINCT solicitud_id)     AS num_solicitudes
            ')
            ->first();

        // ✅ JOIN directo a conceptos — sin with() que dispara queries extra
        $conceptos = (clone $base)
            ->join('conceptos', 'conceptos.id', '=', 'gastos.concepto_id')
            ->selectRaw('gastos.concepto_id, conceptos.nombre, SUM(gastos.monto) AS total')
            ->groupBy('gastos.concepto_id', 'conceptos.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ✅ JOIN directo — sin with('solicitud.proyecto') sobre un GROUP BY
        $proyectos = (clone $base)
            ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
            ->join('proyectos',   'proyectos.id',   '=', 'solicitudes.proyecto_id')
            ->selectRaw('proyectos.id, proyectos.nombre, SUM(gastos.monto) AS total')
            ->groupBy('proyectos.id', 'proyectos.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Rango de fechas obligatorio — sin rango trae toda la historia de la tabla
        $fechaInicio = $filters['fecha_inicio'] ?? now()->subDays(30)->toDateString();
        $fechaFin    = $filters['fecha_fin']    ?? now()->toDateString();

        $timeline = (clone $base)
            ->selectRaw("DATE(fecha_gasto) AS fecha, SUM(monto) AS total")
            ->whereBetween('fecha_gasto', [$fechaInicio, $fechaFin])
            ->groupByRaw("DATE(fecha_gasto)")
            ->orderBy('fecha')
            ->get();

        return [
            'kpis'       => $kpis,
            'conceptos'  => $conceptos,
            'proyectos'  => $proyectos,
            'timeline'   => $timeline,
            'periodo'    => ['desde' => $fechaInicio, 'hasta' => $fechaFin],
        ];
    }

    protected function aplicarFiltros($query, array $filters): void
    {
        $query->when($filters['fecha_inicio'] ?? null,
            fn($q, $v) => $q->whereDate('fecha_gasto', '>=', $v)
        );
        $query->when($filters['fecha_fin'] ?? null,
            fn($q, $v) => $q->whereDate('fecha_gasto', '<=', $v)
        );
        // whereHas en lugar de JOIN para filtros opcionales — más legible y seguro
        $query->when($filters['proyecto_id'] ?? null,
            fn($q, $v) => $q->whereHas('solicitud', fn($qq) => $qq->where('proyecto_id', $v))
        );
        $query->when($filters['concepto_id'] ?? null,
            fn($q, $v) => $q->where('concepto_id', $v)
        );
        $query->when($filters['estatus'] ?? null,
            fn($q, $v) => $q->where('estatus', $v)
        );
    }

    protected function aplicarScopePorRol($query, User $user): void
    {
        if ($user->hasRoleName('admin')) {
            return;
        }

        if ($user->hasRoleName('gerente')) {
            // JOIN directo — más eficiente que whereHas anidado
            $query->join('solicitudes as s_r', 's_r.id', '=', 'gastos.solicitud_id')
                  ->join('empleados as e_r',   'e_r.id', '=', 's_r.empleado_id')
                  ->where('e_r.area_id', $user->empleado->area_id);
            return;
        }

        // Empleado solo ve sus propios gastos
        $query->whereHas('solicitud', fn($q) =>
            $q->where('empleado_id', $user->empleado->id)
        );
    }
}
