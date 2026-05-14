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

            <div class="hidden sm:block">
                <flux:table container:class="max-h-80">
                    <flux:table.columns>
                        <flux:table.column class="pl-4">Concepto</flux:table.column>
                        <flux:table.column>Monto</flux:table.column>
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
                                <flux:table.cell>
                                    <div class="flex flex-col gap-3">
                                        @if ($detalle['tipo_limite_politica'] === 'Diario' && $solicitud?->fecha_inicio && $solicitud?->fecha_fin)
                                            @php
                                                $duracion = $solicitud->fecha_inicio->diffInDays($solicitud->fecha_fin) + 1;
                                                $monto = (float) $detalle['limite_politica'] * $duracion
                                            @endphp
                                            <span class="font-semibold">
                                                {{ Number::currency($monto, in: 'MXN') }}
                                            </span>
                                        @endif
                                        <span class="font-mono text-sm text-zinc-500">
                                            {{ $detalle['limite_politica'] ? Number::currency($detalle['limite_politica'], in: 'MXN') : '—' }}
                                            - <span class="text-xs">
                                                {{ $detalle['tipo_limite_politica'] ?? '-' }}
                                            </span>
                                        </span>
                                    </div>
                                </flux:table.cell>
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
                                    @if (
                                        $detalle['semaforo'] === 'excedido'
                                        && !$detalle['permite_excepcion']
                                        && $tieneTarjetaCorporativa
                                    )
                                        <div class="mt-2 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/30 px-3 py-2.5 space-y-2">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-2">
                                                    <flux:icon.credit-card class="size-4 text-blue-500 shrink-0" />
                                                    <span class="text-xs font-medium text-blue-800 dark:text-blue-200 hidden sm:block">
                                                        El excedente lo cubro con mi tarjeta corporativa
                                                    </span>
                                                </div>
                                                <flux:switch
                                                    wire:click="toggleExtensionTarjeta({{ $detalle['id'] }})"
                                                    :checked="$detalle['requiere_extension_tarjeta']"
                                                />
                                            </div>

                                            @if ($detalle['requiere_extension_tarjeta'])
                                                <div class="flex items-center gap-3 pt-1">
                                                    <div class="flex flex-col gap-0.5 flex-1">
                                                        <span class="text-[10px] text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                                            Monto que va a solicitud
                                                        </span>
                                                        <span class="text-sm font-mono font-semibold text-zinc-800 dark:text-zinc-100">
                                                            @php
                                                                $montoSolicitud = $detalle['monto_estimado']
                                                                    - ($detalle['monto_extension_tarjeta'] ?? 0);

                                                                if ($detalle['tipo_limite_politica'] === 'Diario'
                                                                        && $solicitud?->fecha_inicio
                                                                        && $solicitud?->fecha_fin) {
                                                                    $duracion = $solicitud->fecha_inicio->diffInDays($solicitud->fecha_fin) + 1;
                                                                    $montoSolicitud = $detalle['limite_politica'] * $duracion;
                                                                }
                                                            @endphp
                                                            {{ Number::currency(max(0, $montoSolicitud), in: 'MXN') }}
                                                        </span>
                                                    </div>

                                                    <flux:field class="w-36">
                                                        <flux:label class="text-[10px] text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                                            Monto a tarjeta ($)
                                                        </flux:label>
                                                        <flux:input
                                                            type="number"
                                                            step="0.01"
                                                            min="0.01"
                                                            :max="$detalle['monto_estimado'] - 0.01"
                                                            :value="$detalle['monto_extension_tarjeta'] ?? ($detalle['monto_estimado'] - $detalle['limite_politica'])"
                                                            wire:change="guardarMontoExtension({{ $detalle['id'] }}, $event.target.value)"
                                                            size="sm"
                                                        />
                                                    </flux:field>
                                                </div>

                                                <p class="text-[10px] text-blue-500 dark:text-blue-400">
                                                    Al autorizarse la solicitud se generará automáticamente una comprobación
                                                    de tarjeta vinculada donde subirás el CFDI con la porción correspondiente.
                                                </p>
                                            @endif
                                        </div>
                                    @endif
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
            </div>

            <div class="space-y-4 md:hidden">
                @foreach ($detalles as $detalle)
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-sm">
                                    {{ $detalle['concepto_nombre'] }}
                                </h3>

                                <div class="mt-1">
                                    @php $color = match($detalle['semaforo']) {
                                        'ok' => 'green',
                                        'limite' => 'yellow',
                                        'excedido' => 'red',
                                        'sin_politica' => 'zinc',
                                    }; @endphp

                                    <flux:badge :color="$color" size="sm">
                                        {{ match($detalle['semaforo']) {
                                            'ok' => 'Ok',
                                            'limite' => 'Al límite',
                                            'excedido' => 'Excedido',
                                            'sin_politica' => 'Sin política',
                                        } }}
                                    </flux:badge>
                                </div>
                            </div>

                            @if ($solicitud->estatus === 'Borrador')
                                <div class="flex items-center gap-1">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil"
                                        wire:click="editarDetalle({{ $detalle['id'] }})"
                                    />

                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="eliminarDetalle({{ $detalle['id'] }})"
                                    />
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-zinc-500 text-xs uppercase tracking-wide">
                                    Monto
                                </p>

                                @if ($editandoDetalle === $detalle['id'])
                                    <div class="mt-1 flex gap-2">
                                        <flux:input
                                            wire:model="editandoMonto"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                        />

                                        <flux:button
                                            size="sm"
                                            icon="check"
                                            wire:click="guardarDetalle({{ $detalle['id'] }})"
                                        />

                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="x-mark"
                                            wire:click="$set('editandoDetalle', null)"
                                        />
                                    </div>
                                @else
                                    <p class="font-semibold">
                                        {{ Number::currency($detalle['monto_estimado'], in: 'MXN') }}
                                    </p>
                                @endif
                            </div>

                            <div>
                                <p class="text-zinc-500 text-xs uppercase tracking-wide">
                                    Límite
                                </p>

                                <div class="flex flex-col">
                                    @if ($detalle['tipo_limite_politica'] === 'Diario' && $solicitud?->fecha_inicio && $solicitud?->fecha_fin)
                                        @php
                                            $duracion = $solicitud->fecha_inicio->diffInDays($solicitud->fecha_fin) + 1;
                                            $monto = (float) $detalle['limite_politica'] * $duracion;
                                        @endphp

                                        <span class="font-semibold">
                                            {{ Number::currency($monto, in: 'MXN') }}
                                        </span>
                                    @endif

                                    <span class="text-xs text-zinc-500">
                                        {{ $detalle['limite_politica']
                                            ? Number::currency($detalle['limite_politica'], in: 'MXN')
                                            : '—'
                                        }}
                                        · {{ $detalle['tipo_limite_politica'] ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if (
                            $detalle['semaforo'] === 'excedido'
                            && !$detalle['permite_excepcion']
                            && $tieneTarjetaCorporativa
                        )
                            <div class="mt-2 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/30 px-3 py-2.5 space-y-2">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.credit-card class="size-4 text-blue-500 shrink-0" />
                                        <span class="text-xs font-medium text-blue-800 dark:text-blue-200">
                                            El excedente lo cubro con mi tarjeta corporativa
                                        </span>
                                    </div>
                                    <flux:switch
                                        wire:click="toggleExtensionTarjeta({{ $detalle['id'] }})"
                                        :checked="$detalle['requiere_extension_tarjeta']"
                                    />
                                </div>

                                @if ($detalle['requiere_extension_tarjeta'])
                                    <div class="flex items-center gap-3 pt-1">
                                        <div class="flex flex-col gap-0.5 flex-1">
                                            <span class="text-[10px] text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                                Monto que va a solicitud
                                            </span>
                                            <span class="text-sm font-mono font-semibold text-zinc-800 dark:text-zinc-100">
                                                @php
                                                    $montoSolicitud = $detalle['monto_estimado']
                                                        - ($detalle['monto_extension_tarjeta'] ?? 0);

                                                    if ($detalle['tipo_limite_politica'] === 'Diario'
                                                            && $solicitud?->fecha_inicio
                                                            && $solicitud?->fecha_fin) {
                                                        $duracion = $solicitud->fecha_inicio->diffInDays($solicitud->fecha_fin) + 1;
                                                        $montoSolicitud = $detalle['limite_politica'] * $duracion;
                                                    }
                                                @endphp
                                                {{ Number::currency(max(0, $montoSolicitud), in: 'MXN') }}
                                            </span>
                                        </div>

                                        <flux:field class="w-36">
                                            <flux:label class="text-[10px] text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                                Monto a tarjeta ($)
                                            </flux:label>
                                            <flux:input
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                :max="$detalle['monto_estimado'] - 0.01"
                                                :value="$detalle['monto_extension_tarjeta'] ?? ($detalle['monto_estimado'] - $detalle['limite_politica'])"
                                                wire:change="guardarMontoExtension({{ $detalle['id'] }}, $event.target.value)"
                                                size="sm"
                                            />
                                        </flux:field>
                                    </div>

                                    <p class="text-[10px] text-blue-500 dark:text-blue-400">
                                        Al autorizarse la solicitud se generará automáticamente una comprobación
                                        de tarjeta vinculada donde subirás el CFDI con la porción correspondiente.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @php
                $totalExtension = collect($detalles)
                    ->where('requiere_extension_tarjeta', true)
                    ->sum('monto_extension_tarjeta');
            @endphp

            @if ($totalExtension > 0)
                <div class="mt-3 mb-3 flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400 font-mono">
                    <flux:icon.credit-card class="size-3" />
                    {{ Number::currency($totalExtension, in: 'MXN') }} irán a comprobación de tarjeta
                </div>
            @endif

            <div class="py-3 flex items-center justify-between">
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
