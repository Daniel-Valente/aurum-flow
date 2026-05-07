<div class="space-y-6">
    @include('livewire.solicitudes.detail.partials._header', [
        'badge'      => 'Borrador',
        'badgeColor' => 'orange'
    ])

    @include('livewire.solicitudes.detail.partials._kpi')

    @php
        $ultimaAuditoria = null;
        if ($solicitud->estatus === 'Borrador') {
            $ultimaAuditoria = \App\Models\SolicitudAuditoria::where('solicitud_id', $solicitud->id)
                ->where('evento', 'rechazado')
                ->latest()
                ->first();
        }
    @endphp

    @if ($ultimaAuditoria && $ultimaAuditoria->created_at->diffInDays(now()) < 7)
        <div class="rounded-lg border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 p-4 mb-6">
            <div class="flex items-start gap-3">
                <flux:icon.exclamation-triangle class="size-5 text-rose-500 shrink-0 mt-0.5" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-rose-800 dark:text-rose-200">
                        Esta solicitud fue rechazada anteriormente
                    </p>
                    @if (!empty($ultimaAuditoria->datos['comentario']))
                        <div class="mt-2 rounded bg-rose-100 dark:bg-rose-900/30 px-3 py-2">
                            <p class="text-xs font-medium text-rose-700 dark:text-rose-300 mb-1">
                                Motivo del rechazo:
                            </p>
                            <p class="text-sm text-rose-600 dark:text-rose-400">
                                {{ $ultimaAuditoria->datos['comentario'] }}
                            </p>
                        </div>
                    @endif
                    <p class="text-xs text-rose-500 mt-2">
                        💡 Corrige los puntos señalados antes de volver a enviar.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-4">
        <flux:card>
            <flux:subheading class="text-xs uppercase tracking-widest text-zinc-400">
                Agregar gasto
            </flux:subheading>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <flux:field class="flex-1">
                    <flux:label>Concepto</flux:label>
                    <flux:select variant="listbox" wire:model="concepto_id">
                        <flux:select.option value=""></flux:select.option>
                        @foreach ($conceptos as $concepto)
                            <flux:select.option value="{{ $concepto['id'] }}">
                                {{ $concepto['nombre'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field class="flex-1">
                    <flux:label>Monto ($)</flux:label>
                    <flux:input
                        wire:model="monto"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                    />
                </flux:field>

                <div class="flex items-end">
                    <flux:button
                        variant="primary"
                        color="cyan"
                        icon="plus"
                        class="w-full"
                        wire:click="agregarDetalle"
                        wire:loading.attr="disabled"
                        >
                        Agregar
                    </flux:button>
                </div>
            </div>
        </flux:card>
    </div>

    <div class="space-y-4">
        <flux:card>
            <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 text-sm">
                <span class="text-zinc-500">
                    Total:
                    <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                        {{ count($detalles) }}
                    </span>
                </span>
            </div>

            <flux:table container:class="max-h-80">
                <flux:table.columns>
                    <flux:table.column class="pl-4">Concepto</flux:table.column>
                    <flux:table.column>Monto</flux:table.column>
                    <flux:table.column>Tipo</flux:table.column>
                    <flux:table.column>Límite</flux:table.column>
                    <flux:table.column>Estado</flux:table.column>
                    <flux:table.column>Acción</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($detalles as $detalle)
                        <flux:table.row :key="$detalle['id']">
                            <flux:table.cell>{{ $detalle['concepto_nombre'] }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($editandoDetalle === $detalle['id'])
                                    <div class="flex items-center gap-2">
                                        <flux:input
                                            wire:model="editandoMonto"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            class="w-28"
                                        />
                                        <flux:button
                                            size="sm"
                                            variant="primary"
                                            icon="check"
                                            wire:click="guardarDetalle({{ $detalle['id'] }})"
                                            wire:keydown.enter="guardarDetalle({{ $detalle['id'] }})"
                                        />
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="x-mark"
                                            wire:click="$set('editandoDetalle', null)"
                                        />
                                    </div>
                                @else
                                    {{ Number::currency($detalle['monto_estimado'], in: 'MXN') }}
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $detalle['tipo_aplicacion'] }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="font-mono text-sm text-zinc-500">
                                    {{ $detalle['limite_politica'] ? Number::currency($detalle['limite_politica'], in: 'MXN') : '—' }}
                                </span>
                            </flux:table.cell>{{-- límite de política (opcional) --}}
                            <flux:table.cell>
                                @php $color = match($detalle['semaforo']) {
                                    'ok'           => 'green',
                                    'limite'       => 'yellow',
                                    'excedido'     => 'red',
                                    'sin_politica' => 'zinc',
                                }; @endphp
                                <flux:badge :color="$color" size="sm">
                                    {{ match($detalle['semaforo']) {
                                        'ok'           => 'Ok',
                                        'limite'       => 'Al límite',
                                        'excedido'     => 'Excedido',
                                        'sin_politica' => 'Sin política',
                                    } }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($solicitud->estatus === 'Borrador')
                                <flux:button
                                    size="sm" variant="ghost" icon="pencil"
                                    wire:click="editarDetalle({{ $detalle['id'] }})"
                                />
                                <flux:button
                                    size="sm" variant="ghost" icon="trash"
                                    wire:click="eliminarDetalle({{ $detalle['id'] }})"
                                />
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="py-3 flex justify-between">
                <div>
                    <span class="text-lg font-mono font-semibold text-zinc-800 dark:text-zinc-100">
                        {{ Number::currency($total, in: 'MXN') }}
                    </span>
                </div>
                <flux:button
                    variant="primary"
                    color="green"
                    icon="paper-airplane"
                    :disabled="count($detalles) === 0"
                    wire:click="enviar"
                    >
                    Enviar solicitud
                </flux:button>
            </div>
        </flux:card>
    </div>

</div>
