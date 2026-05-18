<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use App\Services\Roles\RoleService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Role;

class PermissionModal extends Component
{
    public ?int    $rolId     = null;
    public ?string $rolNombre = null;
    public bool    $esSistema = false;

    public array $seleccionados = [];

    #[On('openRolPermisos')]
    public function open(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);

        $this->rolId         = $role->id;
        $this->rolNombre     = $role->name;
        $this->esSistema     = app(RoleService::class)->esSistema($role);
        $this->seleccionados = $role->permissions->pluck('name')->toArray();

        $this->resetValidation();
        $this->modal('rol-permisos')->show();
    }

    #[Computed()]
    public function permisosAgrupados(): array
    {
        return app(RoleService::class)->permisosAgrupados();
    }

    public function toggleGrupo(string $dominio): void
    {
        $permisosDelGrupo = collect($this->permisosAgrupados[$dominio] ?? [])
            ->pluck('name')
            ->toArray();

        $todosSeleccionados = $this->grupoCompleto($dominio);
        if ($todosSeleccionados) {
            $this->seleccionados = array_values(
                array_diff($this->seleccionados, $permisosDelGrupo)
            );
        } else {
            $this->seleccionados = array_values(
                array_unique(array_merge($this->seleccionados, $permisosDelGrupo))
            );
        }
    }

    public function seleccionarTodos(): void
    {
        $todos = [];
        foreach ($this->permisosAgrupados as $permisos) {
            foreach ($permisos as $permiso) {
                $todos[] = $permiso['name'];
            }
        }
        $this->seleccionados = array_unique($todos);
    }

    public function limpiarTodos(): void
    {
        $this->seleccionados = [];
    }

    public function save(RoleService $service): void
    {
        $role = Role::findOrFail($this->rolId);
        $service->sincronizarPermisos($role, $this->seleccionados, auth()->user());

        $total = count($this->seleccionados);
        $this->modal('rol-permisos')->close();
        $this->resetForm();
        $this->dispatch('permisosSaved', message: "Permisos del rol actualizados ({$total} asignados).");
    }

    public function grupoCompleto(string $dominio): bool
    {
        $nombres = collect($this->permisosAgrupados[$dominio] ?? [])
            ->pluck('name')
            ->toArray();
        if (empty($nombres)) return false;

        return count(array_intersect($nombres, $this->seleccionados)) === count($nombres);
    }

    public function grupoIndeterminado(string $dominio): bool
    {
        $nombres = collect($this->permisosAgrupados[$dominio] ?? [])
            ->pluck('name')
            ->toArray();
        $intersect = count(array_intersect($nombres, $this->seleccionados));

        return $intersect > 0 && $intersect < count($nombres);
    }

    public function totalSeleccionados(): int
    {
        return count($this->seleccionados);
    }

    public function totalDisponibles(): int
    {
        return collect($this->permisosAgrupados)->flatten(1)->count();
    }

    private function resetForm(): void
    {
        $this->reset(['rolId', 'rolNombre', 'esSistema', 'seleccionados']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.roles.permission-modal');
    }
}
