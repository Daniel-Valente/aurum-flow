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
    public string $nombre = '';
    public string $cuenta_contable = '';

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

        $this->editingId       = $centroCosto->id;
        $this->nombre          = $centroCosto->nombre;
        $this->cuenta_contable = $centroCosto->cuenta_contable;

        $this->resetValidation();
        $this->modal('centro-costo-form')->show();
    }

    public function save(CentroCostoService $service): void
    {
        $this->validate([
            'nombre' => 'nullable|string|max:100|required_without:cuenta_contable',
            'cuenta_contable' => 'nullable|string|max:20|required_without:nombre',
        ], messages: [
            'nombre.required_without' => 'Debes capturar el nombre o la cuenta contable.',
            'nombre.max' => 'El nombre no puede tener más de :max caracteres.',

            'cuenta_contable.required_without' => 'Debes capturar la cuenta contable o el nombre.',
            'cuenta_contable.max' => 'La cuenta contable no puede tener más de :max caracteres.',
        ]);

        $data = [
            'nombre' => trim($this->nombre),
            'cuenta_contable' => $this->cuenta_contable,
            'estatus' => true
        ];

        if ($this->editingId) {
            $service->update(CentroCosto::findOrFail($this->editingId), $data);
            $msg = 'Referencia contable actualizada correctamente.';
        } else {
            $service->create($data);
            $msg = 'Referencia contable creada correctamente.';
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

        $centroCosto = CentroCosto::findOrFail($this->deletingId);
        $service->toggleEstatus($centroCosto);

        $this->modal('centro-costo-delete')->close();
        $this->reset(['deletingId', 'deletingNombre']);
        $this->dispatch('notify', type: 'success', message: 'Referencia contable dada de baja correctamente.');
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'nombre', 'cuenta_contable']);
        $this->resetValidation();
    }


    public function render(CentroCostoService $service)
    {
        return view('livewire.centros.index', [
            'centroCostos' => $service->paginate(search: $this->search, estatus: $this->estatus),
        ]);
    }
}
