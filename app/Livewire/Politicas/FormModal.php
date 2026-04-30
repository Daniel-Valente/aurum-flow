<?php

namespace App\Livewire\Politicas;

use App\Models\Concepto;
use App\Models\PoliticaGasto;
use App\Services\Concepto\ConceptoService;
use App\Services\Empleado\EmpleadoService;
use App\Services\Gasto\PoliticaGastoService;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class FormModal extends Component
{
    public ?int $editingId = null;

    public ?int $roleId = null;
    public ?int $concepto_id = null;

    public string $monto_max = '';
    public string $tipo_limite = '';
    public bool $permite_excepcion = false;

    public ?string $vigencia_desde = null;
    public ?string $vigencia_hasta = null;

    public ?string $motivo = '';

    public array $roles = [];
    public array $conceptos = [];

    #[On('openPoliticaForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $politica = PoliticaGasto::with(['role', 'concepto'])->findOrFail($id);

            $this->editingId = $id;
            $this->roleId = $politica->role_id;
            $this->concepto_id = $politica->concepto_id;

            $this->monto_max = $politica->monto_max;
            $this->tipo_limite = $politica->tipo_limite;
            $this->permite_excepcion = $politica->permite_excepcion;

            $this->vigencia_desde = $politica->vigencia_desde;
            $this->vigencia_hasta = $politica->vigencia_hasta;

            $this->motivo = $politica->motivo;

            $this->conceptos = app(ConceptoService::class)->list((int) $this->roleId);
        } else {
            $this->resetForm();
        }

        $this->resetValidation();
        $this->modal('politica-form')->show();
    }

    public function updatedRoleId($value)
    {
        if (empty($value)) {
            $this->conceptos = [];
        } else {
            $this->conceptos = app(ConceptoService::class)->list((int) $value);
        }

        $this->concepto_id = null;
    }

    public function rules()
    {
        return [
            'motivo' => $this->isEditing() ? 'required|string' : 'nullable|string',
        ];
    }

    public function isEditing()
    {
        return !is_null($this->editingId);
    }

    public function save(PoliticaGastoService $service): void
    {
        $this->validate([
            'roleId' => 'required|exists:roles,id',
            'concepto_id' => 'required|exists:conceptos,id',
            'monto_max' => 'required|numeric|min:0',
            'permite_excepcion' => 'boolean',
            'vigencia_desde' => 'nullable|date',
            'vigencia_hasta' => 'nullable|date|after_or_equal:vigencia_desde',
            'motivo' => $this->editingId
                ? ['required', 'string', 'min:5']
                : ['nullable'],
        ], messages: [
            'roleId.required' => 'El rol es obligatorio.',
            'concepto_id.required' => 'El concepto es obligatorio.',

            'monto_max.required' => 'El monto máximo es obligatorio.',
            'monto_max.numeric' => 'El monto máximo debe que ser un número.',
            'monto_max.min' => 'El monto máximo no puede ser negativo.',

            'vigencia_desde.date' => 'La vigencia desde no es válida.',
            'vigencia_hasta.date' => 'La vigencia hasta no es válida.',
            'vigencia_hasta.after_or_equal' => 'La vigencia hasta debe ser igual o posterior a la vigencia desde.',

            'motivo.required' => 'El motivo de cambio es obligatorio.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.string' => 'El motivo debe ser texto válido.',
        ]);

        $data = [
            'role_id' => $this->roleId,
            'concepto_id' => $this->concepto_id,
            'monto_max' => $this->monto_max,
            'tipo_limite' => $this->tipo_limite,
            'permite_excepcion' => $this->permite_excepcion,
            'vigencia_desde' => $this->vigencia_desde,
            'vigencia_hasta' => $this->vigencia_hasta,
            'motivo' => $this->motivo,
            'estatus' => true,
        ];

        if($this->editingId) {
            $service->update(PoliticaGasto::findOrFail($this->editingId), $data, auth()->user());
            $msg ='Política actualizada correctamente.';
        } else {
            $service->create($data, auth()->user());
            $msg = 'Política creada correctamente.';
        }

        $this->modal('politica-form')->close();
        $this->resetForm();

        $this->dispatch('politicaSaved', message: $msg);
    }

    public function resetForm(): void
    {
        $this->reset(['roleId', 'concepto_id', 'monto_max', 'tipo_limite', 'permite_excepcion', 'vigencia_desde', 'vigencia_hasta', 'motivo']);
        $this->resetValidation();
    }

    public function mount(EmpleadoService $empleadoService): void
    {
        $this->roles = $empleadoService->roles();
    }

    public function render()
    {
        return view('livewire.politicas.form-modal');
    }
}
