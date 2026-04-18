<?php

namespace App\Http\Resources\Solicitud;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,

            'empleado' => $this->empleado->nombre_completo,
            'usuario' => $this->empleado->user->name,

            'proyecto' => $this->proyecto?->nombre,

            'monto_total' => $this->monto_total,

            'estatus' => $this->estatus,
            'estatus_label' => $this->mapEstatus(),

            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,

            'created_at' => $this->created_at,
        ];
    }

    protected function mapEstatus()
    {
        return match ($this->estatus) {
            'Borrador' => 'Borrador',
            'Pendiente' => 'En aprobación',
            'Autorizado' => 'Aprobado',
            'Rechazado' => 'Rechazado',
            'Comprobado' => 'Comprobado',
            'Cancelado' => 'Cancelado',
            default => $this->estatus,
        };
    }
}
