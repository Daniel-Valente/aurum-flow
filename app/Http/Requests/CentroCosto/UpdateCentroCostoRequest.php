<?php

namespace App\Http\Requests\CentroCosto;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCentroCostoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if(!$this->user()) {
            return false;
        }

        return $this->user()->can('centros_costos.editar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:50|unique:centros_costos,codigo,' . $this->centro_costo->id,
            'nombre' => 'required|string|max:255',
            'estatus' => 'boolean'
        ];
    }
}
