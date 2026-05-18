<?php

namespace App\Providers;

use App\Models\Empleado;
use App\Models\GastoComprobante;
use App\Models\Presupuesto;
use App\Models\Solicitud;
use App\Observers\EmpleadoObserver;
use App\Observers\GastoComprobanteObserver;
use App\Observers\PresupuestoObserver;
use App\Observers\SolicitudObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        /*DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );*/

        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        Empleado::observe(EmpleadoObserver::class);
        Solicitud::observe(SolicitudObserver::class);
        Presupuesto::observe(PresupuestoObserver::class);
        GastoComprobante::observe(GastoComprobanteObserver::class);
    }
}
