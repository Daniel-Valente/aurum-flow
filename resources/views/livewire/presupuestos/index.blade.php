<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Presupuestos</flux:heading>
            <flux:subheading>Gestiona los presupuestos de la organización.</flux:subheading>
        </div>
        @can('presupuestos.crear')
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                Nuevo Presupuesto
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
                        placeholder="Código, nombre o descripción..."
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
                        <flux:select.option value="empresa">Empresa</flux:select.option>
                        <flux:select.option value="area">Área</flux:select.option>
                        <flux:select.option value="empleado">Empleado</flux:select.option>
                        <flux:select.option value="proyecto">Proyecto</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="sm:w-48">
                <flux:field>
                    <flux:label>Estatus</flux:label>
                    <flux:select variant="listbox" wire:model.live="estatus">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="borrador">Borrador</flux:select.option>
                        <flux:select.option value="activo">Activo</flux:select.option>
                        <flux:select.option value="agotado">Agotado</flux:select.option>
                        <flux:select.option value="vencido">Vencido</flux:select.option>
                        <flux:select.option value="cancelado">Cancelado</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="sm:w-48">
                <flux:field>
                    <flux:label>Período</flux:label>
                    <flux:select variant="listbox" wire:model.live="periodo">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="diario">Diario</flux:select.option>
                        <flux:select.option value="semanal">Semanal</flux:select.option>
                        <flux:select.option value="quincenal">Quincenal</flux:select.option>
                        <flux:select.option value="mensual">Mensual</flux:select.option>
                        <flux:select.option value="trimestral">Trimestral</flux:select.option>
                        <flux:select.option value="semestral">Semestral</flux:select.option>
                        <flux:select.option value="anual">Anual</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

        </div>

        @if($search || $tipo || $estatus || $periodo || $areaId || $empleadoId)
            <div class="mt-3 flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800 pt-3">
                <flux:text size="sm" class="text-zinc-500">
                    Filtros activos
                </flux:text>
                <flux:button size="sm" variant="ghost" wire:click="clearFilters">
                    Limpiar filtros
                </flux:button>
            </div>
        @endif
    </flux:card>

    <flux:card class="p-2">

        <div class="flex flex-col gap-1 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
            <flux:text size="sm" class="text-zinc-500">
                Total encontrados:
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $presupuestos->total() }}</span>
            </flux:text>
            <flux:text size="sm" class="text-zinc-500">
                Página {{ $presupuestos->currentPage() }} de {{ $presupuestos->lastPage() }}
            </flux:text>
        </div>

        <flux:table :paginate="$presupuestos">
            <flux:table.columns>
                <flux:table.column class="pl-4">Código / Nombre</flux:table.column>
                <flux:table.column>Tipo / Entidad</flux:table.column>
                <flux:table.column>Presupuesto</flux:table.column>
                <flux:table.column>Consumo</flux:table.column>
                <flux:table.column>Vigencia</flux:table.column>
                <flux:table.column>Estatus</flux:table.column>
                <flux:table.column class="flex justify-end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($presupuestos as $presupuesto)
                    <flux:table.row :key="$presupuesto->id">

                        <flux:table.cell class="pl-4">
                            <div class="flex flex-col gap-1">
                                <span class="font-mono text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ $presupuesto->codigo }}
                                </span>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $presupuesto->nombre }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-2">
                                <flux:badge :color="match($presupuesto->tipo) {
                                    'empresa' => 'blue',
                                    'area' => 'purple',
                                    'empleado' => 'green',
                                    'proyecto' => 'orange',
                                    default => 'zinc'
                                }" size="sm">
                                    {{ ucfirst($presupuesto->tipo) }}
                                </flux:badge>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    @if($presupuesto->tipo === 'empresa' && $presupuesto->empresa)
                                        {{ $presupuesto->empresa->nombre }}
                                    @elseif($presupuesto->tipo === 'area' && $presupuesto->area)
                                        {{ $presupuesto->area->nombre }}
                                    @elseif($presupuesto->tipo === 'empleado' && $presupuesto->empleado)
                                        {{ $presupuesto->empleado->nombre_completo }}
                                    @elseif($presupuesto->tipo === 'proyecto' && $presupuesto->proyecto)
                                        {{ $presupuesto->proyecto->nombre }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ Number::currency($presupuesto->monto_total, in: 'MXN') }}
                                </span>
                                <span class="text-xs text-zinc-500">
                                    Disponible: {{ Number::currency($presupuesto->monto_disponible, in: 'MXN') }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-zinc-500">Gastado</span>
                                    <span class="text-xs font-semibold">
                                        {{ Number::currency($presupuesto->monto_gastado, in: 'MXN') }}
                                    </span>
                                </div>
                                <div class="h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                    @php
                                        $pct = $presupuesto->porcentaje_consumido;
                                    @endphp
                                    <div
                                        class="h-full transition-all {{ $pct >= 95 ? 'bg-rose-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-blue-500') }}"
                                        style="width: {{ min($pct, 100) }}%"
                                    ></div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-zinc-400">{{ $pct }}%</span>
                                    @if($presupuesto->monto_comprometido > 0)
                                        <span class="text-xs text-amber-600">
                                            Comprometido: {{ Number::currency($presupuesto->monto_comprometido, in: 'MXN') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs text-zinc-500">
                                    {{ $presupuesto->fecha_inicio->format('d/m/Y') }}
                                </span>
                                <span class="text-xs text-zinc-400">al</span>
                                <span class="text-xs text-zinc-500">
                                    {{ $presupuesto->fecha_fin->format('d/m/Y') }}
                                </span>
                                @if($presupuesto->dias_restantes !== null)
                                    <span class="text-xs {{ $presupuesto->dias_restantes <= 7 ? 'text-amber-600' : 'text-zinc-400' }}">
                                        {{ $presupuesto->dias_restantes }} días
                                    </span>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-2">
                                <flux:badge :color="match($presupuesto->estatus) {
                                    'activo' => 'green',
                                    'borrador' => 'zinc',
                                    'agotado' => 'red',
                                    'vencido' => 'orange',
                                    'cancelado' => 'red',
                                    default => 'zinc'
                                }" size="sm">
                                    {{ ucfirst($presupuesto->estatus) }}
                                </flux:badge>

                                @if($presupuesto->alertas_count > 0)
                                    <div class="flex items-center gap-1 text-xs text-rose-600">
                                        <flux:icon.exclamation-triangle class="size-3" />
                                        <span>{{ $presupuesto->alertas_count }} alerta(s)</span>
                                    </div>
                                @endif

                                @if($presupuesto->renovable)
                                    <div class="flex items-center gap-1 text-xs text-blue-600">
                                        <flux:icon.arrow-path class="size-3" />
                                        <span>Renovable</span>
                                    </div>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <div class="flex items-center justify-end gap-1">

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    inset="top bottom"
                                    wire:click="openDetail({{ $presupuesto->id }})"
                                    title="Ver detalle"
                                />

                                @if($presupuesto->estatus === 'borrador')
                                    @can('presupuestos.aprobar')
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="check-circle"
                                            inset="top bottom"
                                            wire:click="aprobar({{ $presupuesto->id }})"
                                            title="Aprobar"
                                        />
                                    @endcan
                                @endif

                                @if(in_array($presupuesto->estatus, ['borrador', 'activo']))
                                    @can('presupuestos.editar')
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="pencil"
                                            inset="top bottom"
                                            wire:click="openEdit({{ $presupuesto->id }})"
                                            title="Editar"
                                        />
                                    @endcan
                                @endif

                                @if($presupuesto->estatus === 'activo')
                                    @can('presupuestos.transferir')
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="arrows-right-left"
                                            inset="top bottom"
                                            wire:click="openTransferencia({{ $presupuesto->id }})"
                                            title="Transferir"
                                        />
                                    @endcan

                                    @can('presupuestos.cancelar')
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="x-circle"
                                            inset="top bottom"
                                            wire:click="openCancel({{ $presupuesto->id }})"
                                            title="Cancelar"
                                        />
                                    @endcan
                                @endif

                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon.chart-bar class="size-8 text-zinc-300 dark:text-zinc-600" />
                                <flux:text class="text-zinc-400">No se encontraron presupuestos</flux:text>
                                @if ($search || $tipo || $estatus || $periodo)
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

    @livewire('presupuestos.form-modal')
    @livewire('presupuestos.detail-modal')
    @livewire('presupuestos.transferencia-modal')

    <flux:modal name="presupuesto-cancel" class="w-full max-w-md">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div class="flex-1">
                    <flux:heading size="lg">Cancelar presupuesto</flux:heading>
                    <flux:subheading class="mt-1">
                        ¿Estás seguro de cancelar el presupuesto <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $cancelingNombre }}</span>?
                    </flux:subheading>
                </div>
            </div>

            <flux:field>
                <flux:label badge="Requerido">Motivo de cancelación</flux:label>
                <flux:textarea
                    wire:model="motivoCancelacion"
                    placeholder="Explica el motivo de la cancelación..."
                    rows="3"
                />
                <flux:error name="motivoCancelacion" />
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button
                    variant="danger"
                    wire:click="cancel"
                    wire:loading.attr="disabled"
                    wire:target="cancel"
                >
                    <span wire:loading.remove wire:target="cancel">Confirmar cancelación</span>
                    <span wire:loading wire:target="cancel">Cancelando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
