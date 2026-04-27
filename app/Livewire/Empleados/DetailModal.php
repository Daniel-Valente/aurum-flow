<?php

namespace App\Livewire\Empleados;

use App\Models\Empleado;
use Livewire\Attributes\On;
use Livewire\Component;

class DetailModal extends Component
{
    public ?Empleado $empleado = null;

    #[On('openEmpleadoDetail')]
    public function open(int $id): void
    {
        $this->empleado = Empleado::with([
            'user.roles',
            'area',
            'centroCosto'
        ])->findOrFail($id);

        $this->modal('empleado-detail')->show();
    }

    public function close(): void
    {
        $this->empleado = null;
        $this->modal('empleado-detail')->close();
    }

    public function render()
    {
        return view('livewire.empleados.detail-modal');
    }
}
