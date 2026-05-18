<?php

namespace App\Listeners;

use App\Services\Auditoria\ActividadLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogAuthenticationActivity
{
    public function __construct(
        private ActividadLogService $actividadLog
    ) {}

    public function handleLogin(Login $event): void
    {
        $this->actividadLog->registrar([
            'user'                => $event->user,
            'evento'              => 'login',
            'modulo'              => 'auth',
            'entidad'             => $event->user,
            'entidad_descripcion' => "Login exitoso de {$event->user->name}",
            'metadatos'           => [
                'guard'    => $event->guard,
                'remeber'  => request()->filled('remember'),
                'device'   => request()->userAgent(),
                'browser'  => $this->getBrowser(request()->userAgent()),
                'platform' => $this->getPlatform(request()->userAgent()),
            ],
            'severidad' => 'info'
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if (!$event->user) {
            return;
        }

        $this->actividadLog->registrar([
            'user'                => $event->user,
            'evento'              => 'logout',
            'modulo'              => 'auth',
            'entidad'             => $event->user,
            'entidad_descripcion' => "Logout de {$event->user->name}",
            'metadatos' => [
                'guard' => $event->guard,
            ],
            'severidad' => 'info'
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        $this->actividadLog->registrar([
            'user'                => null,
            'evento'              => 'login_failed',
            'modulo'              => 'auth',
            'entidad'             => null,
            'entidad_descripcion' => "Intento de login fallido para: {$event->credentials['email']}",
            'metadatos' => [
                'email' => $event->credentials['email'] ?? null,
                'guard' => $event->guard,
            ],
            'severidad'   => 'warning',
            'es_sensible' => true,
        ]);
    }

    public function handleRegistered(Registered $event): void
    {
        $this->actividadLog->registrar([
            'user'                => $event->user,
            'evento'              => 'registered',
            'modulo'              => 'auth',
            'entidad'             => $event->user,
            'entidad_descripcion' => "Nuevo usuario registrado: {$event->user->name}",
            'severidad'           => 'info',
        ]);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->actividadLog->registrar([
            'user'                => $event->user,
            'evento'              => 'password_reset',
            'modulo'              => 'auth',
            'entidad'             => $event->user,
            'entidad_descripcion' => "Contraseña restablecida para {$event->user->name}",
            'severidad'           => 'warning',
            'es_sensible'         => true,
        ]);
    }

    private function getBrowser(?string $userAgent): ?string
    {
        if (!$userAgent) return null;

        return match(true) {
            str_contains($userAgent, 'Chrome') => 'Chrome',
            str_contains($userAgent, 'Firefox') => 'Firefox',
            str_contains($userAgent, 'Safari') => 'Safari',
            str_contains($userAgent, 'Edge') => 'Edge',
            str_contains($userAgent, 'Opera') => 'Opera',
            default => 'Desconocido',
        };
    }

    private function getPlatform(?string $userAgent): ?string
    {
        if (!$userAgent) return null;

        return match(true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Mac') => 'MacOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            default => 'Desconocido',
        };
    }
}
