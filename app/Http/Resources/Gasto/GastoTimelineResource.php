<?php

namespace App\Http\Resources\Gasto;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GastoTimelineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'evento' => $this->mapEvento(),
            'raw_evento' => $this->evento(),

            'actor' => $this->actor?->name ?? 'Sistema',

            'fecha' => $this->created_at,

            'descripcion' => $this->generarDescripcion(),

            'cambios' => [
                'antes' => $this->datos_antes,
                'despues' => $this->datos_despues,
            ],

            'tipo' => match ($this->evento) {
                'rechazado' => 'danger',
                'aprobado' => 'success',
                'excepcion_creada' => 'warning',
                default => 'info',
            }
        ];
    }

    protected function mapEvento()
    {
        return match ($this->evento) {
            'creado' => 'Gasto creado',
            'excepcion_creada' => 'Enviado a excepción',
            'aprobado' => 'Aprobado',
            'rechazado' => 'Rechazado',
            'estatus_actualizado' => 'Cambio de estatus',
            default => $this->evento,
        };
    }

    protected function generarDescripcion()
    {
        return match ($this->evento) {

            'creado' => 'Se registró el gasto en el sistema',

            'excepcion_creada' => 'El gasto excede política y requiere aprobación',

            'aprobado' => 'El gasto fue aprobado',

            'rechazado' => 'El gasto fue rechazado',

            'estatus_actualizado' => 'Se actualizó el estatus del gasto',

            default => 'Evento registrado',
        };
    }
}
