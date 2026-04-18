<?php

namespace App\Http\Requests\Gasto;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGastoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!$this->user()) {
            return false;
        }

        return $this->user()->can('gastos.crear');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'solicitud_id' => 'required|exists:solicitudes,id',
            'empleado_id' => 'required|exists:empleados,id',
            'concepto_id' => 'required|exists:conceptos:id',
            'fecha_gasto' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'uuid_factura' => 'nullable|string'
        ];
    }
}
