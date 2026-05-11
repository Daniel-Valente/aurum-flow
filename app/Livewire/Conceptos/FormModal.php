<?php

namespace App\Livewire\Conceptos;

use App\Models\Concepto;
use App\Services\Concepto\ConceptoService;
use Livewire\Attributes\On;
use Livewire\Component;

class FormModal extends Component
{
    public ?int $editingId = null;

    public string $codigo  = '';
    public string $nombre  = '';

    public string $searchCategory = '';
    public string  $categoria      = '';
    public string  $descripcion    = '';

    public bool    $aplica_iva     = true;
    public bool    $aplica_ish     = false;
    public bool    $aplica_ieps    = false;

    public ?string $tope_referencia = null;

    public ?string $vigencia_desde = null;
    public ?string $vigencia_hasta = null;

    public array $categorias = [];

    public function mount(ConceptoService $conceptoService): void
    {
        $this->categorias = $conceptoService->categorias();
    }

    #[On('openConceptoForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $concepto = Concepto::findOrFail($id);

            $this->editingId = $concepto->id;

            $this->codigo           = $concepto->codigo;
            $this->nombre           = $concepto->nombre;
            $this->categoria        = $concepto->categoria        ?? '';
            $this->descripcion      = $concepto->descripcion      ?? '';

            $this->aplica_iva       = $concepto->aplica_iva;
            $this->aplica_ish       = $concepto->aplica_ish;
            $this->aplica_ieps      = $concepto->aplica_ieps;

            $this->vigencia_desde = $concepto->vigencia_desde?->format('Y-m-d');
            $this->vigencia_hasta = $concepto->vigencia_hasta?->format('Y-m-d');
        } else {
            $this->resetForm();
        }

        $this->resetValidation();
        $this->modal('concepto-form')->show();
    }

    public function createCategory()
    {
        $this->categoria = $this->searchCategory;
    }

    public function save(ConceptoService $service): void
    {
        $this->validate([
            'nombre'          => 'required|string|max:255',
            'categoria'       => 'nullable|string|max:255',
            'descripcion'     => 'nullable|string|max:500',

            'aplica_iva'      => 'boolean',
            'aplica_ish'      => 'boolean',
            'aplica_ieps'     => 'boolean',

            'vigencia_desde'  => 'nullable|date',
            'vigencia_hasta'  => 'nullable|date|after_or_equal:vigencia_desde',
        ], messages: [
            'nombre.required'          => 'El nombre es obligatorio.',

            'vigencia_desde.date'      => 'La fecha de vigencia desde no es válida.',
            'vigencia_hasta.date'      => 'La fecha de vigencia hasta no es válida.',
            'vigencia_hasta.after_or_equal' => 'La vigencia hasta debe ser igual o posterior a la vigencia desde.',
        ]);

        $data = [
            'nombre'          => $this->nombre,
            'categoria'       => $this->categoria       ?: null,
            'descripcion'     => $this->descripcion     ?: null,
            'aplica_iva'      => $this->aplica_iva,
            'aplica_ish'      => $this->aplica_ish,
            'aplica_ieps'     => $this->aplica_ieps,
            'vigencia_desde'  => $this->vigencia_desde  ?: null,
            'vigencia_hasta'  => $this->vigencia_hasta  ?: null,
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

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'codigo', 'nombre', 'categoria', 'descripcion',
            'aplica_iva', 'aplica_ieps', 'aplica_ish',
            'vigencia_desde', 'vigencia_hasta',
        ]);
        $this->aplica_iva  = true;
        $this->aplica_ieps = false;
        $this->aplica_ish  = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.conceptos.form-modal');
    }
}
