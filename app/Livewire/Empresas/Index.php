<?php

namespace App\Livewire\Empresas;

use App\Models\Empresa;
use App\Services\Empresa\EmpresaService;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search  = '';
    public string $estatus = '';

    public ?int $deletingId       = null;
    public string $deletingNombre = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'estatus']);
        $this->resetPage();
    }

    public function openDelete(int $id): void
    {
        $empresa  = Empresa::findOrFail($id);
        $this->deletingId     = $empresa->id;
        $this->deletingNombre = $empresa->nombre;

        $this->modal('empresa-delete')->show();
    }

    public function delete(EmpresaService $service): void
    {
        if (!$this->deletingId) return;

        $empresa  = Empresa::findOrFail($this->deletingId);
        $service->toggleActivo($empresa, auth()->user());

        $this->modal('empresa-delete')->close();
        $this->reset(['deletingId', 'deletingNombre']);
        Flux::toast(variant: 'success', text: 'Empresa deshabilitada correctamente.');
    }

    public function openCreate(): void
    {
        $this->dispatch('openEmpresaForm');
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('openEmpresaForm', id: $id);
    }

    public function openConfiguracion(int $id): void
    {
        $this->dispatch('openConfiguracionModal', empresaId: $id);
    }

    #[On('empresaSaved')]
    public function onEmpresaSaved(string $message): void
    {
        Flux::toast(variant: 'success', text: $message);
    }

    #[On('configuracionGuardada')]
    public function onConfiguracionGuardada(string $mensaje): void
    {
        Flux::toast(variant: 'success', text: $mensaje);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openEmpresaDetail', id: $id);
    }

    #[On('empresasImportados')]
    public function onEmpresasImportados(int $total): void
    {
        Flux::toast(variant: 'success', text: "{$total} empresas importados correctamente.");
    }

    public function openImport(): void
    {
        $this->dispatch('openImportEmpresas');
    }

    public function render(EmpresaService $service)
    {
        return view('livewire.empresas.index', [
            'empresas' => $service->paginate(
                search     : $this->search,
                soloActivas: $this->estatus,
            ),
        ]);
    }
}
