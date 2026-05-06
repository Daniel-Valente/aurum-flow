<div class="space-y-6">
    @include('livewire.solicitudes.detail.partials._header', [
        'badge'      => 'Pendiente de aprobación',
        'badgeColor' => 'yellow'
    ])

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

    @include('livewire.solicitudes.detail.partials._kpi')

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
