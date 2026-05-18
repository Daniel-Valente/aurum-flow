<?php

namespace App\Livewire\Presupuestos;

use App\Models\Presupuesto;
use App\Services\Presupuesto\PresupuestoService;
use Livewire\Attributes\On;
use Livewire\Component;

class DetailModal extends Component
{
    public ?array $detalle = null;

    #[On('openPresupuestoDetail')]
    public function open(int $id, PresupuestoService $service): void
    {
        $this->detalle = $service->detalle(Presupuesto::findOrFail($id));
        $this->modal('presupuesto-detail')->show();
    }

    public function close(): void
    {
        $this->detalle = null;
        $this->modal('presupuesto-detail')->close();
    }

    public function render()
    {
        return view('livewire.presupuestos.detail-modal');
    }
}
