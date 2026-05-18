<?php

namespace App\Services\Dashboard;

use App\Models\Gasto;
use App\Models\GastoComprobante;
use App\Models\GastoExcepcion;
use App\Models\Solicitud;
use Carbon\CarbonInterface;

abstract class DashboardService
{
    protected CarbonInterface $fechaInicio;
    protected CarbonInterface $fechaFin;
    protected $user;

    public function __construct()
    {
        $this->fechaInicio = now()->startOfMonth();
        $this->fechaFin = now()->endOfMonth();
    }

    /**
     * Método principal que cada rol implementa
     */
    abstract public function getData($user): array;

    /**
     * Helpers compartidos entre roles
     */
    protected function getPresupuestoMensual($roleId = null, $areaId = null): array
    {
        // TODO: Implementar lógica de presupuestos cuando exista la tabla
        // Por ahora retornamos valores mock
        return [
            'total' => 20000,
            'gastado' => 12450,
            'disponible' => 7550,
            'porcentaje' => 62.25,
            'proyeccion' => 18900,
            'dias_restantes' => now()->endOfMonth()->diffInDays(now()),
        ];
    }

    protected function getSolicitudesPendientes($userId = null, $areaId = null): int
    {
        return Solicitud::query()
            ->when($userId, fn($q) => $q->where('empleado_id', function ($sq) use ($userId) {
                $sq->select('id')->from('empleados')->where('user_id', $userId);
            }))
            ->whereIn('estatus', ['Pendiente', 'Autorizado'])
            ->count();
    }

    protected function getGastosSinComprobar($userId = null, $areaId = null): array
    {
        $query = Gasto::query()
            ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
            ->where('gastos.estatus', 'aprobado');

        if ($userId) {
            $query->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
                ->where('empleados.user_id', $userId);
        }

        if ($areaId) {
            $query->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
                ->where('empleados.area_id', $areaId);
        }

        $gastos = $query->select('gastos.*')->get();

        $proximosVencer = $gastos->filter(function ($g) {
            // Política: comprobar en 30 días
            $fechaLimite = $g->created_at->addDays(30);
            return $fechaLimite->diffInDays(now(), false) <= 5;
        });

        return [
            'total' => $gastos->count(),
            'proximos_vencer' => $proximosVencer->count(),
        ];
    }

    protected function getComprobantesRechazados($userId = null): int
    {
        $query = GastoComprobante::query()
            ->where('validacion_manual', 'rechazado');

        if ($userId) {
            $query->join('gastos', 'gastos.id', '=', 'gasto_comprobantes.gasto_id')
                ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
                ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
                ->where('empleados.user_id', $userId);
        }

        return $query->count();
    }

    protected function getExcepcionesPendientes($nivel, $userId = null, $areaId = null): int
    {
        $query = GastoExcepcion::query()
            ->where('gastos_excepciones.nivel', $nivel)
            ->where('gastos_excepciones.estatus', 'pendiente');

        if ($userId) {
            $query->join('gastos', 'gastos.id', '=', 'gastos_excepciones.gasto_id')
                ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
                ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
                ->where('empleados.user_id', $userId);
        }

        if ($areaId) {
            $query->join('gastos', 'gastos.id', '=', 'gastos_excepciones.gasto_id')
                ->join('solicitudes', 'solicitudes.id', '=', 'gastos.solicitud_id')
                ->join('empleados', 'empleados.id', '=', 'solicitudes.empleado_id')
                ->where('empleados.area_id', $areaId);
        }

        return $query->count();
    }
}
