<?php

namespace App\Http\Resources\Concepto;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConceptoResource extends JsonResource
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
            'categoria' => $this->categoria,
            'descripcion' => $this->descripcion,
            'tipo_aplicacion' => $this->tipo_aplicacion,
            'orden' => $this->orden,
            'requiere_factura' => $this->requiere_factura,
            'requiere_comprobante' => $this->requiere_comprobante,
            'requiere_uuid' => $this->requiere_uuid,
            'permite_sin_factura' => $this->permite_sin_factura,
            'aplica_iva' => $this->aplica_iva,
            'acumulable_dia' => $this->acumulable_dia,
            'tope_referencia' => $this->tope_referencia,
            'vigencia_desde' => $this->vigencia_desde,
            'vigencia_hasta' => $this->vigencia_hasta,
            'estatus' => $this->estatus,
        ];
    }
}
