<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Referencia Contable</flux:heading>
            <flux:subheading>Gestiona las referencias contables de la organización.</flux:subheading>
        </div>
        @can('centros_costos.crear')
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                Nueva Referencia Contable
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
                        placeholder="Código o cuenta contable, centro de costo..."
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

        </div>
    </flux:card>

    <flux:card class="p-2">

        <div class="flex flex-col gap-1 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
            <flux:text size="sm" class="text-zinc-500">
                Total encontrados:
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $centroCostos->total() }}</span>
            </flux:text>
            <flux:text size="sm" class="text-zinc-500">
                Página {{ $centroCostos->currentPage() }} de {{ $centroCostos->lastPage() }}
            </flux:text>
        </div>

        <flux:table :paginate="$centroCostos">
            <flux:table.columns>
                <flux:table.column class="pl-4">Código</flux:table.column>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column>Cuenta Contable</flux:table.column>
                <flux:table.column>Estatus</flux:table.column>
                <flux:table.column class="flex justify-end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($centroCostos as $centroCosto)
                    <flux:table.row :key="$centroCosto->id">

                        <flux:table.cell size="xs" class="pl-4 font-mono text-zinc-500 dark:text-zinc-400 px-4">
                            {{ $centroCosto->codigo }}
                        </flux:table.cell>

                        <flux:table.cell variant="strong">
                            {{ $centroCosto->nombre }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $centroCosto->cuenta_contable }}
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($centroCosto->estatus)
                                <flux:badge color="green" size="sm" inset="top bottom">Activo</flux:badge>
                            @else
                                <flux:badge color="red" size="sm" inset="top bottom">Inactivo</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('centros_costos.editar')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil"
                                        inset="top bottom"
                                        wire:click="openEdit({{ $centroCosto->id }})"
                                        title="Editar"
                                    />
                                @endcan

                                @if ($centroCosto->estatus)
                                    @can('centros_costos.eliminar')
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            inset="top bottom"
                                            wire:click="openDelete({{ $centroCosto->id }})"
                                            title="Deshabilitar"
                                        />
                                    @endcan
                                @endif
                            </div>
                        </flux:table.cell>

                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="inbox" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                <flux:text class="text-zinc-400">No se encontraron referencias contables</flux:text>
                                @if ($search || $estatus !== '')
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

    <flux:modal name="centro-costo-form" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Editar referencia contable' : 'Nueva referencia contable' }}
                </flux:heading>

                <flux:subheading>
                    {{ $editingId
                        ? 'Modifica los datos de la referencia contable.'
                        : 'Captura al menos un campo para registrar un nueva referencia contable.' }}
                </flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>
                        Centro de costo
                    </flux:label>

                    <flux:input
                        wire:model.defer="nombre"
                        placeholder="Ej. Oficina Central"
                    />

                    <flux:error name="nombre" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        Cuenta contable
                    </flux:label>

                    <flux:input
                        wire:model.defer="cuenta_contable"
                        placeholder="Ej. 102-01-001"
                    />

                    <flux:error name="cuenta_contable" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">
                        Cancelar
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    variant="primary"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">
                        {{ $editingId ? 'Guardar cambios' : 'Crear' }}
                    </span>

                    <span wire:loading wire:target="save">
                        Guardando...
                    </span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="centro-costo-delete" class="w-full max-w-sm">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon
                        name="exclamation-triangle"
                        class="size-5 text-red-600 dark:text-red-400"
                    />
                </div>
                <div>
                    <flux:heading size="lg">Deshabilitar referencia contable</flux:heading>
                    <flux:subheading class="mt-1">
                        ¿Estás seguro de deshabilitar <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deletingNombre }}</span>? Esta acción no se puede deshacer.
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
