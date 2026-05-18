<x-layouts::auth :title="__('Confirmar Contraseña')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirmar Contraseña')"
            :description="__('Este es un área segura de la aplicación. Por favor, confirme su contraseña antes de continuar.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('Contraseña')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('Confirmar') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
