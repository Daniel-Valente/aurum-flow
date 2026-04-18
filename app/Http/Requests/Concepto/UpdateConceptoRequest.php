<?php

namespace App\Http\Requests\Concepto;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConceptoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!$this->user()) {
            return false;
        }

        return $this->user()->can('conceptos.editar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigo' => 'sometimes|string|max:50|unique:conceptos,codigo,' . $this->concepto->id,
            'nombre' => 'sometimes|string|max:255',

            'categoria' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',

            'tipo_aplicacion' => 'sometimes|in:Diario,Evento,Viaje',
            'orden' => 'nullable|integer|min:0',

            'requiere_factura' => 'boolean',
            'requiere_comprobante' => 'boolean',
            'requiere_uuid' => 'boolean',
            'permite_sin_factura' => 'boolean',
            'aplica_iva' => 'boolean',
            'acumulable_dia' => 'boolean',

            'tope_referencia' => 'nullable|numeric|min:0',

            'vigencia_desde' => 'nullable|date',
            'vigencia_hasta' => 'nullable|date|after_or_equal:vigencia_desde',

            'estatus' => 'boolean'
        ];
    }
}
