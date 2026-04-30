<?php

namespace App\Livewire\Politicas;

use App\Models\PoliticaGasto;
use App\Services\Concepto\ConceptoService;
use App\Services\Empleado\EmpleadoService;
use App\Services\Gasto\PoliticaGastoService;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estatus = '';
    public string $vigencia = ''; //Vigente | Futura | Expirada | Sin vigencia
    public ?int $rolId = null;
    public ?int $conceptoId = null;

    public array $roles = [];
    public array $conceptos = [];

    public ?int $deletingId = null;
    public string $deletingNombre = '';

    public function updatingRol(): void
    {
        $this->resetPage();
    }

    public function updatingVigencia(): void
    {
        $this->resetPage();
    }

    public function updatingConcepto(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'estatus', 'vigencia', 'rolId', 'conceptoId']);
        $this->resetPage();
    }

    public function mount(ConceptoService $conceptoService, EmpleadoService $empleadoService): void
    {
        $this->roles = $empleadoService->roles();
        $this->conceptos = $conceptoService->list();
    }

    public function openCreate(): void
    {
        $this->dispatch('openPoliticaForm');
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('openPoliticaForm', id: $id);
    }

    #[On('politicaSaved')]
    public function onPoliticaSaved(string $message): void
    {
        Flux::toast(variant: 'success', text: $message);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openPoliticaDetail', id: $id);
    }

    public function openVersion(int $id): void
    {
        $this->dispatch('openPoliticaVersion', id: $id);
    }

        public function openDelete(int $id): void
    {
        $politica = PoliticaGasto::with([
            'role:id,name',
            'concepto:id,nombre'
        ])->findOrFail($id);

        $this->deletingId = $politica->id;
        $this->deletingNombre = $politica->nombre;

        $this->modal('area-delete')->show();
    }

    public function delete(PoliticaGastoService $service): void
    {
        if (! $this->deletingId) return;

        $politica = PoliticaGasto::with([
            'role:id,name',
            'concepto:id,nombre'
        ])->findOrFail($this->deletingId);
        $service->toggleEstatus($politica, auth()->user());

        $this->modal('politica-delete')->close();
        $this->reset(['deletingId', 'deletingNombre']);
        $this->dispatch('notify', type: 'success', message: 'Política dada de baja correctamente.');
    }

    public function render(PoliticaGastoService $service)
    {
        return view('livewire.politicas.index', [
            'politicas' => $service->paginate(
                roleId: $this->rolId,
                conceptoId: $this->conceptoId,
                vigencia: $this->vigencia,
                estatus: $this->estatus
            )
        ]);
    }
}
