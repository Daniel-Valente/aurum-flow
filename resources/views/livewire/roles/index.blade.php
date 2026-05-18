<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Roles y Permisos</flux:heading>
            <flux:subheading>Gestiona los roles del sistema y sus permisos de acceso.</flux:subheading>
        </div>
        @can('roles.crear')
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                Nuevo Rol
            </flux:button>
        @endcan
    </div>

    <flux:card class="p-0 overflow-hidden">
        <div class="flex flex-col gap-1 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
            <flux:text size="sm" class="text-zinc-500">
                Total de roles:
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $roles->count() }}</span>
            </flux:text>
            <flux:text size="xs" class="text-zinc-400">
                Los roles de sistema no se pueden eliminar ni renombrar.
            </flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>
                    <span class="pl-4">Nombre del Rol</span>
                </flux:table.column>
                <flux:table.column>Permisos</flux:table.column>
                <flux:table.column>Usuarios</flux:table.column>
                <flux:table.column>Tipo</flux:table.column>
                <flux:table.column>
                    <span class="flex items-end justify-end pr-4">Acciones</span>
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($roles as $role)
                    <flux:table.row :key="$role->id">

                        <flux:table.cell>
                            <div class="flex items-center gap-3 pl-4">
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

                                <div class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $styles['bg'] }}">
                                    <flux:icon
                                        name="shield-check"
                                        class="size-4 {{ $styles['text'] }}"
                                    />
                                </div>
                                <div>
                                    <p class="font-semibold text-zinc-800 dark:text-zinc-100 capitalize">
                                        {{ $role->name }}
                                    </p>
                                    <p class="text-xs font-mono text-zinc-400">guard: web</p>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @php
                                $dominiosAsignados = $role->permissions
                                    ->map(fn($p) => explode('.', str_replace('-', '.', $p->name))[0])
                                    ->unique()
                                    ->values();
                            @endphp

                            @if ($role->permissions_count === 0)
                                <flux:badge color="zinc" size="sm">Sin permisos</flux:badge>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    <flux:badge color="blue" size="sm">
                                        {{ $role->permissions_count }} permiso{{ $role->permissions_count !== 1 ? 's' : '' }}
                                    </flux:badge>
                                    @if ($dominiosAsignados->count() <= 3)
                                        @foreach ($dominiosAsignados->take(3) as $dom)
                                            <flux:badge color="zinc" size="sm" class="capitalize">{{ $dom }}</flux:badge>
                                        @endforeach
                                    @else
                                        @foreach ($dominiosAsignados->take(2) as $dom)
                                            <flux:badge color="zinc" size="sm" class="capitalize">{{ $dom }}</flux:badge>
                                        @endforeach
                                        <flux:badge color="zinc" size="sm">
                                            +{{ $dominiosAsignados->count() - 2 }} más
                                        </flux:badge>
                                    @endif
                                </div>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:icon name="users" class="size-4 text-zinc-400" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ $role->users_count }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if (in_array($role->name, ['admin', 'manager', 'finanzas', 'operativo']))
                                <flux:badge color="amber" size="sm" icon="lock-closed">Sistema</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm" icon="user">Personalizado</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1 pr-4">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    inset="top bottom"
                                    wire:click="openDetail({{ $role->id }})"
                                    title="Ver detalle"
                                />
                                @can('roles.permisos')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="key"
                                        inset="top bottom"
                                        wire:click="openPermisos({{ $role->id }})"
                                        title="Gestionar permisos"
                                    />
                                @endcan
                                @unless(in_array($role->name, ['admin', 'manager', 'finanzas', 'operativo']))
                                    @can('roles.editar')
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="pencil"
                                            inset="top bottom"
                                            wire:click="openEdit({{ $role->id }})"
                                            title="Editar nombre"
                                        />
                                    @endcan
                                    @can('roles.eliminar')
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            inset="top bottom"
                                            wire:click="openDelete({{ $role->id }})"
                                            title="Eliminar"
                                        />
                                    @endcan
                                @endunless
                            </div>
                        </flux:table.cell>

                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="shield-exclamation" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                <flux:text class="text-zinc-400">No hay roles registrados</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    @livewire('roles.form-modal')
    @livewire('roles.permission-modal')
    @livewire('roles.detail-modal')

    <flux:modal name="rol-delete" class="w-full max-w-sm">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon name="exclamation-triangle" class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <flux:heading size="lg">Eliminar rol</flux:heading>
                    <flux:subheading class="mt-1">
                        ¿Estás seguro de eliminar el rol
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100 capitalize">
                            {{ $deletingNombre }}
                        </span>?
                        Se revocarán todos sus permisos.
                    </flux:subheading>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button
                    variant="danger"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                >
                    <span wire:loading.remove wire:target="delete">Eliminar</span>
                    <span wire:loading wire:target="delete">Eliminando…</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>
