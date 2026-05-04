<flux:modal name="autorizacion-detail" flyout variant="floating" class="md:w-lg">
    @if ($solicitud)
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
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <flux:avatar
                    :name="$solicitud->proyecto?->nombre"
                    :initials="strtoupper(substr($solicitud->proyecto?->nombre ?? 'P', 0, 1))"
                    size="lg"
                />
                <div class="flex flex-col gap-0.5">
                    <flux:heading size="lg" class="leading-tight">
                        {{ $solicitud->empleado->nombre_completo }} - {{ $solicitud->proyecto?->nombre ?? '-' }}
                    </flux:heading>
                    <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500 tracking-wide">
                        {{ $solicitud->folio }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <flux:badge :color="$estatusColor" size="sm" inset="top bottom">
                    {{ $solicitud->estatus }}
                </flux:badge>

                <flux:badge :color="$cumplimientoColor" size="sm" inset="top bottom">
                    {{ $cumplimientoLabel }}
                </flux:badge>
            </div>
        </div>

        @if ($solicitud->motivo)
            <flux:text class="text-sm text-zinc-500 leading-relaxed">
                {{ $solicitud->motivo }}
            </flux:text>
        @endif

        <flux:separator />

        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                General
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Fecha de solicitud</span>
                    <span class="text-sm font-mono">
                        {{ $solicitud->fecha_solicitud->format('d/m/Y') }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Monto total</span>
                    <span class="text-sm font-mono text-zinc-700">
                        {{ Number::currency($solicitud->monto_total ?? 0, in: 'MXN') }}
                    </span>
                    <span class="text-xs text-zinc-400">
                        Aprobable: {{ Number::currency($solicitud->monto_aprobable ?? 0, in: 'MXN') }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Excepción N1 pendiente</span>
                    <span class="text-sm font-mono">
                        {{ $solicitud->excepciones_n1 ?? 0 }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Excepción N2 pendiente</span>
                    <span class="text-sm font-mono text-zinc-700">
                        {{ $solicitud->excepciones_n2 ?? 0 }}
                    </span>
                </div>
            </div>
        </div>

        <flux:separator />

        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                Vigencia
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Inicio</span>
                    <span class="text-sm font-mono">
                        {{ $solicitud->fecha_inicio->format('d/m/Y') }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Fin</span>
                    <span class="text-sm font-mono">
                        {{ $solicitud->fecha_fin->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>

        <flux:separator />

        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                Detalle
            </flux:subheading>
            <div class="space-y-4">
                <flux:table container:class="max-h-80">
                    <flux:table.columns>
                        <flux:table.column class="pl-4">Concepto</flux:table.column>
                        <flux:table.column>Monto</flux:table.column>
                        <flux:table.column>Tipo</flux:table.column>
                        <flux:table.column>Límite</flux:table.column>
                        <flux:table.column>Estado</flux:table.column>
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
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                <div class="py-3 flex justify-between gap-3">

                    @if ($confirmandoRechazo)
                        <div class="flex flex-col gap-3 w-full">
                            <flux:field>
                                <flux:label badge="Requerido">Motivo de rechazo</flux:label>
                                <flux:textarea
                                    wire:model="motivo_rechazo"
                                    placeholder="Describe el motivo por el que rechazas esta solicitud..."
                                    resize="none"
                                    rows="3"
                                />
                                <flux:error name="motivo_rechazo" />
                            </flux:field>

                            <div class="flex justify-between gap-2">
                                <flux:button
                                    variant="ghost"
                                    wire:click="cancelarRechazo"
                                >
                                    Cancelar
                                </flux:button>

                                <flux:button
                                    variant="danger"
                                    icon="x-mark"
                                    wire:click="rechazar"
                                    wire:loading.attr="disabled"
                                    wire:target="rechazar"
                                >
                                    <span wire:loading.remove wire:target="rechazar">Confirmar rechazo</span>
                                    <span wire:loading wire:target="rechazar">Rechazando…</span>
                                </flux:button>
                            </div>
                        </div>

                    @else
                        {{-- Botones normales --}}
                        <flux:button
                            variant="danger"
                            icon="x-mark"
                            wire:click="iniciarRechazo"
                        >
                            Rechazar
                        </flux:button>

                        <flux:button
                            variant="primary"
                            color="green"
                            icon="check"
                            wire:click="aprobar"
                            wire:loading.attr="disabled"
                            wire:target="aprobar"
                        >
                            <span wire:loading.remove wire:target="aprobar">Aprobar</span>
                            <span wire:loading wire:target="aprobar">Aprobando…</span>
                        </flux:button>
                    @endif

                </div>
            </div>
        </div>

    </div>
    @endif
</flux:modal>
