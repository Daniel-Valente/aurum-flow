<?php

namespace App\Livewire\Areas;

use App\Models\Area;
use App\Services\Area\AreaService;
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
        $this->modal('area-form')->show();
    }

    public function openEdit(int $id): void
    {
        $area = Area::findOrFail($id);

        $this->editingId = $area->id;
        $this->codigo = $area->codigo;
        $this->nombre = $area->nombre;
        $this->estatusForm = (bool) $area->estatus;

        $this->resetValidation();
        $this->modal('area-form')->show();
    }

    public function save(AreaService $service): void
    {
        $this->validate([
            'codigo' => [
                'required', 'string', 'max:20',
                Rule::unique('areas', 'codigo')->ignore($this->editingId),
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
            $service->update(Area::findOrFail($this->editingId), $data);
            $msg = 'Área actualizada correctamente.';
        } else {
            $service->create($data);
            $msg = 'Área creada correctamente.';
        }

        $this->modal('area-form')->close();
        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function openDelete(int $id): void
    {
        $area = Area::findOrFail($id);

        $this->deletingId = $area->id;
        $this->deletingNombre = $area->nombre;

        $this->modal('area-delete')->show();
    }

    public function delete(AreaService $service): void
    {
        if (! $this->deletingId) return;

        $service->delete(Area::findOrFail($this->deletingId));

        $this->modal('area-delete')->close();
        $this->reset(['deletingId', 'deletingNombre']);
        $this->dispatch('notify', type: 'success', message: 'Área eliminada correctamente.');
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'codigo', 'nombre']);
        $this->estatusForm = true;
        $this->resetValidation();
    }

    public function render(AreaService $service)
    {
        return view('livewire.areas.index', [
            'areas' => $service->paginate(search: $this->search, estatus: $this->estatus),
        ]);
    }
}
