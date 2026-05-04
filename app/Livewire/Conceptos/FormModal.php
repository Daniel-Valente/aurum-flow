<?php

namespace App\Livewire\Conceptos;

use App\Models\Concepto;
use App\Services\Concepto\ConceptoService;
use App\Services\Empleado\EmpleadoService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class FormModal extends Component
{
    public ?int $editingId = null;

    // — Identificación —
    public string $codigo  = '';
    public string $nombre  = '';

    // — Clasificación —
    public string  $categoria      = '';
    public string  $descripcion    = '';
    public string  $tipo_aplicacion = '';
    public ?int    $orden          = null;

    // — Naturaleza fiscal (único flag que queda en concepto) —
    public bool    $aplica_iva     = true;

    // — Referencia de mercado —
    public ?string $tope_referencia = null;

    // — Vigencia —
    public ?string $vigencia_desde = null;
    public ?string $vigencia_hasta = null;

    // — Acceso por rol —
    public array $rolesSeleccionados = [];

    // — Datos para inputs —
    public array $roles = [];

    // -------------------------------------------------------------------------
    // Ciclo de vida
    // -------------------------------------------------------------------------

    public function mount(EmpleadoService $empleadoService): void
    {
        $this->roles = $empleadoService->roles();
    }

    // -------------------------------------------------------------------------
    // Apertura del modal
    // -------------------------------------------------------------------------

    #[On('openConceptoForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $concepto = Concepto::with('roles')->findOrFail($id);

            $this->editingId = $concepto->id;

            $this->codigo           = $concepto->codigo;
            $this->nombre           = $concepto->nombre;
            $this->categoria        = $concepto->categoria        ?? '';
            $this->descripcion      = $concepto->descripcion      ?? '';
            $this->tipo_aplicacion  = $concepto->tipo_aplicacion;
            $this->orden            = $concepto->orden;

            $this->aplica_iva       = $concepto->aplica_iva;
            $this->tope_referencia  = $concepto->tope_referencia !== null
                ? (string) $concepto->tope_referencia
                : null;

            $this->vigencia_desde = $concepto->vigencia_desde?->format('Y-m-d');
            $this->vigencia_hasta = $concepto->vigencia_hasta?->format('Y-m-d');

            $this->rolesSeleccionados = array_map(
                'strval',
                $concepto->roles->pluck('name')->toArray()
            );
        } else {
            $this->resetForm();
        }

        $this->resetValidation();
        $this->modal('concepto-form')->show();
    }

    // -------------------------------------------------------------------------
    // Guardar
    // -------------------------------------------------------------------------

    public function save(ConceptoService $service): void
    {
        $this->validate([
            'nombre'          => 'required|string|max:255',
            'categoria'       => 'nullable|string|max:255',
            'descripcion'     => 'nullable|string|max:500',
            'tipo_aplicacion' => 'required|string|in:Diario,Evento,Viaje',
            'orden'           => 'nullable|integer|min:0',

            'aplica_iva'      => 'boolean',

            'tope_referencia' => 'nullable|numeric|min:0',

            'vigencia_desde'  => 'nullable|date',
            'vigencia_hasta'  => 'nullable|date|after_or_equal:vigencia_desde',

            'rolesSeleccionados'   => 'array',
            'rolesSeleccionados.*' => 'exists:roles,name',
        ], messages: [
            'nombre.required'          => 'El nombre es obligatorio.',

            'tipo_aplicacion.required' => 'El tipo de aplicación es obligatorio.',
            'tipo_aplicacion.in'       => 'El tipo debe ser Diario, Evento o Viaje.',

            'orden.integer'            => 'El orden debe ser un número entero.',
            'orden.min'                => 'El orden no puede ser negativo.',

            'tope_referencia.numeric'  => 'El tope de referencia debe ser un número.',
            'tope_referencia.min'      => 'El tope de referencia no puede ser negativo.',

            'vigencia_desde.date'      => 'La fecha de vigencia desde no es válida.',
            'vigencia_hasta.date'      => 'La fecha de vigencia hasta no es válida.',
            'vigencia_hasta.after_or_equal' => 'La vigencia hasta debe ser igual o posterior a la vigencia desde.',
        ]);

        $data = [
            'nombre'          => $this->nombre,
            'categoria'       => $this->categoria       ?: null,
            'descripcion'     => $this->descripcion     ?: null,
            'tipo_aplicacion' => $this->tipo_aplicacion,
            'orden'           => $this->orden            ?? 0,
            'aplica_iva'      => $this->aplica_iva,
            'tope_referencia' => filled($this->tope_referencia) ? $this->tope_referencia : null,
            'vigencia_desde'  => $this->vigencia_desde  ?: null,
            'vigencia_hasta'  => $this->vigencia_hasta  ?: null,
            'roles'           => $this->rolesSeleccionados,
            'estatus'         => true,
        ];

        if ($this->editingId) {
            $service->update(Concepto::findOrFail($this->editingId), $data);
            $msg = 'Concepto actualizado correctamente.';
        } else {
            $service->create($data);
            $msg = 'Concepto creado correctamente.';
        }

        $this->modal('concepto-form')->close();
        $this->resetForm();
        $this->dispatch('conceptoSaved', message: $msg);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'codigo', 'nombre', 'categoria', 'descripcion',
            'tipo_aplicacion', 'orden',
            'aplica_iva', 'tope_referencia',
            'vigencia_desde', 'vigencia_hasta',
            'rolesSeleccionados',
        ]);
        $this->aplica_iva = true; // default
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.conceptos.form-modal');
    }
}
