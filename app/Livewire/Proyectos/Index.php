<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyecto;
use App\Services\CentroCosto\CentroCostoService;
use App\Services\Proyecto\ProyectoService;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estatus = '';
    public string $tipo = '';
    public string $region = '';
    public string $estadoOperativo = '';
    public ?int $centroCostoId = null;
    public ?int $responsableId = null;

    public ?int $deletingId = null;
    public string $deletingNombre = '';

    public array $centrosCostos = [];
    public array $regiones = [];

    public function updatingCentroCostoId(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'estatus', 'tipo', 'estadoOperativo', 'centroCostoId']);
        $this->resetPage();
    }

    public function getBadgeColorAttribute()
    {
        return match ($this->estado_operativo) {
            'Draft' => 'gray',
            'Activo' => 'green',
            default => 'red',
        };
    }

    public function mount(ProyectoService $proyectoService, CentroCostoService $centroService): void
    {
        $this->centrosCostos = $centroService->list();
        $this->regiones = $proyectoService->regiones();
    }

    public function openCreate(): void
    {
        $this->dispatch('openProyectoForm');
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('openProyectoForm', id: $id);
    }

    #[On('proyectoSaved')]
    public function onProyectoSaved(string $message): void
    {
        Flux::toast(variant: 'success', text: $message);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openProyectoDetail', id: $id);
    }


    public function openDelete(int $id): void
    {
        $proyecto = Proyecto::with(['centroCosto', 'responsable'])->findOrFail($id);

        $this->deletingId     = $proyecto->id;
        $this->deletingNombre = $proyecto->nombre;
        $this->modal('proyecto-delete')->show();
    }

    public function delete(ProyectoService $service): void
    {
        if (! $this->deletingId) return;
        $proyecto = Proyecto::with(['centroCosto', 'responsable'])->findOrFail($this->deletingId);

        $service->toggleEstatus($proyecto);

        $this->modal('proyecto-delete')->close();
        $this->reset(['deletingId', 'deletingNombre']);
        $this->dispatch('notify', type: 'success', message: 'Proyecto deshabilitado correctamente.');
        Flux::toast(variant: 'success', text: 'Proyecto deshabilitado correctamente.');
    }


    public function render(ProyectoService $service)
    {
        return view('livewire.proyectos.index', [
            'proyectos' => $service->paginate(
                search: $this->search,
                estatus: $this->estatus,
                tipo: $this->tipo,
                region: $this->region,
                estadoOperativo: $this->estadoOperativo,
                responsableId: $this->responsableId  ?: null,
                centroCostoId: $this->centroCostoId ?: null,
            ),
        ]);
    }
}
