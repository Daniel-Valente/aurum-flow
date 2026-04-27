<?php

namespace App\Livewire\Centros;

use App\Models\CentroCosto;
use App\Services\CentroCosto\CentroCostoService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estatus = '';

    public ?int $editingId = null;
    public string $codigo = '';
    public string $nombre = '';
    public bool $estatusForm = true;

    public ?int $deletingId = null;
    public string $deletingNombre = '';


    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'estatus']);
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('centro-costo-form')->show();
    }

    public function openEdit(int $id): void
    {
        $centroCosto = CentroCosto::findOrFail($id);

        $this->editingId = $centroCosto->id;
        $this->codigo = $centroCosto->codigo;
        $this->nombre = $centroCosto->nombre;
        $this->estatusForm = (bool) $centroCosto->estatus;

        $this->resetValidation();
        $this->modal('centro-costo-form')->show();
    }

    public function save(CentroCostoService $service): void
    {
        $this->validate([
            'codigo' => [
                'required', 'string', 'max:20',
                Rule::unique('centros_costos', 'codigo')->ignore($this->editingId),
            ],
            'nombre' => 'required|string|max:100',
            'estatusForm' => 'boolean'
        ], messages: [
            'codigo.required'   => 'El código es obligatorio.',
            'codigo.max'        => 'El código no puede tener más de :max caracteres.',
            'codigo.unique'     => 'Este código ya está registrado.',
            'nombre.required'   => 'El nombre es obligatorio.',
            'nombre.max'        => 'El nombre no puede tener más de :max caracteres.',
        ]);

        $data = [
            'codigo' => strtoupper(trim($this->codigo)),
            'nombre' => trim($this->nombre),
            'estatus' => $this->estatusForm
        ];

        if ($this->editingId) {
            $service->update(CentroCosto::findOrFail($this->editingId), $data);
            $msg = 'Centro de Costo actualizado correctamente.';
        } else {
            $service->create($data);
            $msg = 'Centro de Costo creado correctamente.';
        }

        $this->modal('centro-costo-form')->close();
        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function openDelete(int $id): void
    {
        $centroCosto = CentroCosto::findOrFail($id);

        $this->deletingId = $centroCosto->id;
        $this->deletingNombre = $centroCosto->nombre;

        $this->modal('centro-costo-delete')->show();
    }

    public function delete(CentroCostoService $service): void
    {
        if (! $this->deletingId) return;

        $service->delete(CentroCosto::findOrFail($this->deletingId));

        $this->modal('centro-costo-delete')->close();
        $this->reset(['deletingId', 'deletingNombre']);
        $this->dispatch('notify', type: 'success', message: 'Centro de Costo eliminado correctamente.');
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'codigo', 'nombre']);
        $this->estatusForm = true;
        $this->resetValidation();
    }


    public function render(CentroCostoService $service)
    {
        return view('livewire.centros.index', [
            'centroCostos' => $service->paginate(search: $this->search, estatus: $this->estatus),
        ]);
    }
}
