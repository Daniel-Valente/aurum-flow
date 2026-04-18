<?php

namespace App\Http\Resources\Gasto;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GastoExcepcionDetailResource extends JsonResource
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
            'comentario' => $this->comentario,

            'diferencia' => $this->gasto->monto - ($this->gasto->concepto->tope_referencia ?? 0),

            'gasto' => [
                'id' => $this->gasto->id,
                'monto' => $this->gasto->monto,
                'fecha' => $this->gasto->fecha_gasto,
                'uuid' => $this->gasto->uuid_factura,
                'estatus' => $this->gasto->estatus,
            ],

            'concepto' => [
                'id' => $this->gasto->concepto->id,
                'nombre' => $this->gasto->concepto->nombre,
                'tope_referencia' => $this->gasto->concepto->tope_referencia,
            ],

            'empleado' => [
                'id' => $this->gasto->solicitud->empleado->id,
                'nombre' => $this->gasto->solicitud->empleado->nombre_completo,
                'email' => $this->gasto->solicitud->empleado->user->email,
            ],
        ];
    }
}
