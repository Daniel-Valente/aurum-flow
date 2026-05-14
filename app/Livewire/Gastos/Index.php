<?php

namespace App\Livewire\Gastos;

use App\Models\ComprobacionTarjeta;
use App\Models\Solicitud;
use App\Services\Gasto\ComprobacionTarjetaService;
use App\Services\Solicitudes\SolicitudService;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $tab          = 'solicitudes'; // 'solicitudes' | 'tarjeta'
    public string $search       = '';
    public string $cumplimiento = '';

    public ?int   $deletingId     = null;
    public string $deletingNombre = '';
    public string $motivo_cancelacion = '';

    public ?int  $createdId    = null;
    public string $createdFolio = '';

    public string $searchTarjeta = '';
    public string $estatus =       '';

    public function updatedSearch():        void { $this->resetPage(); }
    public function updatedCumplimiento():  void { $this->resetPage(); }
    public function updatedSearchTarjeta(): void { $this->resetPage(); }
    public function updatedEstatus():       void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['search', 'cumplimiento', 'estatus', 'searchTarjeta']);
        $this->resetPage();
    }

    public function show(int $id): void
    {
        $this->redirectRoute('solicitudes.show', $id);
    }

    public function showComprobacionTarjeta(int $id): void
    {
        $this->redirectRoute('tarjetas.show', $id);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openSolicitudDetail', id: $id);
    }

    public function openCreate(): void
    {
        $this->dispatch('openTarjetaForm');
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('openTarjetaForm', id: $id);
    }

    public function openDetailComprobacion(int $id): void
    {
        $this->dispatch('openTarjetaDetail', id: $id);
    }

    #[On('solicitudError')]
    public function onSolicitudError(string $message): void
    {
        Flux::toast(variant: 'danger', text: $message);
    }

    #[On('tarjetaPeriodoCreado')]
    public function onTarjetaPeriodoCreado(string $message, int $id, bool $isNew = false): void
    {
        Flux::toast(variant: 'success', text: $message);

        if ($isNew) {
            $comprobacion = ComprobacionTarjeta::select('id', 'folio')->findOrFail($id);
            $this->createdId    = $id;
            $this->createdFolio = $comprobacion->folio;

            $this->modal('comprobacion-tarjeta-creada')->show();
        }
    }

    #[On('tarjetaError')]
    public function onTarjetaError(string $message): void
    {
        Flux::toast(variant: 'danger', text: $message);
    }

    public function stayHere(): void
    {
        $this->reset(['createdId', 'createdFolio']);
        $this->modal('comprobacion-tarjeta-creada')->close();
    }

    public function goToDetail(): void
    {
        $id = $this->createdId;
        $this->reset(['createdId', 'createdFolio']);
        $this->modal('comprobacion-tarjeta-creada')->close();

        $this->redirectRoute('tarjetas.show', $id);
    }

    public function render(SolicitudService $service, ComprobacionTarjetaService $tarjetaService)
    {
        $solicitudes = $service->paginateAutorizados(
            user:        auth()->user(),
            search:      $this->search,
            cumplimiento: $this->cumplimiento,
        );

        $tarjetas = $tarjetaService->paginate(
            user:    auth()->user(),
            search:  $this->searchTarjeta,
            estatus: $this->estatus
        );

        return view('livewire.gastos.index', [
            'solicitudes' => $solicitudes,
            'tarjetas' => $tarjetas
        ]);
    }
}
