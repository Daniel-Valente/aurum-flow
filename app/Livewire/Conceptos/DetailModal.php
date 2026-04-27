<?php

namespace App\Livewire\Conceptos;

use App\Models\Concepto;
use Livewire\Attributes\On;
use Livewire\Component;

class DetailModal extends Component
{
    public ?Concepto $concepto = null;

    #[On('openConceptoDetail')]
    public function open(int $id): void
    {
        $this->concepto = Concepto::with('roles')->findOrFail($id);

        $this->modal('concepto-detail')->show();
    }

    public function close(): void
    {
        $this->concepto = null;
        $this->modal('concepto-detail')->close();
    }

    public function render()
    {
        return view('livewire.conceptos.detail-modal');
    }
}
