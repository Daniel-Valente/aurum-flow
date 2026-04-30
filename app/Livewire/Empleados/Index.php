<?php

namespace App\Livewire\Empleados;

use App\Models\Empleado;
use App\Services\CentroCosto\CentroCostoService;
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
    public ?int   $areaId = null;
    public ?int   $centroCostoId = null;
    public string $rol = '';

    public array $roles = [];
    public array $centrosCostos = [];

    public ?int $deletingId = null;
    public string $deletingNombre = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function mount(EmpleadoService $service, CentroCostoService $centroService): void
    {
        $this->roles = $service->roles();
        $this->centrosCostos = $centroService->list();
    }

    public function updatingRol(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'estatus', 'areaId', 'rol', 'centroCostoId']);
        $this->resetPage();
    }

    public function openDelete(int $id): void
    {
        $empleado = Empleado::with('user.roles')->findOrFail($id);
        $authUser = auth()->user();

        if ($authUser->hasRole('gerente')) {
            $rolObjetivo = $empleado->role?->name;

            if (in_array($rolObjetivo, ['gerente', 'admin'])) {
                $this->dispatch('notify',
                    type: 'error',
                    message: 'No tienes permiso para desactivar este empleado.'
                );
                return;
            }
        }

        $this->deletingId     = $empleado->id;
        $this->deletingNombre = $empleado->user?->email;
        $this->modal('empleado-delete')->show();
    }

    public function delete(EmpleadoService $service): void
    {
        if (! $this->deletingId) return;

        $service->delete(Empleado::findOrFail($this->deletingId));

        $this->modal('empleado-delete')->close();
        $this->reset(['deletingId', 'deletingNombre']);
        $this->dispatch('notify', type: 'success', message: 'Empleado deshabilitado correctamente.');
        Flux::toast(variant: 'success', text: 'Empleado deshabilitado correctamente.');
    }

    public function openCreate(): void
    {
        $this->dispatch('openEmpleadoForm');
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('openEmpleadoForm', id: $id);
    }

    #[On('empleadoSaved')]
    public function onEmpleadoSaved(string $message): void
    {
        $this->dispatch('notify', type: 'success', message: $message);
        Flux::toast(variant: 'success', text: $message);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openEmpleadoDetail', id: $id);
    }

    public function render(EmpleadoService $service)
    {
        return view('livewire.empleados.index', [
            'empleados' => $service->paginate(
                search:        $this->search,
                estatus:       $this->estatus,
                areaId:        $this->areaId ?: null,
                centroCostoId: $this->centroCostoId ?: null,
                rol:           $this->rol ?: null,
            ),
        ]);
    }
}
