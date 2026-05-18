<?php

namespace App\Livewire\Presupuestos;

use App\Models\Presupuesto;
use App\Services\Area\AreaService;
use App\Services\Empleado\EmpleadoService;
use App\Services\Presupuesto\PresupuestoService;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tipo = '';
    public string $estatus = '';
    public string $periodo = '';
    public ?int $empresaId = null;
    public ?int $areaId = null;
    public ?int $empleadoId = null;
    public ?int $proyectoId = null;
    public string $sortBy = 'created_at';
    public string $sortDir = 'desc';

    public array $areas = [];
    public array $empleados = [];

    public ?int $cancelingId = null;
    public string $cancelingNombre = '';
    public string $motivoCancelacion = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function mount(AreaService $areaService, EmpleadoService $empleadoService): void
    {
        $this->areas = $areaService->list();
        $this->empleados = $empleadoService->list();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'tipo',
            'estatus',
            'periodo',
            'empresaId',
            'areaId',
            'empleadoId',
            'proyectoId'
        ]);
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function openCreate(): void
    {
        $this->dispatch('openPresupuestoForm');
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('openPresupuestoForm', id: $id);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openPresupuestoDetail', id: $id);
    }

    public function openTransferencia(int $id): void
    {
        $this->dispatch('openTransferenciaModal', origenId: $id);
    }

    public function openCancel(int $id): void
    {
        $presupuesto = Presupuesto::findOrFail($id);

        $this->cancelingId = $presupuesto->id;
        $this->cancelingNombre = $presupuesto->codigo . ' - ' . $presupuesto->nombre;
        $this->motivoCancelacion = '';

        $this->modal('presupuesto-cancel')->show();
    }

    public function cancel(PresupuestoService $service): void
    {
        if (!$this->cancelingId) {
            return;
        }

        $this->validate([
            'motivoCancelacion' => 'required|string|min:10|max:500',
        ], [
            'motivoCancelacion.required' => 'El motivo de cancelación es obligatorio.',
            'motivoCancelacion.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivoCancelacion.max' => 'El motivo no puede exceder 500 caracteres.',
        ]);

        try {
            $presupuesto = Presupuesto::findOrFail($this->cancelingId);
            $service->cancelar($presupuesto, auth()->user(), $this->motivoCancelacion);

            $this->modal('presupuesto-cancel')->close();
            $this->reset(['cancelingId', 'cancelingNombre', 'motivoCancelacion']);

            Flux::toast(variant: 'success', text: 'Presupuesto cancelado correctamente.');
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function aprobar(int $id, PresupuestoService $service): void
    {
        try {
            $presupuesto = Presupuesto::findOrFail($id);
            $service->aprobar($presupuesto, auth()->user());

            Flux::toast(variant: 'success', text: 'Presupuesto aprobado correctamente.');
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    #[On('presupuestoSaved')]
    public function onPresupuestoSaved(string $message): void
    {
        Flux::toast(variant: 'success', text: $message);
    }

    #[On('transferenciaCreada')]
    public function onTransferenciaCreada(): void
    {
        Flux::toast(variant: 'success', text: 'Transferencia solicitada correctamente.');
    }

    public function render(PresupuestoService $service)
    {
        return view('livewire.presupuestos.index', [
            'presupuestos' => $service->paginate(
                user: auth()->user(),
                search: $this->search,
                tipo: $this->tipo,
                estatus: $this->estatus,
                periodo: $this->periodo,
                empresaId: $this->empresaId,
                areaId: $this->areaId,
                empleadoId: $this->empleadoId,
                proyectoId: $this->proyectoId,
                sortBy: $this->sortBy,
                sortDir: $this->sortDir,
            ),
        ]);
    }
}
