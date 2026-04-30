<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Conceptos de Viáticos</flux:heading>
            <flux:subheading>Cátalogo de conceptos para viáticos</flux:subheading>
        </div>
        @can('conceptos.crear')
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                Nuevo Concepto
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
                        placeholder="Código o nombre"
                        icon="magnifying-glass"
                        clearable
                    />
                </flux:field>
            </div>

            <div class="sm:w-48">
                <flux:field>
                    <flux:label>Categoría</flux:label>
                    <flux:select wire.model.live="categoria">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach ($categorias as $categoria)
                            <flux:select.option value="{{ $categoria }}">
                                {{ $categoria }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="sm:w-48">
                <flux:field>
                    <flux:label>Tipo</flux:label>
                    <flux:select wire.model.live="tipo">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="Diario">Diario</flux:select.option>
                        <flux:select.option value="Evento">Evento</flux:select.option>
                        <flux:select.option value="Viaje">Viaje</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end mt-2">


            <div class="flex-1">
                <flux:field>
                    <flux:label>Rol</flux:label>
                    <flux:select wire.model.live="rol">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach ($roles as $role)
                            <flux:select.option value="{{ $role['id'] }}">
                                {{ $role['name'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="flex-1">
                <flux:field>
                    <flux:label>Estatus</flux:label>
                    <flux:select variant="listbox" wire:model.live="estatus">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="1">Activo</flux:select.option>
                        <flux:select.option value="0">Inactivo</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="flex-1">
                <flux:field>
                    <flux:label>Vigencia</flux:label>
                    <flux:select variant="listbox" wire:model.live="estatus">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="vigentes">Vigentes</flux:select.option>
                        <flux:select.option value="no_vigentes">No Vigentes</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>
        </div>
    </flux:card>

    <flux:card class="p-2">
        <div class="flex flex-col gap-1 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
            <flux:text size="sm" class="text-zinc-500">
                Total encontrados:
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $conceptos->total() }}
                </span>
            </flux:text>
            <flux:text size="sm" class="text-zinc-500">
                Páginas {{ $conceptos->currentPage() }} de {{ $conceptos->lastPage() }}
            </flux:text>
        </div>

        <flux:table :paginate="$conceptos">
            <flux:table.columns>
                <flux:table.column class="pl-4">Código</flux:table.column>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column>Categoría</flux:table.column>
                <flux:table.column>Tipo</flux:table.column>
                <flux:table.column>Roles</flux:table.column>
                <flux:table.column>Vigencia</flux:table.column>
                <flux:table.column>Reglas</flux:table.column>
                <flux:table.column>Estatus</flux:table.column>
                <flux:table.column class="flex justify-end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($conceptos as $concepto)
                    <flux:table.row :key="$concepto->id">
                        <flux:table.cell size="xs" class="pl-4 font-mono text-zinc-500 dark:text-zinc-400 px-4">
                            {{ $concepto->codigo }}
                        </flux:table.cell>

                        <flux:table.cell variant="strong">
                            {{ $concepto->nombre }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $concepto->categoria }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $concepto->tipo_aplicacion }}
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($concepto->roles->isEmpty())
                                <flux:badge size="sm" color="zinc" inset="top bottom">-</flux:badge>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($concepto->roles as $role)
                                        <flux:badge size="sm" color="blue" inset="top bottom">
                                            {{ $role->name }}
                                        </flux:badge>
                                    @endforeach
                                </div>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($concepto->vigencia_desde && $concepto->vigencia_hasta)
                                Desde: {{ $concepto->vigencia_desde?->format('Y-m-d') }} - Hasta: {{ $concepto->vigencia_hasta?->format('Y-m-d') }}
                            @elseif ($concepto->vigencia_desde && !$concepto->vigencia_hasta)
                                Desde: {{ $concepto->vigencia_desde?->format('Y-m-d') }}
                            @elseif ($concepto->vigencia_hasta && !$concepto->vigencia_hasta)
                                Hasta: {{ $concepto->vigencia_hasta?->format('Y-m-d') }}
                            @else
                                -
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($concepto->requiere_factura)
                                <flux:badge size="xs" color="blue" inset="top bottom">
                                    Factura
                                </flux:badge>
                            @endif

                            @if ($concepto->requiere_comprobante)
                                <flux:badge class="ml-0.5" size="xs" color="orange" inset="top bottom">
                                    Comp.
                                </flux:badge>
                            @endif

                            @if ($concepto->requiere_uuid)
                                <flux:badge class="ml-0.5" size="xs" color="green" inset="top bottom">
                                    UUID
                                </flux:badge>
                            @endif

                            @if ($concepto->permite_sin_factura)
                                <flux:badge class="ml-0.5" size="xs" color="red" inset="top bottom">
                                    Sin Fact.
                                </flux:badge>
                            @endif

                            @if ($concepto->aplica_iva)
                                <flux:badge class="ml-0.5" size="xs" color="purple" inset="top bottom">
                                    IVA
                                </flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($concepto->estatus)
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
                                    insert="top bottom"
                                    wire:click="openDetail({{ $concepto->id }})"
                                    title="Ver"
                                />

                                @can('conceptos.editar')
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil"
                                    insert="top bottom"
                                    wire:click="openEdit({{ $concepto->id }})"
                                    title="Editar"
                                />
                                @endcan

                                @can('conceptos.eliminar')
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    insert="top bottom"
                                    wire:click=""
                                    title="Deshabilitar"
                                />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="inbox" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                <flux:text class="text-zinc-400">No se encontraron conceptos</flux:text>
                                @if ($search || $estatus !== '' || $rolId || $tipo !== '' || $categoria !== '' || $vigencia !== '')
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

    @livewire('conceptos.form-modal')
    @livewire('conceptos.detail-modal')

    <flux:modal name="concepto-delete" class="w-full max-w-sm">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon
                        name="exclamation-triangle"
                        class="size-5 text-red-600 dark:text-red-400"
                    />
                </div>
                <div>
                    <flux:heading size="lg">Deshabilitar concepto</flux:heading>
                    <flux:subheading class="mt-1">
                        ¿Estás seguro de deshabilitar el concepto <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deletingNombre }}</span>? Esta acción no se puede deshacer.
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
