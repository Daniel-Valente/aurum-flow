<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Directorio de Empleados</flux:heading>
            <flux:subheading>Gestiona los empleados de la organización.</flux:subheading>
        </div>
        @can('empleados.crear')
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                Nuevo Empleado
            </flux:button>
        @endcan
    </div>

    <flux:card>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">

            <div class="flex-1">
                <flux:field>
                    <flux:label>Búsqueda</flux:label>
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Nombre, email, RFC o nómina..."
                        icon="magnifying-glass"
                        clearable
                    />
                </flux:field>
            </div>

            <div class="sm:w-48">
                <flux:field>
                    <flux:label>Estatus</flux:label>
                    <flux:select variant="listbox" wire:model.live="estatus">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="1">Activo</flux:select.option>
                        <flux:select.option value="0">Inactivo</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="sm:w-48">
                <flux:field>
                    <flux:label>Rol</flux:label>
                    <flux:select variant="listbox" wire:model.live="rol">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach ($roles as $role)
                            <flux:select.option value="{{ $role['name'] }}">
                                {{ $role['name'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="sm:w-48">
                <flux:field>
                    <flux:label>Centro de Costos</flux:label>
                    <flux:select variant="listbox" wire:model.live="centroCostoId">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach ($centrosCostos as $centroCosto)
                            <flux:select.option value="{{ $centroCosto['id'] }}">{{ $centroCosto['nombre'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

        </div>
    </flux:card>

    <flux:card class="p-2">

        <div class="flex flex-col gap-1 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
            <flux:text size="sm" class="text-zinc-500">
                Total encontrados:
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $empleados->total() }}</span>
            </flux:text>
            <flux:text size="sm" class="text-zinc-500">
                Página {{ $empleados->currentPage() }} de {{ $empleados->lastPage() }}
            </flux:text>
        </div>

        <flux:table :paginate="$empleados">
            <flux:table.columns>
                <flux:table.column class="pl-4">Nombre / Puesto</flux:table.column>
                <flux:table.column>Contacto</flux:table.column>
                <flux:table.column>Rol / CC</flux:table.column>
                <flux:table.column>Nómina / RRHH </flux:table.column>
                <flux:table.column>Estatus</flux:table.column>
                <flux:table.column class="flex justify-end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($empleados as $empleado)
                    <flux:table.row :key="$empleado->user_id">
                        <flux:table.cell class="pl-4">
                            <div class="flex flex-col gap-3">
                                <span class="font-bold">
                                    {{ $empleado->nombre_completo }}
                                </span>
                                <span size="xs" class="font-mono text-zinc-500 dark:text-zinc-400 px-4">
                                    {{ $empleado->puesto }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-3">
                                <span>
                                    {{ $empleado->user?->email }}
                                </span>
                                <span size="xs" class="font-mono text-zinc-500 dark:text-zinc-400 px-4">
                                    {{ $empleado->rfc }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-3">
                                <span>
                                    {{ $empleado->role?->name }}
                                </span>
                                <span size="xs" class="font-mono text-zinc-500 dark:text-zinc-400 px-4">
                                    {{ $empleado->centroCosto?->nombre }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-3">
                                <span>
                                    {{ $empleado->numero_nomina }}
                                </span>
                                <span size="xs" class="font-mono text-zinc-500 dark:text-zinc-400 px-4">
                                    {{ $empleado->banco_nomina }} / Ingreso: {{ $empleado->fecha_ingreso?->format('Y-m-d') }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($empleado->estatus)
                                <flux:badge color="green" size="sm" inset="top bottom">Activo</flux:badge>
                            @else
                                <flux:badge color="red" size="sm" inset="top bottom">Inactivo</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="eye"
                                        inset="top bottom"
                                        wire:click="openDetail({{ $empleado->id }})"
                                        title="Ver"
                                    />

                                @can('empleados.editar')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil"
                                        inset="top bottom"
                                        wire:click="openEdit({{ $empleado->id }})"
                                        title="Editar"
                                    />
                                @endcan

                                @can('empleados.eliminar')
                                    @php
                                        $rolEmpleado = $empleado->role?->name;
                                        $puedeEliminar = auth()->user()->hasRole('admin') ||
                                            (auth()->user()->hasRole('gerente') && $rolEmpleado === 'operativo');
                                    @endphp

                                    @if ($puedeEliminar)
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            inset="top bottom"
                                            wire:click="openDelete({{ $empleado->id }})"
                                            title="Desactivar"
                                        />
                                    @endif
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="inbox" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                <flux:text class="text-zinc-400">No se encontraron empleados</flux:text>
                                @if ($search || $estatus !== '' || $areaId || $centroCostoId || $rol !== '')
                                    <flux:button size="sm" variant="ghost" wire:click="clearFilters">
                                        Limpiar filtros
                                    </flux:button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    @livewire('empleados.detail-modal')
    @livewire('empleados.form-modal')

    <flux:modal name="empleado-delete" class="w-full max-w-sm">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon
                        name="exclamation-triangle"
                        class="size-5 text-red-600 dark:text-red-400"
                    />
                </div>
                <div>
                    <flux:heading size="lg">Deshabilitar empleado</flux:heading>
                    <flux:subheading class="mt-1">
                        ¿Estás seguro deshabilidar la cuenta al empleado <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deletingNombre }}</span>?
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
                    <span wire:loading.remove wire:target="delete">Deshabilitar</span>
                    <span wire:loading wire:target="delete">Deshabilitando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
