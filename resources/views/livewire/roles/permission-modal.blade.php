<flux:modal name="rol-permisos" class="w-full max-w-4xl" scroll="body">
    <div class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="lg">
                    Permisos del rol
                    <span class="ml-2 font-mono text-blue-600 dark:text-blue-400 capitalize">
                        {{ $rolNombre }}
                    </span>
                </flux:heading>
                <flux:subheading>
                    Selecciona los permisos que tendrá este rol. Los cambios aplican inmediatamente al guardar.
                </flux:subheading>
            </div>
            @if ($esSistema)
                <flux:badge color="amber" icon="lock-closed">Sistema</flux:badge>
            @endif
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="flex items-center justify-between gap-4 px-4 py-3 bg-zinc-50 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex flex-col">
                        <flux:text size="sm" class="font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ $this->totalSeleccionados() }} de {{ $this->totalDisponibles() }} permisos
                        </flux:text>
                        <flux:text size="xs" class="text-zinc-500">
                            seleccionados
                        </flux:text>
                    </div>

                    @php
                        $porcentaje = $this->totalDisponibles() > 0
                            ? round(($this->totalSeleccionados() / $this->totalDisponibles()) * 100)
                            : 0;
                    @endphp
                    <div class="w-32 h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-300
                                {{ $porcentaje === 100 ? 'bg-green-500' : 'bg-blue-500' }}"
                            style="width: {{ $porcentaje }}%"
                        ></div>
                    </div>
                    <flux:text size="xs" class="text-zinc-500">{{ $porcentaje }}%</flux:text>
                </div>

                <div class="flex items-center gap-2">
                    <flux:button
                        size="sm"
                        variant="ghost"
                        wire:click="limpiarTodos"
                        wire:loading.attr="disabled"
                    >
                        Limpiar todo
                    </flux:button>
                    <flux:button
                        size="sm"
                        variant="filled"
                        wire:click="seleccionarTodos"
                        wire:loading.attr="disabled"
                    >
                        Seleccionar todo
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @foreach ($this->permisosAgrupados as $dominio => $permisos)
                @php
                    $completo       = $this->grupoCompleto($dominio);
                    $indeterminado  = $this->grupoIndeterminado($dominio);
                    $countDominio   = count($permisos);
                    $countSel       = collect($permisos)
                                        ->filter(fn($p) => in_array($p['name'], $seleccionados))
                                        ->count();
                @endphp

                <div class="rounded-xl border overflow-hidden
                    {{ $completo ? 'border-blue-200 dark:border-blue-800' : 'border-zinc-200 dark:border-zinc-700' }}
                    {{ $indeterminado ? 'border-blue-100 dark:border-blue-900' : '' }}
                ">
                    <div
                        class="flex items-center gap-3 px-4 py-3 cursor-pointer select-none
                            {{ $completo
                                ? 'bg-blue-50 dark:bg-blue-900/20'
                                : 'bg-zinc-50 dark:bg-zinc-900' }}"
                        wire:click="toggleGrupo('{{ $dominio }}')"
                    >
                        <div class="relative flex size-5 shrink-0 items-center justify-center rounded
                            border-2 transition-colors
                            {{ $completo
                                ? 'border-blue-600 bg-blue-600 dark:border-blue-500 dark:bg-blue-500'
                                : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-800' }}"
                        >
                            @if ($completo)
                                <flux:icon name="check" class="size-3 text-white" />
                            @elseif ($indeterminado)
                                <div class="size-2 rounded-sm bg-blue-500"></div>
                            @endif
                        </div>

                        <div class="flex-1">
                            <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ $dominio }}
                            </p>
                        </div>

                        <flux:badge
                            size="sm"
                            color="{{ $completo ? 'blue' : ($countSel > 0 ? 'zinc' : 'zinc') }}"
                        >
                            {{ $countSel }}/{{ $countDominio }}
                        </flux:badge>
                    </div>

                    <div class="grid grid-cols-2 gap-3 *gap-x-2 bg-zinc-100 dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700
                                sm:grid-cols-3 md:grid-cols-4 p-2">
                        @foreach ($permisos as $permiso)
                            @php $activo = in_array($permiso['name'], $seleccionados); @endphp

                            <flux:field variant="inline">
                                <flux:checkbox
                                    wire:model.live.debounce.100ms="seleccionados"
                                    value="{{ $permiso['name'] }}"
                                    class="size-4 rounded border-zinc-300 text-blue-600
                                        focus:ring-blue-500 dark:border-zinc-600
                                        dark:bg-zinc-800 dark:checked:bg-blue-500"
                                />
                                <flux:label>
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300 leading-tight">
                                        {{ $permiso['label'] }}
                                    </span>
                                </flux:label>
                            </flux:field>
                        @endforeach
                    </div>
                </div>
            @endforeach


            <flux:description>
                Los permisos personalizados a nivel usuario deben implementarse mediante desarrollo.
            </flux:description>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:text size="sm" class="text-zinc-500">
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $this->totalSeleccionados() }}
                </span>
                permisos seleccionados de
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $this->totalDisponibles() }}
                </span>
                disponibles.
            </flux:text>

            <div class="flex gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button
                    variant="primary"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">Guardar permisos</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </flux:button>
            </div>
        </div>

    </div>
</flux:modal>
