<?php

namespace App\Livewire\Gastos;

use App\Models\Solicitud;
use App\Services\Solicitudes\SolicitudService;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $tab          = 'solicitudes'; // 'solicitudes' | 'manual'
    public string $search       = '';
    public string $cumplimiento = '';

    public ?int   $deletingId     = null;
    public string $deletingNombre = '';
    public string $motivo_cancelacion = '';

    public ?int  $createdId    = null;
    public string $createdFolio = '';

    public function updatedSearch():       void { $this->resetPage(); }
    public function updatedEstatus():      void { $this->resetPage(); }
    public function updatedCumplimiento(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['search', 'cumplimiento']);
        $this->resetPage();
    }

    public function show(int $id): void
    {
        $this->redirectRoute('solicitudes.show', $id);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openSolicitudDetail', id: $id);
    }

    #[On('solicitudError')]
    public function onSolicitudError(string $message): void
    {
        Flux::toast(variant: 'danger', text: $message);
    }

    public function render(SolicitudService $service)
    {
        $solicitudes = $service->paginateAutorizados(
            user:        auth()->user(),
            search:      $this->search,
            cumplimiento: $this->cumplimiento,
        );

        return view('livewire.gastos.index', [
            'solicitudes' => $solicitudes
        ]);
    }
}
