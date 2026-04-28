<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyecto;
use Livewire\Attributes\On;
use Livewire\Component;

class DetailModal extends Component
{
    public ?Proyecto $proyecto = null;

    #[On('openProyectoDetail')]
    public function open(int $id): void
    {
        $this->proyecto = Proyecto::with(['centroCosto', 'responsable'])->findOrFail($id);

        $this->modal('proyecto-detail')->show();
    }

    public function close(): void
    {
        $this->proyecto = null;

        $this->modal('proyecto-detail')->close();
    }

    public function render()
    {
        return view('livewire.proyectos.detail-modal');
    }
}
