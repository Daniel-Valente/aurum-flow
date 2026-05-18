<?php

namespace App\Services\Auditoria;

use App\Models\ActividadLog;
use Illuminate\Support\Facades\Request;

class ActividadLogService
{
    public function registrar(array $data): ActividadLog
    {
        $user = $data['user'] ?? auth()->user();

        $location = $this->getLocationFromIp(request()->ip());

        return ActividadLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Sistema',
            'user_email' => $user?->email ?? $data['metadatos']['email'] ?? null,
            'user_role' => $user?->roles?->first()?->name,

            'empresa_id' => $data['empresa_id'] ?? $user?->empleado?->empresa_id,
            'area_id' => $data['area_id'] ?? $user?->empleado?->area_id,

            'evento' => $data['evento'],
            'modulo' => $data['modulo'],

            'entidad_type' => isset($data['entidad']) ? get_class($data['entidad']) : null,
            'entidad_id' => $data['entidad']?->id,
            'entidad_descripcion' => $data['entidad_descripcion'] ?? null,

            'datos_antes' => $data['datos_antes'] ?? null,
            'datos_despues' => $data['datos_despues'] ?? null,
            'metadatos' => $data['metadatos'] ?? null,

            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()?->getId(),

            'ciudad' => $location['ciudad'] ?? null,
            'pais' => $location['pais'] ?? null,
            'latitud' => $location['latitud'] ?? null,
            'longitud' => $location['longitud'] ?? null,

            'severidad' => $data['severidad'] ?? 'info',
            'es_sensible' => $data['es_sensible'] ?? false,
        ]);
    }

    public function actividadUsuario(int $userId, int $limit = 50)
    {
        return ActividadLog::usuario($userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function actividadModulo(string $modulo, int $limit = 50)
    {
        return ActividadLog::modulo($modulo)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function actividadesSensibles(int $dias = 7)
    {
        return ActividadLog::sensibles()
            ->entreFechas(now()->subDays($dias), now())
            ->orderByDesc('created_at')
            ->get();
    }

    public function estadisticas($fechaInicio, $fechaFin)
    {
        return [
            'total_actividades' => ActividadLog::entreFechas($fechaInicio, $fechaFin)->count(),
            'usuarios_activos' => ActividadLog::entreFechas($fechaInicio, $fechaFin)
                ->distinct('user_id')
                ->count('user_id'),
            'por_modulo' => ActividadLog::entreFechas($fechaInicio, $fechaFin)
                ->groupBy('modulo')
                ->selectRaw('modulo, COUNT(*) as total')
                ->pluck('total', 'modulo'),
            'por_evento' => ActividadLog::entreFechas($fechaInicio, $fechaFin)
                ->groupBy('evento')
                ->selectRaw('evento, COUNT(*) as total')
                ->pluck('total', 'evento'),
        ];
    }

    private function getLocationFromIp(?string $ip): array
    {
        if (!$ip || $ip === '127.0.0.1' || str_starts_with($ip, '192.168.')) {
            return [];
        }

        return [];
    }
}
