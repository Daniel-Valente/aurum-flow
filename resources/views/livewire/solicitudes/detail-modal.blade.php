<flux:modal name="solicitud-detail" flyout variant="floating" class="md:w-lg">
    @if ($solicitud)
    @php

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
                        {{ $solicitud->proyecto?->nombre ?? '-' }}
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
                        {{ $solicitud->fecha_solicitud?->format('d/m/Y') ?? '-' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Monto total</span>
                    <span class="text-sm font-mono text-zinc-700">
                        {{ Number::currency($solicitud->monto_total ?? 0, in: 'MXN') }}
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
                        {{ $solicitud->fecha_inicio?->format('d/m/Y') ?? '-' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Fin</span>
                    <span class="text-sm font-mono">
                        {{ $solicitud->fecha_fin?->format('d/m/Y') ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        @if ($solicitud->motivo_rechazo)
        <flux:Separator />
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                Motivo de rechazo
            </flux:subheading>

            <flux:text class="text-sm text-zinc-500 leading-relaxed">
                {{ $solicitud->motivo_rechazo }}
            </flux:text>
        </div>
        @endif

        @if ($solicitud->motivo_cancelacion)
        <flux:Separator />
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                Motivo de cancelación
            </flux:subheading>

            <flux:text class="text-sm text-zinc-500 leading-relaxed">
                {{ $solicitud->motivo_cancelacion }}
            </flux:text>
        </div>
        @endif
    </div>
    @endif
</flux:modal>
