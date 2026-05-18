<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Index extends Component
{
    public string $rol;

    public function mount()
    {
        $user = auth()->user();
        $this->rol = $user->empleado->role->name ?? '';
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}
