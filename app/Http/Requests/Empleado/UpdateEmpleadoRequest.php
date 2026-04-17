<?php

namespace App\Http\Requests\Empleado;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpleadoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!$this->user()) {
            return false;
        }

        return $this->user()->can('empleados.editar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|unique:users,email,' . $this->route('empleado')->user_id,

            'nombre_completo' => 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'area_departamento' => 'required|string|max:255',

            'area_id' => 'required|exists:areas,id',
            'centro_costo_id' => 'required|exists:centros_costos,id',

            'rfc' => 'required|string|max:13',
            'curp' => 'required|string|max:18',

            'banco_nomina' => 'required|string|max:255',
            'cuenta_nomina' => 'required|string|max:255',
            'clabe_nomina' => 'required|string|max:18',

            'nss' => 'required|string|max:20',

            'fecha_ingreso' => 'nullable|date',

            'telefono' => 'nullable|string|max:20'
        ];
    }
}
