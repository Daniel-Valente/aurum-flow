<flux:modal name="rol-detail" flyout variant="floating" class="md:w-2xl">
    @if ($role)
        <div class="flex flex-col gap-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    @php
                        $roleStyles = [
                            'admin' => [
                                'bg' => 'bg-red-100 dark:bg-red-900/30',
                                'text' => 'text-red-600 dark:text-red-400',
                            ],
                            'finanzas' => [
                                'bg' => 'bg-blue-100 dark:bg-blue-900/30',
                                'text' => 'text-blue-600 dark:text-blue-400',
                            ],
                            'manager' => [
                                'bg' => 'bg-amber-100 dark:bg-amber-900/30',
                                'text' => 'text-amber-600 dark:text-amber-400',
                            ],
                            'operativo' => [
                                'bg' => 'bg-green-100 dark:bg-green-900/30',
                                'text' => 'text-green-600 dark:text-green-400',
                            ],
                        ];

                        $styles = $roleStyles[$role->name] ?? [
                            'bg' => 'bg-zinc-100 dark:bg-zinc-800',
                            'text' => 'text-zinc-500 dark:text-zinc-400',
                        ];
                    @endphp

                    <div class="flex size-14 items-center justify-center rounded-xl {{ $styles['bg'] }}">
                        <flux:icon
                            name="shield-check"
                            class="size-7 {{ $styles['text'] }}"
                        />
                    </div>

                    <div>
                        <flux:heading size="lg" class="capitalize">
                            {{ $role->name }}
                        </flux:heading>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs font-mono text-zinc-400">guard: web</span>
                        </div>
                    </div>
                </div>

                @if ($esSistema)
                    <flux:badge color="amber" icon="lock-closed">Sistema</flux:badge>
                @else
                    <flux:badge color="zinc" icon="user">Personalizado</flux:badge>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-4 py-3">
                    <span class="text-[10px] uppercase tracking-wide text-zinc-400">Permisos asignados</span>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-bold text-zinc-800 dark:text-zinc-100">
                            {{ $totalPermisos }}
                        </span>
                        @if ($totalPermisos > 0)
                            <flux:badge color="blue" size="sm">activos</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">ninguno</flux:badge>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-4 py-3">
                    <span class="text-[10px] uppercase tracking-wide text-zinc-400">Usuarios con este rol</span>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-bold text-zinc-800 dark:text-zinc-100">
                            {{ $totalUsuarios }}
                        </span>
                        <flux:icon name="users" class="size-5 text-zinc-400 mb-1" />
                    </div>
                </div>
            </div>

            <flux:separator />

            @if (!empty($permisosGrupo))
                <div>
                    <flux:subheading class="mb-4 text-xs uppercase tracking-widest text-zinc-400">
                        Permisos por módulo
                    </flux:subheading>

                    <div class="space-y-3">
                        @foreach ($permisosGrupo as $dominio => $permisos)
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                <div class="flex items-center justify-between px-3 py-2 bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                                    <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                        {{ $dominio }}
                                    </span>
                                    <flux:badge color="blue" size="sm">
                                        {{ count($permisos) }}
                                    </flux:badge>
                                </div>

                                <div class="flex flex-wrap gap-2 px-3 py-3">
                                    @foreach ($permisos as $permiso)
                                        <div class="inline-flex items-center gap-1.5 rounded-md
                                            bg-blue-50 dark:bg-blue-900/20
                                            border border-blue-100 dark:border-blue-800
                                            px-2.5 py-1">
                                            <div class="size-1.5 rounded-full bg-blue-500"></div>
                                            <span class="text-xs font-medium text-blue-700 dark:text-blue-300">
                                                {{ $permiso['label'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 py-10">
                    <flux:icon name="key" class="size-8 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="text-zinc-400">Este rol no tiene permisos asignados</flux:text>
                </div>
            @endif
        </div>
    @endif
</flux:modal>
