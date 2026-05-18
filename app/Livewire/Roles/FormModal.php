<?php

namespace App\Livewire\Roles;

use App\Services\Roles\RoleService;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class FormModal extends Component
{
    public ?int   $editingId = null;
    public string $name      = '';
    public bool   $esSistema = false;

    #[On('openRolForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $role           = Role::findOrFail($id);
            $this->editingId = $role->id;
            $this->name      = $role->name;
            $this->esSistema = app(RoleService::class)->esSistema($role);
        } else {
            $this->resetForm();
        }

        $this->resetValidation();
        $this->modal('rol-form')->show();
    }

    public function save(RoleService $service): void
    {
        $this->validate([
            'name' => 'required|string|max:50|regex:/^[a-z0-9_\-]+$/i',
        ], messages: [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.max'      => 'El nombre no puede exceder 50 caracteres.',
            'name.regex'    => 'Solo letras, números, guiones y guiones bajos.',
        ]);

        try {
            if ($this->editingId) {
                $role = Role::findOrFail($this->editingId);
                $service->actualizar($role, ['name' => $this->name], auth()->user());
                $msg = "Rol '{$this->name}' actualizado correctamente.";
            } else {
                $service->crear(['name' => $this->name], auth()->user());
                $msg = "Rol '{$this->name}' creado correctamente.";
            }

            $this->modal('rol-form')->close();
            $this->resetForm();
            $this->dispatch('rolSaved', message: $msg);
        } catch (\Exception $e) {
            $this->addError('name', $e->getMessage());
        }
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'esSistema']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.roles.form-modal');
    }
}
