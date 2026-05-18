<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\AdminDashboardService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AdminStats extends Component
{
    public array $data = [];

    public function mount(AdminDashboardService $service)
    {
        $this->data = $service->getData(auth()->user());
    }

    #[Computed()]
    public function estadoGeneral(): string
    {
        $pct = $this->data['kpis_estrategicos']['porcentajes_gastado'] ?? 0;

        return match(true) {
            $pct > 95 => 'critico',
            $pct > 85 => 'alerta',
            $pct > 75 => 'precaucion',
            default => 'saludable'
        };
    }

    public function render()
    {
        return view('livewire.dashboard.admin-stats');
    }
}
