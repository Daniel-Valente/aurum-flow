<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Comprobar Gastos</flux:heading>
            <flux:subheading>Gestiona tus comprobaciones mediante solicitud</flux:subheading>
        </div>

        @can('comprobacion.manual')
            <flux:button variant="primary" icon="plus" wire:click="">
                Comprobación Manual
            </flux:button>
        @endcan
    </div>

    @can('comprobacion.manual')
        <flux:tabs wire:model.live="tab">
            <flux:tab name="solicitudes" icon="document-check">
                Solicitudes
            </flux:tab>
            <flux:tab name="excepciones" icon="exclamation-triangle">
                Manual
            </flux:tab>
        </flux:tabs>
    @endcan

    @if ($tab === 'solicitudes')
        <flux:card>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">

                <div class="flex-1">
                    <flux:field>
                        <flux:label>Búsqueda</flux:label>
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Folio, proyecto, motivo..."
                            icon="magnifying-glass"
                            clearable
                        />
                    </flux:field>
                </div>

                <div class="sm:w-44">
                    <flux:field>
                        <flux:label>Cumplimiento</flux:label>
                        <flux:select variant="listbox" wire:model.live="cumplimiento">
                            <flux:select.option value="">Todos</flux:select.option>
                            <flux:select.option value="ok">Ok</flux:select.option>
                            <flux:select.option value="con_excepcion">Con excepción</flux:select.option>
                            <flux:select.option value="rechazado">Rechazo política</flux:select.option>
                            <flux:select.option value="sin_captura">Sin captura</flux:select.option>
                        </flux:select>
                    </flux:field>
                </div>
            </div>
        </flux:card>

        <flux:card class="p-0">

            <div class="flex flex-col gap-1 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
                <flux:text size="sm" class="text-zinc-500">
                    Total: <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $solicitudes->total() }}</span>
                </flux:text>
                <flux:text size="sm" class="text-zinc-500">
                    Página {{ $solicitudes->currentPage() }} de {{ $solicitudes->lastPage() }}
                </flux:text>
            </div>

            <flux:table :paginate="$solicitudes">
                <flux:table.columns>
                    <flux:table.column class="pl-4">
                        <span class="pl-4">
                            Folio
                        </span>
                    </flux:table.column>
                    <flux:table.column>Proyecto</flux:table.column>
                    <flux:table.column>Fechas</flux:table.column>
                    <flux:table.column>Presupuesto</flux:table.column>
                    <flux:table.column>Cumplimiento</flux:table.column>
                    <flux:table.column>Excepciones</flux:table.column>
                    <flux:table.column>Comprobación</flux:table.column>
                    <flux:table.column>Estatus</flux:table.column>
                    <flux:table.column class="flex justify-end pr-4">
                        <span class="pr-4">
                            Acciones
                        </span>
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($solicitudes as $solicitud)
                        @php
                            $pct = $solicitud->monto_total > 0
                                ? round(($solicitud->monto_comprobado / $solicitud->monto_total) * 100, 1)
                                : 0;

                            $cumplimientoColor = match($solicitud->cumplimiento_calculado) {
                                'ok'            => 'green',
                                'con_excepcion' => 'yellow',
                                'rechazado'     => 'red',
                                default         => 'zinc',
                            };

                            $cumplimientoLabel = match($solicitud->cumplimiento_calculado) {
                                'ok'            => 'Ok',
                                'con_excepcion' => 'Con excepción',
                                'rechazado'     => 'Rechazo política',
                                default         => 'Sin captura',
                            };

                            $estatusColor = match($solicitud->estatus) {
                                'Borrador'   => 'zinc',
                                'Pendiente'  => 'yellow',
                                'Autorizado' => 'green',
                                'Rechazado'  => 'red',
                                'Comprobado' => 'blue',
                                'Cancelado'  => 'zinc',
                                default      => 'zinc',
                            };
                        @endphp

                        <flux:table.row :key="$solicitud->id">
                            <flux:table.cell class="pl-4">
                                <span class="pl-4 font-mono text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $solicitud->folio }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-sm text-zinc-800 dark:text-zinc-100">
                                        {{ $solicitud->proyecto_nombre ?? '—' }}
                                    </span>
                                </div>
                            </flux:table.cell>

                            {{-- Fechas --}}
                            <flux:table.cell>
                                <div class="flex flex-col text-xs text-zinc-500">
                                    <span>{{ \Carbon\Carbon::parse($solicitud->fecha_inicio)->format('d/M/Y') }}</span>
                                    <span>{{ \Carbon\Carbon::parse($solicitud->fecha_fin)->format('d/M/Y') }}</span>
                                </div>
                            </flux:table.cell>

                            {{-- Presupuesto + aprobable --}}
                            <flux:table.cell>
                                <div class="flex flex-col text-sm">
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                                        {{ Number::currency($solicitud->monto_total ?? 0, in: 'MXN') }}
                                    </span>
                                    <span class="text-xs text-zinc-400">
                                        Aprobable: {{ Number::currency($solicitud->monto_aprobable ?? 0, in: 'MXN') }}
                                    </span>
                                </div>
                            </flux:table.cell>

                            {{-- Cumplimiento --}}
                            <flux:table.cell>
                                <flux:badge :color="$cumplimientoColor" size="sm" inset="top bottom">
                                    {{ $cumplimientoLabel }}
                                </flux:badge>
                            </flux:table.cell>

                            {{-- Excepciones N1 / N2 --}}
                            <flux:table.cell>
                                <div class="flex flex-col text-xs text-zinc-500">
                                    <span>N1: {{ $solicitud->excepciones_n1 ?? 0 }}</span>
                                    <span>N2: {{ $solicitud->excepciones_n2 ?? 0 }}</span>
                                </div>
                            </flux:table.cell>

                            {{-- Comprobación: monto comprobado / total + porcentaje --}}
                            <flux:table.cell>
                                <div class="flex flex-col text-xs">
                                    <span class="text-zinc-700 dark:text-zinc-300">
                                        {{ Number::currency($solicitud->monto_comprobado ?? 0, in: 'MXN') }}
                                        /
                                        {{ Number::currency($solicitud->monto_total ?? 0, in: 'MXN') }}
                                    </span>
                                    <span class="text-zinc-400">{{ $pct }}%</span>
                                </div>
                            </flux:table.cell>

                            {{-- Estatus --}}
                            <flux:table.cell>
                                <flux:badge :color="$estatusColor" size="sm" inset="top bottom">
                                    {{ $solicitud->estatus }}
                                </flux:badge>
                            </flux:table.cell>

                            {{-- Acciones — bloqueadas según estatus --}}
                            <flux:table.cell class="text-right ">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button
                                        size="sm" variant="ghost" icon="document-currency-dollar"
                                        wire:click="show({{ $solicitud->id }})"
                                        title="Comprobar gastos"
                                    />

                                </div>
                            </flux:table.cell>

                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="9" class="py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <flux:icon name="inbox" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                    <flux:text class="text-zinc-400">No se encontraron solicitudes para comprobar gastos</flux:text>
                                    @if ($search || $cumplimiento)
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
    @endif

</div>
