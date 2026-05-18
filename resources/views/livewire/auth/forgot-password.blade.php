<x-layouts::auth :title="__('Olvidé mi contraseña')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Olvidé mi contraseña')" :description="__('Ingresa tu correo electrónico para recibir un enlace de restablecimiento de contraseña')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Correo electrónico')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('restablecer la contraseña') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('O, volver a') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Iniciar Sesión') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
