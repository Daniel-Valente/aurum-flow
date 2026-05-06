<?php

namespace App\Providers;

use App\Models\Solicitud;
use App\Policies\SolicitudPolicy;
use Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('comprobacion.manual', fn($user) => $user->puedeHacerComprobacionManual());
        Gate::define(Solicitud::class, SolicitudPolicy::class);
    }
}
