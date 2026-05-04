<div class="space-y-6">
    @php
        $steps = [
            1 => ['label' => 'Borrador',   'icon' => 'clipboard-document'],
            2 => ['label' => 'Aprobación', 'icon' => 'check-circle'],
            3 => ['label' => 'Viaje',      'icon' => 'globe-americas'],
        ];

        function stepStatus($current, $step) {
            return $current > $step
                ? 'complete'
                : ($current === $step ? 'current' : 'incomplete');
        }
    @endphp

    <flux:timeline horizontal>
        @foreach ($steps as $number => $step)
            <flux:timeline.item status="{{ stepStatus($stepActual, $number) }}">

                <flux:timeline.indicator>
                    <flux:icon :name="$step['icon']" variant="micro" />
                </flux:timeline.indicator>

                <flux:timeline.content>
                    <flux:heading>{{ $step['label'] }}</flux:heading>
                </flux:timeline.content>

            </flux:timeline.item>
        @endforeach
    </flux:timeline>

    <div class="pt-4">

        @if ($stepActual === 1)
            <div class="space-y-6">
                <flux:card>
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <flux:heading size="lg">
                                {{ $solicitud->proyecto->nombre }}
                            </flux:heading>
                            <span class="text-xs font-mono text-zinc-400 tracking-wide">
                                {{ $solicitud->folio }}
                            </span>
                        </div>

                        <flux:badge color="orange" size="sm">
                            {{ $solicitud->estatus }}
                        </flux:badge>
                    </div>
                </flux:card>

                <div class="grid gap-4 md:grid-cols-4">

                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div>
                            <span class="text-xs uppercase text-zinc-400">Dentro de límite</span>
                            <p class="text-2xl font-semibold text-emerald-600">{{ $kpi_ok }}</p>
                        </div>
                        <flux:icon.check-circle class="size-5 text-emerald-500" />
                    </div>

                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div>
                            <span class="text-xs uppercase text-zinc-400">Al límite</span>
                            <p class="text-2xl font-semibold text-amber-500">{{ $kpi_limite }}</p>
                        </div>
                        <flux:icon.exclamation-circle class="size-5 text-amber-500" />
                    </div>

                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div>
                            <span class="text-xs uppercase text-zinc-400">Excedidos</span>
                            <p class="text-2xl font-semibold text-rose-500">{{ $kpi_excedido }}</p>
                        </div>
                        <flux:icon.x-circle class="size-5 text-rose-500" />
                    </div>

                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div>
                            <span class="text-xs uppercase text-zinc-400">Sin política</span>
                            <p class="text-2xl font-semibold text-zinc-500">{{ $kpi_sin_politica }}</p>
                        </div>
                        <flux:icon.question-mark-circle class="size-5 text-zinc-400" />
                    </div>

                </div>

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
                                        <flux:table.cell>{{ Number::currency($detalle['monto_estimado'], in: 'MXN') }}</flux:table.cell>
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
        @endif


        @if ($stepActual === 2)
            <div class="space-y-6">
                <flux:card>
                    <div class="flex flex-col gap-4">

                        <div class="flex items-start justify-between gap-3">
                            <div class="flex flex-col gap-0.5">
                                <flux:heading size="lg">{{ $solicitud->proyecto_nombre ?? $solicitud->proyecto?->nombre }}</flux:heading>
                                <span class="text-xs font-mono text-zinc-400">{{ $solicitud->folio }}</span>
                            </div>
                            <flux:badge color="yellow" size="sm">Pendiente de aprobación</flux:badge>
                        </div>

                        {{-- Banner de espera --}}
                        <div class="flex items-center gap-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-3 py-2.5">
                            <flux:icon.clock class="size-4 text-amber-500 shrink-0" />
                            <span class="text-sm text-amber-700 dark:text-amber-400">
                                Tu solicitud está en revisión. Se requieren
                                <span class="font-semibold">{{ $aprobacionesTotal }} / {{ $aprobacionesMinimo }} aprobaciones</span>
                                para continuar.
                                @if ($aprobacionesFaltan > 0)
                                    <span class="text-xs opacity-75">· Faltan {{ $aprobacionesFaltan }}</span>
                                @endif
                            </span>
                        </div>

                        {{-- Datos clave --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Presupuesto</span>
                                <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                                    {{ Number::currency($solicitud->monto_total ?? 0, in: 'MXN') }}
                                </span>
                            </div>
                            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Conceptos</span>
                                <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                                    {{ count($detalles) }}
                                </span>
                            </div>
                            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Inicio</span>
                                <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                                    {{ $solicitud->fecha_inicio?->format('d/m/Y') ?? '—' }}
                                </span>
                            </div>
                            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Fin</span>
                                <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                                    {{ $solicitud->fecha_fin?->format('d/m/Y') ?? '—' }}
                                </span>
                            </div>
                        </div>

                        @if ($solicitud->motivo)
                            <div>
                                <span class="text-[10px] uppercase tracking-widest text-zinc-400">Motivo</span>
                                <p class="mt-1 text-sm text-zinc-500 leading-relaxed">{{ $solicitud->motivo }}</p>
                            </div>
                        @endif

                    </div>
                </flux:card>

                <flux:card>
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="sm">Aprobadores requeridos</flux:heading>

                        {{-- Progreso 2/3 --}}
                        <div class="flex items-center gap-2">
                            @php
                                $aprobados = collect($aprobadores)->where('aprobado', true)->count();
                                $rechazados = collect($aprobadores)->where('rechazado', true)->count();
                            @endphp
                            <span class="text-xs text-zinc-400">{{ $aprobados }} / 2 aprobaciones</span>
                            <div class="flex gap-1">
                                @foreach ($aprobadores as $ap)
                                    <div class="w-2.5 h-2.5 rounded-full {{ $ap['aprobado'] ? 'bg-emerald-500' : ($ap['rechazado'] ? 'bg-rose-500' : 'bg-zinc-300 dark:bg-zinc-600') }}"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($aprobadores as $ap)
                            <div class="flex items-center justify-between py-3 gap-3">
                                <div class="flex items-center gap-3">
                                    {{-- Avatar inicial --}}
                                    <div class="flex size-9 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800 text-sm font-medium text-zinc-600 dark:text-zinc-300 shrink-0">
                                        {{ strtoupper(substr($ap['nombre'], 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                            {{ $ap['nombre'] }}
                                        </span>
                                        <span class="text-xs text-zinc-400">{{ $ap['rol'] }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if ($ap['aprobado'])
                                        <flux:badge color="green" size="sm">
                                            <flux:icon.check class="size-3 mr-1" />
                                            Aprobado
                                        </flux:badge>
                                        @if ($ap['fecha'])
                                            <span class="text-xs text-zinc-400">{{ $ap['fecha'] }}</span>
                                        @endif
                                    @elseif ($ap['rechazado'])
                                        <flux:badge color="red" size="sm">
                                            <flux:icon.x-mark class="size-3 mr-1" />
                                            Rechazado
                                        </flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">Pendiente</flux:badge>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Si fue rechazada --}}
                    @if ($solicitud->estatus === 'Rechazado' && $solicitud->motivo_rechazo)
                        <div class="mt-4 rounded-lg bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 px-3 py-2.5">
                            <p class="text-xs font-medium text-rose-700 dark:text-rose-400 mb-0.5">Motivo de rechazo</p>
                            <p class="text-sm text-rose-600 dark:text-rose-300">{{ $solicitud->motivo_rechazo }}</p>
                        </div>
                    @endif

                </flux:card>

                <div class="grid gap-3 grid-cols-2 sm:grid-cols-4">
                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div>
                            <span class="text-xs uppercase text-zinc-400">Dentro de límite</span>
                            <p class="text-2xl font-semibold text-emerald-600">{{ $kpi_ok }}</p>
                        </div>
                        <flux:icon.check-circle class="size-5 text-emerald-500" />
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div>
                            <span class="text-xs uppercase text-zinc-400">Al límite</span>
                            <p class="text-2xl font-semibold text-amber-500">{{ $kpi_limite }}</p>
                        </div>
                        <flux:icon.exclamation-circle class="size-5 text-amber-500" />
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div>
                            <span class="text-xs uppercase text-zinc-400">Excedidos</span>
                            <p class="text-2xl font-semibold text-rose-500">{{ $kpi_excedido }}</p>
                        </div>
                        <flux:icon.x-circle class="size-5 text-rose-500" />
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div>
                            <span class="text-xs uppercase text-zinc-400">Sin política</span>
                            <p class="text-2xl font-semibold text-zinc-500">{{ $kpi_sin_politica }}</p>
                        </div>
                        <flux:icon.question-mark-circle class="size-5 text-zinc-400" />
                    </div>
                </div>

                <flux:card class="p-0">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:text size="sm" class="text-zinc-500">
                            Conceptos: <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ count($detalles) }}</span>
                        </flux:text>
                        <flux:text size="sm" class="text-zinc-500 font-mono">
                            Total: {{ Number::currency($solicitud->monto_total ?? 0, in: 'MXN') }}
                        </flux:text>
                    </div>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="pl-4"><span class="pl-4">Concepto</span></flux:table.column>
                            <flux:table.column>Tipo</flux:table.column>
                            <flux:table.column>Monto estimado</flux:table.column>
                            <flux:table.column>Límite política</flux:table.column>
                            <flux:table.column>Comprobante</flux:table.column>
                            <flux:table.column>Estado</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($detalles as $detalle)
                                @php
                                    $semaforoColor = match($detalle['semaforo']) {
                                        'ok'           => 'green',
                                        'limite'       => 'yellow',
                                        'excedido'     => 'red',
                                        'sin_politica' => 'zinc',
                                    };
                                    $semaforoLabel = match($detalle['semaforo']) {
                                        'ok'           => 'Ok',
                                        'limite'       => 'Al límite',
                                        'excedido'     => 'Excedido',
                                        'sin_politica' => 'Sin política',
                                    };
                                    $comprobanteLabel = match($detalle['comprobante_requerido'] ?? 'ninguno') {
                                        'cfdi'    => 'CFDI (factura)',
                                        'ticket'  => 'Ticket / recibo',
                                        'ninguno' => 'Sin requisito',
                                        default   => '—',
                                    };
                                    $comprobanteColor = match($detalle['comprobante_requerido'] ?? 'ninguno') {
                                        'cfdi'    => 'red',
                                        'ticket'  => 'yellow',
                                        'ninguno' => 'green',
                                        default   => 'zinc',
                                    };
                                @endphp
                                <flux:table.row :key="$detalle['id']">
                                    <flux:table.cell class="pl-4">
                                        <div class="pl-4 flex flex-col">
                                            <span class="font-medium text-sm">{{ $detalle['concepto_nombre'] }}</span>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-xs text-zinc-500">{{ $detalle['tipo_aplicacion'] }}</span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="font-mono text-sm">
                                            {{ Number::currency($detalle['monto_estimado'], in: 'MXN') }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="font-mono text-sm text-zinc-500">
                                            {{ $detalle['limite_politica'] ? Number::currency($detalle['limite_politica'], in: 'MXN') : '—' }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge :color="$comprobanteColor" size="sm">
                                            {{ $comprobanteLabel }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge :color="$semaforoColor" size="sm">
                                            {{ $semaforoLabel }}
                                        </flux:badge>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </flux:card>

            </div>
        @endif


        @if ($stepActual === 3)
            {{-- VIAJE --}}
            {{--
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-900">
                <flux:heading size="sm">Detalles del viaje</flux:heading>

                <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-xs text-zinc-400">Origen</span>
                        <p>{{ $solicitud->origen ?? '-' }}</p>
                    </div>

                    <div>
                        <span class="text-xs text-zinc-400">Destino</span>
                        <p>{{ $solicitud->destino ?? '-' }}</p>
                    </div>
                </div>
            </div> --}}
        @endif

    </div>

    <flux:modal name="justificacion-excesos" wire:model="mostrandoJustificaciones">
        <div class="flex flex-col gap-5">

            <div>
                <flux:heading size="lg">Justificación requerida</flux:heading>
                <flux:subheading>
                    Los siguientes conceptos exceden el límite de política.
                    Explica el motivo antes de enviar.
                </flux:subheading>
            </div>

            @foreach ($detalles as $detalle)
                @if ($detalle['semaforo'] === 'excedido')
                    <div class="rounded-lg border border-rose-200 bg-rose-50 dark:border-rose-800 dark:bg-rose-900/10 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-sm">{{ $detalle['concepto_nombre'] }}</span>
                            <div class="flex items-center gap-2 text-xs font-mono">
                                <span class="text-zinc-400 line-through">
                                    {{ Number::currency($detalle['limite_politica'], in: 'MXN') }}
                                </span>
                                <span class="text-rose-600 font-bold">
                                    {{ Number::currency($detalle['monto_estimado'], in: 'MXN') }}
                                </span>
                            </div>
                        </div>

                        <flux:field>
                            <flux:label>Justificación del exceso</flux:label>
                            <flux:textarea
                                wire:model="justificaciones.{{ $detalle['id'] }}"
                                placeholder="Ej: El hotel recomendado estaba lleno, se ocupó la opción más cercana disponible..."
                                resize="none"
                                rows="2"
                            />
                            <flux:error name="justificaciones.{{ $detalle['id'] }}" />
                        </flux:field>
                    </div>
                @endif
            @endforeach

            <div class="flex justify-between gap-3">
                <flux:button variant="ghost" wire:click="$set('mostrandoJustificaciones', false)">
                    Cancelar
                </flux:button>
                <flux:button
                    variant="primary"
                    color="green"
                    icon="paper-airplane"
                    wire:click="guardarJustificacionesYEnviar"
                    wire:loading.attr="disabled"
                    wire:target="guardarJustificacionesYEnviar"
                >
                    <span wire:loading.remove wire:target="guardarJustificacionesYEnviar">Enviar solicitud</span>
                    <span wire:loading wire:target="guardarJustificacionesYEnviar">Enviando…</span>
                </flux:button>
            </div>

        </div>
    </flux:modal>

</div>
