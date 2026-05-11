<?php

namespace App\Livewire\Tarjeta;

use App\Services\Gasto\ComprobacionTarjetaService;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search  = '';
    public string $estatus = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedEstatus(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['search', 'estatus']);
        $this->reset();
    }

    public function show(int $id): void
    {
        $this->redirectRoute('tarjeta.show', $id);
    }

    #[On('tarjetaPeriodoCreado')]
    public function onPeriodoCreado(string $message): void
    {
        $this->resetPage();
        Flux::toast(variant: 'success', text: $message);
    }

    #[On('tarjetaError')]
    public function onError(string $message): void
    {
        Flux::toast(variant: 'danger', text: $message);
    }

    public function render(ComprobacionTarjetaService $service)
    {
        return view('livewire.tarjeta.index', [
            'periodos' => $service->paginate(
                user:    auth()->user(),
                search:  $this->search,
                estatus: $this->estatus
            )
        ]);
    }
}
