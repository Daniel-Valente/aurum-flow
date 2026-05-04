<?php

namespace App\Livewire\Autorizaciones;

use App\Models\GastoExcepcion;
use App\Services\Area\AreaService;
use App\Services\Gasto\GastoExcepcionService;
use App\Services\Proyecto\ProyectoService;
use App\Services\Solicitudes\SolicitudService;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $tab       = 'solicitudes'; // 'solicitudes' | 'excepciones'
    public string $search    = '';
    public ?int $proyecto_id = null;
    public ?int $area_id     = null;

    public array $proyectos = [];
    public array $areas     = [];

    public function updatedTab(): void         { $this->resetPage(); }
    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedProyectoId(): void  { $this->resetPage(); }
    public function updatedAreaId(): void      { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['search', 'proyecto_id', 'area_id']);
        $this->resetPage();
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openAutorizacionDetail', id: $id);
    }

    public function openExcepcion(int $id): void
    {
        $this->dispatch('openExcepcionDetail', id: $id);
    }

    #[On('autorizacionResuelta')]
    public function onAutorizacionResuelta(string $message): void
    {
        Flux::toast(variant: 'success', text: $message);
    }

    #[On('autorizacionError')]
    public function onAutorizacionError(string $message): void
    {
        Flux::toast(variant: 'danger', text: $message);
    }

    #[On('excepcionResuelta')]
    public function onExcepcionResuelta(string $message): void
    {
        Flux::toast(variant: 'success', text: $message);
    }

    public function mount(ProyectoService $proyectoService, AreaService $areaService): void
    {
        $this->proyectos = $proyectoService->list();
        $this->areas     = $areaService->list();
    }

    public function render(SolicitudService $service)
    {
        $user  = auth()->user();
        $rolNombre = $user->roles->first()?->name;

        // Qué nivel de excepción puede resolver este rol
        $nivelFiltro = match($rolNombre) {
            'manager' => 1,
            'admin'   => 2,
            default   => null,
        };

        $excepciones = $nivelFiltro
            ? GastoExcepcion::with([
                'gasto.solicitud.empleado',
                'gasto.solicitud.proyecto',
                'gasto.concepto',
                'aprobador',
            ])
            ->where('nivel', $nivelFiltro)
            ->where('estatus', 'pendiente')
            // ✅ Scope por rol
            ->when(
                $rolNombre === 'manager' && $user->empleado?->area_id,
                fn($q) => $q->whereHas('gasto.solicitud.empleado', fn($q2) =>
                    $q2->where('area_id', $user->empleado->area_id)
                )
            )
            ->latest()
            ->paginate(15)
            : collect();

        return view('livewire.autorizaciones.index', [
            'autorizaciones'  => $service->paginateAutorizaciones(
                user: $user,
                search: $this->search,
                proyectoId: $this->proyecto_id,
                areaId: $this->area_id,
            ),
            'excepciones'     => $excepciones,
            'nivelFiltro'     => $nivelFiltro,
            'totalExcepciones'=> $nivelFiltro
                ? GastoExcepcion::where('nivel', $nivelFiltro)->where('estatus', 'pendiente')->count()
                : 0,
        ]);
    }
}
