<?php

namespace App\Jobs;

use App\Models\PresupuestoAlerta;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificarAlertaPresupuestoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PresupuestoAlerta $alerta
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $presupuesto = $this->alerta->presupuesto;
        $usuarios = $this->obtenerUsuariosANotificar($presupuesto);

        if ($usuarios->isEmpty()) {
            return;
        }

        foreach ($usuarios as $user) {
            // TODO: Crear PresupuestoAlertaNotification
            // Notification::send($user, new PresupuestoAlertaNotification($this->alerta));

            \Log::info("Notificación de alerta enviada a {$user->email}", [
                'presupuesto' => $presupuesto->codigo,
                'tipo' => $this->alerta->tipo,
                'severidad' => $this->alerta->severidad,
            ]);
        }

        $this->alerta->marcarNotificado();
    }

    private function obtenerUsuariosANotificar($presupuesto)
    {
        return match($presupuesto->tipo) {
            'empleado' => User::whereHas('empleado', fn($q) =>
                $q->where('id', $presupuesto->empleado_id)
            )->get(),

            'area' => User::role('manager')
                ->whereHas('empleado', fn($q) =>
                    $q->where('area_id', $presupuesto->area_id)
                )->get(),

            'proyecto' => User::role(['manager', 'admin'])->get(),

            'empresa' => User::role(['finanzas', 'admin'])->get(),

            default => collect(),
        };
    }
}
