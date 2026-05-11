<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Proyectos y Rutas</flux:heading>
            <flux:subheading>Cátalogo de proyectos, rutas y zonas</flux:subheading>
        </div>
        @can('proyectos.crear')
        <flux:button variant="primary" icon="plus" wire:click="openCreate">
            Nuevo Proyecto
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
                        placeholder="Código, nombre, cliente, ciudad, etc..."
                        icon="magnifying-glass"
                        clearable
                    />
                </flux:field>
            </div>

            <div class="sm:w-48">
                <flux:field>
                    <flux:label>Tipo</flux:label>
                    <flux:select variant="listbox" wire:model.live="tipo">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="Proyecto">Proyecto</flux:select.option>
                        <flux:select.option value="Ruta">Ruta</flux:select.option>
                        <flux:select.option value="Zona">Zona</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="sm:w-48">
                <flux:field>
                    <flux:label>Estado Operativo</flux:label>
                    <flux:select variant="listbox" wire:model.live="estadoOperativo">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="Activo">Activo</flux:select.option>
                        <flux:select.option value="Cerrado">Cerrado</flux:select.option>
                        <flux:select.option value="Draft">Draft</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end mt-2">
            <div class="flex-1">
                <flux:field>
                    <flux:label>Centros de Costos</flux:label>
                    <flux:select variant="listbox" wire:model.live="centroCostoId">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach ($centrosCostos as $centroCosto)
                            <flux:select.option value="{{ $centroCosto['id'] }}">
                                {{ $centroCosto['nombre'] ?? $centroCosto['cuenta_contable'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="flex-1">
                <flux:field>
                    <flux:label>Región</flux:label>
                    <flux:select variant="listbox" wire:model.live="region">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach ($regiones as $region)
                            <flux:select.option value="{{ $region }}">
                                {{ $region }}
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
        </div>
    </flux:card>

    <flux:card class="p-2">
        <div class="flex flex-col gap-1 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
            <flux:text size="sm" class="text-zinc-500">
                Total encontrados:
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $proyectos->total() }}
                </span>
            </flux:text>

            <flux:text size="sm" class="text-zinc-500">
                Páginas {{ $proyectos->currentPage() }} de {{ $proyectos->lastPage() }}
            </flux:text>
        </div>

        <flux:table :paginate="$proyectos">
            <flux:table.columns>
                <flux:table.column class="pl-4">Código</flux:table.column>
                <flux:table.column>Nombre / Cliente</flux:table.column>
                <flux:table.column>Ubicación</flux:table.column>
                <flux:table.column>CC / Responsable</flux:table.column>
                <flux:table.column>Presupuesto / Vigencia</flux:table.column>
                <flux:table.column>Estado Operativo</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column class="flex justify-end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($proyectos as $proyecto)
                    <flux:table.row :key="$proyecto->id">
                        <flux:table.cell size="xs" class="pl-4">
                            <div class="flex flex-col gap-3">
                                <span size="xs" class="fonto-mono text-zinc-500 dark:text-zinc-400 px-4"></span>{{ $proyecto->codigo }}</span>
                                <div>
                                    <flux:badge
                                        color="{{
                                            $proyecto->tipo === 'Proyecto' ? 'orange' :
                                            ($proyecto->tipo === 'Ruta' ? 'purple' : 'pink')
                                        }}"
                                        size="sm"
                                        inset="top bottom"
                                    >
                                        {{ $proyecto->tipo }}
                                    </flux:badge>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell variant="strong">
                            <div class="flex flex-col gap-3">
                                <span>{{ $proyecto->nombre }}</span>
                                <span>{{ $proyecto->cliente ?? '-' }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-3">
                                <span>{{ $proyecto->ciudad ?? '-' }}, {{ $proyecto->estado ?? '-' }}</span>
                                <span class="text-sm font-mono text-zinc-500 dark:text-zinc-400">
                                    {{ $proyecto->region }} / {{ $proyecto->pais ?? '-' }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-3">
                                <span>{{ $proyecto->centroCosto?->nombre ?? $empleado->centroCosto?->cuenta_contable ?? '' }}</span>
                                <span class="font-mono text-zinc-500 dark:text-zinc-400">
                                    {{ $proyecto->responsable?->nombre ?? 'Sin responsable' }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-3">
                                <span>
                                    {{ Number::currency($proyecto->presupuesto_total ?? 0.00, in: 'MXN') }}
                                </span>
                                <span class="font-mono text-zinc-500 dark:text-zinc-400">
                                    {{ $proyecto->fecha_inicio?->format('Y-m-d') ?? '-' }} a {{ $proyecto->fecha_fin?->format('Y-m-d') ?? '-' }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="{{ $proyecto->badge_color }}" size="sm" inset="top bottom">
                                {{ $proyecto->estado_operativo }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($proyecto->estatus)
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
                                    wire:click="openDetail({{ $proyecto->id }})"
                                    title="Ver"
                                />

                                @can('proyectos.editar')
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil"
                                    insert="top bottom"
                                    wire:click="openEdit({{ $proyecto->id }})"
                                    title="Editar"
                                />
                                @endcan

                                @if ($proyecto->estatus)
                                    @can('proyectos.eliminar')
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            insert="top bottom"
                                            wire:click="openDelete({{ $proyecto->id }})"
                                            title="Deshabilitar"
                                        />
                                    @endcan
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="inbox" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                <flux:text class="text-zinc-400">No se encontraron proyectos</flux:text>
                                @if ($search || $estatus !== '' || $tipo !== '' || $region !== '' || $estadoOperativo !== '' || $centroCostoId)
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

    @livewire('proyectos.form-modal')
    @livewire('proyectos.detail-modal')

    <flux:modal name="proyecto-delete" class="w-full max-w-sm">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon
                        name="exclamation-triangle"
                        class="size-5 text-red-600 dark:text-red-400"
                    />
                </div>
                <div>
                    <flux:heading size="lg">Deshabilitar proyecto</flux:heading>
                    <flux:subheading class="mt-1">
                        ¿Estás seguro deshabilitar el proyecto <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deletingNombre }}</span>?
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
