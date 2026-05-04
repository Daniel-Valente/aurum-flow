<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Mis Solicitudes</flux:heading>
            <flux:subheading>Gestiona tus viáticos con visibilidad de políticas y excepciones</flux:subheading>
        </div>

        @can('solicitudes.crear')
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                Nueva Solicitud
            </flux:button>
        @endcan
    </div>

    <div class="grid gap-4 md:grid-cols-4">

        <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide text-zinc-400">Solicitudes</span>
                <span class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $kpis['total'] }}
                </span>
                <span class="text-xs text-zinc-400">En página actual</span>
            </div>
            <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                <flux:icon.document-text class="size-5 text-blue-600 dark:text-blue-400" />
            </div>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide text-zinc-400">Presupuesto</span>
                <span class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ Number::currency($kpis['presupuesto'] ?? 0, in: 'MXN') }}
                </span>
                <span class="text-xs text-zinc-400">Total estimado</span>
            </div>
            <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                <flux:icon.currency-dollar class="size-5 text-emerald-600 dark:text-emerald-400" />
            </div>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide text-zinc-400">Comprobado</span>
                <span class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ Number::currency($kpis['comprobado'] ?? 0, in: 'MXN') }}
                </span>
                <span class="text-xs text-zinc-400">Con documentos</span>
            </div>
            <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                <flux:icon.shield-check class="size-5 text-amber-600 dark:text-amber-400" />
            </div>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide text-zinc-400">Excepciones</span>
                <span class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $kpis['excepciones'] }}
                </span>
                <span class="text-xs text-zinc-400">N1 + N2</span>
            </div>
            <div class="flex size-10 items-center justify-center rounded-lg bg-rose-50 dark:bg-rose-900/20">
                <flux:icon.exclamation-triangle class="size-5 text-rose-600 dark:text-rose-400" />
            </div>
        </div>

    </div>

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
                    <flux:label>Estatus</flux:label>
                    <flux:select variant="listbox" wire:model.live="estatus">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="Borrador">Borrador</flux:select.option>
                        <flux:select.option value="Pendiente">Pendiente</flux:select.option>
                        <flux:select.option value="Autorizado">Autorizado</flux:select.option>
                        <flux:select.option value="Rechazado">Rechazado</flux:select.option>
                        <flux:select.option value="Comprobado">Comprobado</flux:select.option>
                        <flux:select.option value="Cancelado">Cancelado</flux:select.option>
                    </flux:select>
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

            @if ($search || $estatus || $cumplimiento)
                <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                    Limpiar
                </flux:button>
            @endif

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
                        $editable   = $solicitud->estatus === 'Borrador';
                        $cancelable = in_array($solicitud->estatus, ['Borrador', 'Pendiente']);
                        $gestionable = in_array($solicitud->estatus, ['Borrador', 'Pendiente']);
                        $comprobable = $solicitud->estatus === 'Autorizado';

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

                                {{-- Ver detalle — siempre disponible --}}
                                <flux:button
                                    size="sm" variant="ghost" icon="eye"
                                    wire:click="openDetail({{ $solicitud->id }})"
                                    title="Ver detalle"
                                />

                                {{-- Gestionar conceptos — solo Borrador/Pendiente --}}
                                <flux:button
                                    size="sm" variant="ghost" icon="list-bullet"
                                    wire:click="show({{ $solicitud->id }})"
                                    title="{{ $gestionable ? 'Gestionar conceptos' : 'No disponible en este estatus' }}"
                                    :disabled="!$gestionable"
                                />

                                {{-- Comprobar — solo Autorizado --}}
                                <flux:button
                                    size="sm" variant="ghost" icon="document-currency-dollar"
                                    wire:click="show({{ $solicitud->id }})"
                                    title="{{ $comprobable ? 'Comprobar gastos' : 'Disponible cuando esté autorizada' }}"
                                    :disabled="!$comprobable"
                                />

                                {{-- Editar — solo Borrador --}}
                                @can('solicitudes.editar')
                                <flux:button
                                    size="sm" variant="ghost" icon="pencil"
                                    wire:click="{{ $editable ? 'openEdit(' . $solicitud->id . ')' : '' }}"
                                    title="{{ $editable ? 'Editar' : 'Solo editable en borrador' }}"
                                    :disabled="!$editable"
                                />
                                @endcan

                                {{-- Cancelar — solo Borrador/Pendiente --}}
                                @can('solicitudes.eliminar')
                                <flux:button
                                    size="sm" variant="ghost" icon="trash"
                                    wire:click="{{ $cancelable ? 'openDelete(' . $solicitud->id . ')' : '' }}"
                                    title="{{ $cancelable ? 'Cancelar solicitud' : 'No cancelable en este estatus' }}"
                                    :disabled="!$cancelable"
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
                                <flux:text class="text-zinc-400">No se encontraron solicitudes</flux:text>
                                @if ($search || $estatus || $cumplimiento)
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

    @livewire('solicitudes.form-modal')

    <flux:modal name="solicitud-creada" class="w-full max-w-md">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="size-5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:heading size="lg">Solicitud creada</flux:heading>
                    <flux:subheading class="mt-1">
                        La solicitud <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $createdFolio }}</span>
                        fue registrada. ¿Deseas agregar los conceptos ahora?
                    </flux:subheading>
                </div>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                <flux:button variant="ghost" wire:click="stayHere">
                    Quedarme aquí
                </flux:button>
                <flux:button variant="primary" icon="arrow-right" wire:click="goToDetail">
                    Agregar conceptos
                </flux:button>
            </div>
        </div>
    </flux:modal>

    @livewire('solicitudes.detail-modal')

    <flux:modal name="solicitud-delete" class="w-full max-w-sm">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon name="exclamation-triangle" class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <flux:heading size="lg">Cancelar solicitud</flux:heading>
                </div>
            </div>

            <div class="mt-2">
                <flux:subheading class="mt-1">
                    ¿Seguro que deseas cancelar la solicitud
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $deletingNombre }}
                    </span>?
                    Esta acción puede revertirse reabriendo la solicitud.
                </flux:subheading>
            </div>

            <div class="mt-2">
                <flux:field>
                    <flux:label badge="Requerido">Motivo de cancelación</flux:label>
                    <flux:textarea resize="none" wire:model="motivo_cancelacion" />

                    <flux:error name="motivo_cancelacion" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cerrar</flux:button>
                </flux:modal.close>

                <flux:button
                    variant="danger"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                >
                    <span wire:loading.remove wire:target="delete">Cancelar solicitud</span>
                    <span wire:loading wire:target="delete">Cancelando…</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>
