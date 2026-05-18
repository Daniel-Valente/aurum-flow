<?php

namespace App\Events;

use App\Models\Presupuesto;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PresupuestoCritico
{
    use Dispatchable, SerializesModels;

    public Presupuesto $presupuesto;
    public float $porcentajeUsado;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Presupuesto $presupuesto,
        float $porcentajeUsado
    ) {
        $this->presupuesto = $presupuesto;
        $this->porcentajeUsado = $porcentajeUsado;
    }
}
