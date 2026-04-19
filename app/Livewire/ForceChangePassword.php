<?php

namespace App\Livewire;

use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ForceChangePassword extends Component
{
    public bool $open = true;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Validación en tiempo real
    protected function rules()
    {
        return [
            'current_password' => ['required', 'current_password:web'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
            ],
        ];
    }

    protected function messages()
    {
        return [
            'current_password.required' => 'Ingresa tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual es incorrecta. Verifica e intenta de nuevo.',
            'password.required' => 'Debes crear una nueva contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden. Asegúrate de escribir la misma contraseña dos veces.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.uncompromised' => 'Esta contraseña aparece en filtraciones de seguridad. Elige una diferente.',
        ];
    }

    public function mount()
    {
        $this->open = auth()->user()?->must_change_password ?? false;
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['password', 'password_confirmation'])) {
            $this->validateOnly('password');
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function save()
    {
        $validated = $this->validate();
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        $user->refresh();

        $this->open = false;
        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->dispatch('toast',
            variant: 'success',
            title: '¡Contraseña actualizada!',
            description: 'Tu contraseña se cambió correctamente.'
        );

        Flux::toast(
            variant: 'success',
            text: '¡Contraseña actualizada!'
        );
    }

    public function render()
    {
        return view('livewire.force-change-password');
    }
}
