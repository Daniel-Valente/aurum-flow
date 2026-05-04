<?php

namespace App\Livewire\Solicitudes;

use App\Models\Solicitud;
use Livewire\Attributes\On;
use Livewire\Component;

class DetailModal extends Component
{
    public ?Solicitud $solicitud = null;

    #[On('openSolicitudDetail')]
    public function open(int $id): void
    {
        $this->solicitud = Solicitud::with([
            'empleado.user.roles',
            'proyecto',
        ])->findOrFail($id);
;
        $this->modal('solicitud-detail')->show();
    }

    public function close(): void
    {
        $this->solicitud = null;

        $this->modal('solicitud-detail')->close();
    }

    public function render()
    {
        return view('livewire.solicitudes.detail-modal');
    }
}
