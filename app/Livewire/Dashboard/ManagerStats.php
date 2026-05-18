<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\ManagerDashboardService;
use Livewire\Component;

class ManagerStats extends Component
{
    public array $data = [];

    public function mount(ManagerDashboardService $service)
    {
        $this->data = $service->getData(auth()->user());
    }

    public function render()
    {
        return view('livewire.dashboard.manager-stats');
    }
}
