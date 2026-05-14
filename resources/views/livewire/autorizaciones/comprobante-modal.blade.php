<flux:modal wire:model="open" class="w-full max-w-lg">

    @if ($comprobante)
        @php
            $gasto     = $comprobante->gasto;
            $solicitud = $gasto->solicitud;
            $empleado  = $solicitud->empleado;

            $tipoLabel = match($comprobante->tipo) {
                'pdf'    => 'PDF',
                'recibo' => 'Recibo',
                default  => ucfirst($comprobante->tipo),
            };
        @endphp

        <div class="flex flex-col gap-5">

            {{-- Header --}}
            <div>
                <flux:heading size="lg">Validar comprobante</flux:heading>
                <flux:subheading>
                    Revisa el archivo adjunto y aprueba o rechaza el comprobante.
                </flux:subheading>
            </div>

            {{-- Datos del gasto --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-100 dark:divide-zinc-800">

                <div class="grid grid-cols-2 gap-x-4 gap-y-1 px-4 py-3 text-sm">
                    <span class="text-zinc-400">Empleado</span>
                    <span class="font-medium text-zinc-800 dark:text-zinc-100">
                        {{ $empleado->nombre_completo }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-1 px-4 py-3 text-sm">
                    <span class="text-zinc-400">Folio solicitud</span>
                    <span class="font-mono text-zinc-500">{{ $solicitud->folio }}</span>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-1 px-4 py-3 text-sm">
                    <span class="text-zinc-400">Proyecto</span>
                    <span class="text-zinc-700 dark:text-zinc-200">{{ $solicitud->proyecto->nombre ?? '—' }}</span>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-1 px-4 py-3 text-sm">
                    <span class="text-zinc-400">Concepto</span>
                    <span class="text-zinc-700 dark:text-zinc-200">{{ $gasto->concepto->nombre }}</span>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-1 px-4 py-3 text-sm">
                    <span class="text-zinc-400">Monto del comprobante</span>
                    <span class="font-mono font-semibold text-zinc-800 dark:text-zinc-100">
                        {{ Number::currency($comprobante->monto, in: 'MXN') }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-1 px-4 py-3 text-sm">
                    <span class="text-zinc-400">Tipo de comprobante</span>
                    <flux:badge color="blue" size="sm">{{ $tipoLabel }}</flux:badge>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-1 px-4 py-3 text-sm">
                    <span class="text-zinc-400">Subido el</span>
                    <span class="text-zinc-500">{{ $comprobante->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            {{-- Enlace al archivo --}}
            <div class="space-y-2">
                {{-- XML / Ticket / Recibo --}}
                <div class="flex items-center gap-3 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-600 px-4 py-3">
                    <flux:icon.document-text class="size-5 text-zinc-400 shrink-0" />
                    <div class="flex flex-col min-w-0 flex-1">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200 truncate">
                            {{ basename($comprobante->archivo) }}
                        </span>
                        <span class="text-xs text-zinc-400">
                            {{ $comprobante->tipo === 'factura' ? 'XML del CFDI' : 'Ticket / Recibo' }}
                        </span>
                    </div>
                    <flux:button
                        size="sm"
                        wire:click="descargar"
                        icon="arrow-down-tray"
                        title="Descargar archivo"
                    />
                </div>

                {{-- PDF companion (solo facturas) --}}
                @if ($comprobante->archivo_pdf)
                    <div class="flex items-center gap-3 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-600 px-4 py-3">
                        <flux:icon.document class="size-5 text-rose-400 shrink-0" />
                        <div class="flex flex-col min-w-0 flex-1">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200 truncate">
                                {{ basename($comprobante->archivo_pdf) }}
                            </span>
                            <span class="text-xs text-zinc-400">PDF de la factura</span>
                        </div>
                        <flux:button
                            size="sm"
                            wire:click="descargarPdf"
                            icon="arrow-down-tray"
                            title="Descargar PDF"
                        />
                    </div>
                @endif
            </div>

            {{-- Campo de comentario (requerido si rechaza) --}}
            <flux:field>
                <flux:label>
                    Comentario
                    @if ($accion === 'rechazado')
                        <span class="text-rose-500 ml-0.5">*</span>
                    @endif
                </flux:label>
                <flux:textarea
                    wire:model="comentario"
                    placeholder="Observaciones para el empleado..."
                    resize="none"
                    rows="3"
                />
                <flux:error name="comentario" />
                <flux:description>
                    Obligatorio si vas a rechazar. Opcional si apruebas.
                </flux:description>
            </flux:field>

            {{-- Acciones --}}
            <div class="flex items-center justify-between gap-3">
                <flux:button variant="ghost" wire:click="cerrar">
                    Cancelar
                </flux:button>

                <div class="flex gap-2">
                    <flux:button
                        variant="danger"
                        icon="x-circle"
                        wire:click="resolver('rechazado')"
                        wire:loading.attr="disabled"
                        wire:target="resolver"
                    >
                        <span wire:loading.remove wire:target="resolver">Rechazar</span>
                        <span wire:loading wire:target="resolver">Procesando…</span>
                    </flux:button>

                    <flux:button
                        variant="primary"
                        color="green"
                        icon="check-circle"
                        wire:click="resolver('aprobado')"
                        wire:loading.attr="disabled"
                        wire:target="resolver"
                    >
                        <span wire:loading.remove wire:target="resolver">Aprobar</span>
                        <span wire:loading wire:target="resolver">Procesando…</span>
                    </flux:button>
                </div>
            </div>

        </div>
    @endif

</flux:modal>
