<?php

namespace App\Livewire\Politicas;

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
        Flux::toast($message);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openPoliticaDetail', id: $id);
    }

    public function openVersion(int $id): void
    {
        $this->dispatch('openPoliticaVersion', id: $id);
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
