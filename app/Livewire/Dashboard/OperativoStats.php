<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\OperativoDashboardService;
use Livewire\Component;

class OperativoStats extends Component
{
    public array $data = [];

    public function mount(OperativoDashboardService $service)
    {
        $this->data = $service->getData(auth()->user());
    }

    public function render()
    {
        return view('livewire.dashboard.operativo-stats');
    }
}
