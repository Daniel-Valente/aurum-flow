<?php

namespace App\Http\Requests\CentroCosto;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCentroCostoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if(!$this->user()) {
            return false;
        }

        return $this->user()->can('centros_costos.crear');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:50|unique:centros_costos,codigo',
            'nombre' => 'required|string|max:255',
        ];
    }
}
