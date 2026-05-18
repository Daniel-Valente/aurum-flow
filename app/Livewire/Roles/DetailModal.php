<?php

namespace App\Livewire\Roles;

use App\Services\Roles\RoleService;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class DetailModal extends Component
{
    public ?Role  $role          = null;
    public array  $permisosGrupo = [];
    public bool   $esSistema     = false;
    public int    $totalPermisos = 0;
    public int    $totalUsuarios = 0;

    #[On('openRolDetail')]
    public function open(int $id): void
    {
        $service    = app(RoleService::class);
        $this->role = Role::with('permissions')->withCount('users')->findOrFail($id);

        $this->esSistema     = $service->esSistema($this->role);
        $this->totalPermisos = $this->role->permissions->count();
        $this->totalUsuarios = $this->role->users_count;

        $todosAgrupados      = $service->permisosAgrupados();
        $asignados           = $this->role->permissions->pluck('name')->toArray();
        $this->permisosGrupo = [];

        foreach ($todosAgrupados as $dominio => $items) {
            $asignadosEnGrupo = array_values(
                array_filter($items, fn($p) => in_array($p['name'], $asignados))
            );

            if (!empty($asignadosEnGrupo)) {
                $this->permisosGrupo[$dominio] = $asignadosEnGrupo;
            }
        }

        $this->modal('rol-detail')->show();
    }

    public function render()
    {
        return view('livewire.roles.detail-modal');
    }
}
