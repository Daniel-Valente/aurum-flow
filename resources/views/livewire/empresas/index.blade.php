<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Empresas</flux:heading>
            <flux:subheading>Gestiona las empresas de la organización/corporativo.</flux:subheading>
        </div>
        @can('empresas.crear')
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                Nueva Empresa
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
                        placeholder="Nombre, nombre comercial, ciudad o rfc..."
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
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $empresas->total() }}</span>
            </flux:text>
            <flux:text size="sm" class="text-zinc-500">
                Página {{ $empresas->currentPage() }} de {{ $empresas->lastPage() }}
            </flux:text>
        </div>

        <flux:table :paginate="$empresas">
            <flux:table.columns>
                <flux:table.column>
                    <span class="pl-4">Código</span>
                </flux:table.column>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column>RFC</flux:table.column>
                <flux:table.column>Ubicación</flux:table.column>
                <flux:table.column>Estatus</flux:table.column>
                <flux:table.column>
                    <span class="flex items-end justify-end pr-4">Acciones</span>
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($empresas as $empresa)
                    <flux:table.row :key="$empresa->id">
                        <flux:table.cell>
                            <span size="xs" class="pl-4 font-mono text-zinc-500 dark:text-zinc-400 px-4">{{ $empresa->codigo }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col gap-3">
                                <span class="font-bold">
                                    {{ $empresa->nombre }}
                                </span>
                                <span size="xs" class="font-mono text-zinc-500 dark:text-zinc-400 px-4">
                                    {{ $empresa->nombre_comercial }}
                                </span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $empresa->rfc }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col gap-3">
                                <span>{{ $empresa->ciudad ?? '-' }}, {{ $empresa->estado ?? '-' }}</span>
                                <span class="text-sm font-mono text-zinc-500 dark:text-zinc-400">
                                    {{ $empresa->codigo_postal }} / {{ $empresa->pais ?? '-' }}
                                </span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($empresa->activo)
                                <flux:badge color="green" size="sm" inset="top bottom">Activo</flux:badge>
                            @else
                                <flux:badge color="red" size="sm" inset="top bottom">Inactivo</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-right flex items-center justify-end gap-1 pr-4">
                                @can('empresas.ver')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="eye"
                                        inset="top bottom"
                                        wire:click="openDetail({{ $empresa->id }})"
                                        title="Ver detalle"
                                    />
                                @endcan
                                @can('empresas.configurar')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="cog"
                                        wire:click="openConfiguracion({{ $empresa->id }})"
                                        title="Configurar"
                                    />
                                @endcan
                                @can('empresas.editar')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil"
                                        inset="top bottom"
                                        wire:click="openEdit({{ $empresa->id }})"
                                        title="Editar"
                                    />
                                @endcan
                                @if ($empresa->activo)
                                    @can('empresas.eliminar')
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            inset="top bottom"
                                            wire:click="openDelete({{ $empresa->id }})"
                                            title="Deshabilitar"
                                        />
                                    @endcan
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="inbox" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                <flux:text class="text-zinc-400">No se encontraron empresas</flux:text>
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

    @livewire('empresas.form-modal')
    @livewire('empresas.detail-modal')
    @livewire('empresas.configuracion-modal')

    <flux:modal name="empresa-delete" class="w-full max-w-sm">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon
                        name="exclamation-triangle"
                        class="size-5 text-red-600 dark:text-red-400"
                    />
                </div>
                <div>
                    <flux:heading size="lg">Deshabilitar empresa</flux:heading>
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
