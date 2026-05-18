<?php

namespace App\Livewire\Roles;

use App\Services\Roles\RoleService;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    public ?int    $deletingId     = null;
    public string  $deletingNombre = '';

    public function openCreate(): void
    {
        $this->dispatch('openRolForm');
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('openRolForm', id: $id);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openRolDetail', id: $id);
    }

    public function openPermisos(int $id): void
    {
        $this->dispatch('openRolPermisos', id: $id);
    }

    public function openDelete(int $id): void
    {
        $role = Role::withCount('users')->findOrFail($id);

        $this->deletingId     = $role->id;
        $this->deletingNombre = $role->name;

        $this->modal('rol-delete')->show();
    }

    public function delete(RoleService $service): void
    {
        if (!$this->deletingId) return;

        try {
            $role = Role::findOrFail($this->deletingId);
            $service->eliminar($role, auth()->user());

            $this->modal('rol-delete')->close();
            $this->reset(['deletingId', 'deletingNombre']);
            Flux::toast(variant: 'success', text: 'Rol eliminado correctamente.');
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
            $this->modal('rol-delete')->close();
        }
    }

    #[On('rolSaved')]
    public function onRolSaved(string $message): void
    {
        Flux::toast(variant: 'success', text: $message);
    }

    #[On('permisosSaved')]
    public function onPermisosSaved(string $message): void
    {
        Flux::toast(variant: 'success', text: $message);
    }

    public function render(RoleService $service)
    {
        return view('livewire.roles.index', [
            'roles' => $service->todos(),
        ]);
    }
}
