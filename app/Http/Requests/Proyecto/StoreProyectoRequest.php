<?php

namespace App\Http\Requests\Proyecto;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProyectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()) {
            return false;
        }

        return $this->user()->can('proyectos.crear');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:50|unique:proyectos,codigo',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:100',

            'cliente' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'region' => 'nullable|string|max:255',

            'prioridad' => 'nullable|in:Baja,Media,Alta',
            'estado_operativo' => 'nullable|string|max:100',

            'centro_costo_id' => 'nullable|exists:centros_costos,id',
            'responsable_id' => 'nullable|exists:empleados,id',

            'presupuesto_total' => 'nullable|numeric|min:0',

            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',

            'pais' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
        ];
    }
}
