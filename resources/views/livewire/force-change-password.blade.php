<flux:modal
    wire:model="open"
    :dismissible="false"
    variant="bare"
    class="backdrop-blur-md p-6 bg-white dark:bg-zinc-900 rounded-xl"
>
    <div class="space-y-4">
        <flux:heading size="lg">
            Cambio obligatorio de contraseña
        </flux:heading>

        <flux:text class="mt-2 text-zinc-500">
            Por seguridad, debes actualizar tu contraseña antes de continuar.
        </flux:text>

        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label>Contraseña actual</flux:label>
                <flux:input
                    type="password"
                    viewable
                    wire:model.blur="current_password"
                    placeholder="Ingresa tu contraseña actual"
                />
                <flux:error name="current_password" />
            </flux:field>

            <flux:field>
                <flux:label>Nueva contraseña</flux:label>
                <flux:input
                    type="password"
                    viewable
                    wire:model.blur="password"
                    placeholder="Mínimo 8 caracteres"
                />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>Confirmar nueva contraseña</flux:label>
                <flux:input
                    type="password"
                    viewable
                    wire:model.blur="password_confirmation"
                    placeholder="Repite tu nueva contraseña"
                    :invalid="$password !== $password_confirmation && $password_confirmation !== ''"
                />
                <flux:error name="password_confirmation" />

                {{-- Mensaje de coincidencia en tiempo real --}}
                @if($password_confirmation !== '')
                    @if($password === $password_confirmation)
                        <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                            <flux:icon.check-circle class="size-4" />
                            Las contraseñas coinciden
                        </p>
                    @else
                        <p class="text-xs text-red-600 mt-1 flex items-center gap-1">
                            <flux:icon.x-circle class="size-4" />
                            Las contraseñas no coinciden
                        </p>
                    @endif
                @endif
            </flux:field>

            <flux:button
                type="submit"
                variant="primary"
                class="w-full"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">Actualizar contraseña</span>
                <span wire:loading wire:target="save" class="flex items-center justify-center gap-2">
                    <flux:icon.loading class="animate-spin size-4" />
                    Actualizando...
                </span>
            </flux:button>
        </form>
    </div>
</flux:modal>
