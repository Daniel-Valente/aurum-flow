<?php

namespace App\Http\Resources\Proyecto;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProyectoResource extends JsonResource
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
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'cliente' => $this->cliente,

            'prioridad' => $this->prioridad,
            'estado_operativo' => $this->estado_operativo,

            'presupuesto_total' => $this->presupuesto_total,

            'fechas' => [
                'inicio' => $this->fecha_inicio,
                'fin' => $this->fecha_fin,
            ],

            'ubicacion' => [
                'pais' => $this->pais,
                'estado' => $this->estado,
                'ciudad' => $this->ciudad,
            ],

            'centro_costo' => $this->whenLoaded('centroCosto'),
            'responsable' => $this->whenLoaded('responsable'),

            'estatus' => $this->estatus,
        ];
    }
}
