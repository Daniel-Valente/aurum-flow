<?php

namespace App\Http\Resources\Gasto;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GastoExcepcionResource extends JsonResource
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
            'nivel' => $this->nivel,
            'estatus' => $this->estatus,

            'gasto' => [
                'id' => $this->gasto->id,
                'monto' => $this->gasto->monto,
                'fecha' => $this->gasto->fecha_gasto,
            ],

            'concepto' => [
                'id' => $this->gasto->concepto->id,
                'nombre' => $this->gasto->concepto->nombre,
            ],

            'empleado' => [
                'id' => $this->gasto->empleado->id,
                'nombre' => $this->gasto->empleado->nombre_completo,
            ]
        ];
    }
}
