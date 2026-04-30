<?php

namespace App\Livewire\Politicas;

use App\Models\PoliticaGasto;
use Livewire\Attributes\On;
use Livewire\Component;

class DetailModal extends Component
{
    public ?PoliticaGasto $politica = null;

    #[On('openPoliticaDetail')]
    public function open(int $id): void
    {
        $this->politica = PoliticaGasto::with([
            'role:id,name',
            'concepto:id,nombre'
        ])->findOrFail($id);

        $this->modal('politica-detail')->show();
    }

    public function close(): void
    {
        $this->politica = null;

        $this->modal('politica-detail')->close();
    }

    public function render()
    {
        return view('livewire.politicas.detail-modal');
    }
}
