<?php

namespace App\Livewire\Empresas;

use App\Models\Empresa;
use App\Services\Empresa\ConfiguracionEmpresaService;
use Livewire\Attributes\On;
use Livewire\Component;

class DetailModal extends Component
{
    public ?Empresa $empresa = null;
    public ?array $configuracion = null;

    #[On('openEmpresaDetail')]
    public function open(int $id): void
    {
        $this->empresa = Empresa::findOrFail($id);

        $service = app(ConfiguracionEmpresaService::class);
        $this->configuracion = $service->stats($this->empresa);

        $this->modal('empresa-detail')->show();
    }

    public function render()
    {
        return view('livewire.empresas.detail-modal');
    }
}
