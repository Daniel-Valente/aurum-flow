<?php

namespace App\Http\Requests\PoliticaGasto;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePoliticaGastoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!$this->user()) {
            return false;
        }

        return $this->user()->can('politicas.crear');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_id' => 'required|exists:roles,id',
            'concepto_id' => 'required|exists:conceptos,id',

            'monto_max' => 'required|numeric|min:0',

            'tipo_limite' => 'required|in:Diario,Viaje',

            'permite_excepcion' => 'boolean',

            'vigencia_desde' => 'nullable|date',
            'vigencia_hasta' => 'nullable|date|after_or_equal:vigencia_desde',
        ];
    }
}
