<?php

namespace App\Livewire\Conceptos;

use App\Services\Concepto\ConceptoService;
use App\Services\Empleado\EmpleadoService;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estatus = '';
    public string $rol = '';
    public string $tipo = '';
    public string $categoria = '';
    public string $vigencia = '';

    public array $roles = [];
    public array $categorias = [];

    public function updatingRol(): void
    {
        $this->resetPage();
    }

    public function updatingVigencia(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'estatus', 'rol', 'tipo', 'vigencia']);
        $this->resetPage();
    }

    public function mount(EmpleadoService $empleadoService, ConceptoService $conceptoService): void
    {
        $this->roles = $empleadoService->roles();
        $this->categorias = $conceptoService->categorias();
    }

    public function openCreate(): void
    {
        $this->dispatch('openConceptoForm');
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('openConceptoForm', id: $id);
    }

    #[On('conceptoSaved')]
    public function onConceptoSaved(string $message): void
    {
        Flux::toast($message);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openConceptoDetail', id: $id);
    }

    public function render(ConceptoService $service)
    {
        return view('livewire.conceptos.index', [
            'conceptos' => $service->paginate(
                search: $this->search,
                tipoAplicacion: $this->tipo,
                estatus: $this->estatus,
                categoria: $this->categoria,
                vigencia: $this->vigencia,
                rol: $this->rol
            ),
        ]);
    }
}
