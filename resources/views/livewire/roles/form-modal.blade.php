<flux:modal name="rol-form" class="w-full max-w-md">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar rol' : 'Nuevo rol' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId
                    ? 'Modifica el nombre del rol. Los permisos se gestionan por separado.'
                    : 'Define el nombre del rol. Después podrás asignarle permisos.' }}
            </flux:subheading>
        </div>

        @if ($esSistema)
            <div class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-800 dark:bg-amber-900/20">
                <flux:icon name="lock-closed" class="size-5 shrink-0 text-amber-600 dark:text-amber-400" />
                <flux:text size="sm" class="text-amber-800 dark:text-amber-200">
                    Este es un rol de sistema. Solo puedes gestionar sus permisos, no su nombre.
                </flux:text>
            </div>
        @endif

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                    Identificación del rol
                </p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Usa nombres en minúsculas, sin espacios. Ej: <code class="font-mono">supervisor_ventas</code>
                </p>
            </div>

            <div class="p-4">
                <flux:field>
                    <flux:label badge="Requerido">Nombre del rol</flux:label>
                    <flux:input
                        wire:model="name"
                        placeholder="Ej. supervisor_ventas"
                        :disabled="$esSistema"
                        class="font-mono"
                        autofocus
                    />
                    <flux:description size="sm">
                        Solo letras minúsculas, números, guiones y guiones bajos.
                    </flux:description>
                    <flux:error name="name" />
                </flux:field>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>

            @unless ($esSistema)
                <flux:button
                    variant="primary"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">
                        {{ $editingId ? 'Guardar cambios' : 'Crear rol' }}
                    </span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </flux:button>
            @endunless
        </div>
    </div>
</flux:modal>
