<?php

namespace App\Http\Resources\Empleado;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class EmpleadoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'nombre' => $this->nombre_completo,
            'puesto' => $this->puesto,

            'estatus' => $this->estatus,

            'area' => [
                'id' => $this->area?->id,
                'nombre' => $this->area?->nombre,
            ],

            'centro_costo' => [
                'id' => $this->centroCosto?->id,
                'nombre' => $this->centroCosto?->nombre,
            ],

            'usuario' => [
                'id' => $this->user?->id,
                'email' => $this->user?->email,
            ],

            'fecha_ingreso' => $this->fecha_ingreso?->format('Y-m-d'),
        ];
    }
}
