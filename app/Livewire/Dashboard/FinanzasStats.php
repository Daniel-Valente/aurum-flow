<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\FinanzasDashboardService;
use Livewire\Component;

class FinanzasStats extends Component
{
    public array $data = [];

    public function mount(FinanzasDashboardService $service)
    {
        $this->data = $service->getData(auth()->user());
    }

    public function render()
    {
        return view('livewire.dashboard.finanzas-stats');
    }
}
