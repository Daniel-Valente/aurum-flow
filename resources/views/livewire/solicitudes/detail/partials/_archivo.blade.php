{{-- Aviso excepción --}}
@if ($gasto['comprobante_requerido'] === 'excede')
    <div class="flex items-start gap-2 rounded-md bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800 px-3 py-2">
        <flux:icon.exclamation-triangle class="size-4 text-amber-500 shrink-0 mt-0.5" />
        <p class="text-xs text-amber-700 dark:text-amber-300">
            Este gasto fue aprobado por excepción. Sube el comprobante que tengas disponible.
        </p>
    </div>
@endif

{{-- ── CFDI (XML + PDF opcional, uno solo) ──────────────────────────────── --}}
@if ($tipoComprobante === 'factura')
    <div class="space-y-3">

        {{-- Upload múltiple de XMLs --}}
        <flux:field>
            <flux:label badge="Requerido">Archivos XML (CFDI)</flux:label>
            <flux:file-upload
                wire:model="archivosCfdi"
                multiple
            >
                <flux:file-upload.dropzone
                    heading="Arrastra uno o varios XML aquí"
                    text="Puedes cargar múltiples facturas a la vez — solo .xml"
                    with-progress
                    inline
                />
            </flux:file-upload>
            <flux:error name="archivosCfdi" />
        </flux:field>

        {{-- Listado de CFDIs parseados --}}
        @if (!empty($archivosCfdi))
            <div class="space-y-2">
                <p class="text-xs font-medium text-zinc-500">
                    {{ count($archivosCfdi) }} CFDI(s) detectado(s):
                </p>

                @foreach ($archivosCfdi as $idx => $cfdi)
                    @php
                        $tieneError = !empty($cfdi['error']);
                    @endphp

                    <div class="rounded-lg border {{ $tieneError ? 'border-rose-300 dark:border-rose-700 bg-rose-50 dark:bg-rose-950/30' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800' }} p-3 space-y-2">

                        {{-- Fila principal: datos del CFDI --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <flux:icon.document-text class="size-4 {{ $tieneError ? 'text-rose-400' : 'text-emerald-500' }} shrink-0" />
                                <div class="min-w-0">
                                    @if ($tieneError)
                                        <p class="text-xs font-medium text-rose-600 dark:text-rose-400">
                                            {{ $cfdi['error'] }}
                                        </p>
                                        <p class="text-[10px] text-zinc-400 truncate">
                                            {{ is_object($cfdi['xml']) ? $cfdi['xml']->getClientOriginalName() : '—' }}
                                        </p>
                                    @else
                                        <p class="text-xs font-medium text-zinc-800 dark:text-zinc-100">
                                            {{ $cfdi['emisor'] }}
                                        </p>
                                        <p class="text-[10px] font-mono text-zinc-400">
                                            {{ strtoupper(substr($cfdi['uuid'], 0, 8)) }}…
                                            · {{ $cfdi['fecha'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                @if (!$tieneError)
                                    <span class="font-mono text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                        {{ Number::currency($cfdi['monto'], in: 'MXN') }}
                                    </span>
                                @endif
                                <flux:button
                                    size="xs"
                                    variant="ghost"
                                    icon="x-mark"
                                    wire:click="removeCfdi({{ $idx }})"
                                    title="Quitar"
                                />
                            </div>
                        </div>

                        {{-- PDF opcional por CFDI --}}
                        @if (!$tieneError)
                            <div class="pl-6">
                                @if (isset($archivosCfdi[$idx]['pdf']) && $archivosCfdi[$idx]['pdf'])
                                    <div class="flex items-center gap-2 text-xs text-zinc-500">
                                        <flux:icon.paper-clip class="size-3" />
                                        <span class="truncate">
                                            {{ $archivosCfdi[$idx]['pdf']->getClientOriginalName() }}
                                        </span>
                                        <flux:button
                                            size="xs"
                                            variant="ghost"
                                            icon="x-mark"
                                            wire:click="$set('archivosCfdi.{{ $idx }}.pdf', null)"
                                        />
                                    </div>
                                @else
                                    <flux:field>
                                        <flux:file-upload wire:model="archivosCfdi.{{ $idx }}.pdf">
                                            <flux:button size="xs" variant="ghost" icon="paper-clip">
                                                Adjuntar PDF (opcional)
                                            </flux:button>
                                        </flux:file-upload>
                                    </flux:field>
                                @endif
                            </div>
                        @endif

                    </div>
                @endforeach

                {{-- Subtotal CFDIs válidos --}}
                @php
                    $totalCfdi = collect($archivosCfdi)
                        ->filter(fn($c) => empty($c['error']) && ($c['monto'] ?? 0) > 0)
                        ->sum(fn($c) => (float) $c['monto']);
                @endphp
                @if ($totalCfdi > 0)
                    <div class="flex justify-end">
                        <span class="text-xs font-mono text-zinc-500">
                            Total facturas:
                            <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ Number::currency($totalCfdi, in: 'MXN') }}
                            </span>
                        </span>
                    </div>
                @endif

            </div>
        @endif

    </div>
{{-- ── Tickets (múltiples archivos + monto por archivo) ─────────────────── --}}
@elseif ($tipoComprobante === 'pdf')
    <div
        class="space-y-3"
        x-data="{
            // Proxy reactivo sobre la propiedad Livewire
            get archivos() { return $wire.archivosComprobantes },
        }"
    >
        {{-- Upload múltiple --}}
        <flux:field>
            <flux:label badge="Requerido">Tickets / Recibos</flux:label>
            <flux:file-upload wire:model="archivosComprobantes" multiple>
                <flux:file-upload.dropzone
                    heading="Arrastra uno o varios tickets aquí"
                    text="PDF, JPG, PNG — puedes seleccionar varios a la vez"
                    with-progress
                    inline
                />
            </flux:file-upload>
            <flux:error name="archivosComprobantes" />
            <flux:error name="archivosComprobantes.*" />
        </flux:field>

        {{-- Monto por archivo — aparece conforme se cargan --}}
        @if (!empty($archivosComprobantes))
            <div class="space-y-2">
                <p class="text-xs font-medium text-zinc-500">
                    Indica el monto de cada ticket:
                </p>

                @foreach ($archivosComprobantes as $idx => $archivo)
                    <div class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2">
                        <flux:icon.document-text class="size-4 text-zinc-400 shrink-0" />

                        <span class="text-xs text-zinc-600 dark:text-zinc-300 truncate flex-1 min-w-0">
                            {{ $archivo->getClientOriginalName() }}
                        </span>

                        <div class="flex items-center gap-1 shrink-0">
                            <span class="text-xs text-zinc-400">$</span>
                            <flux:input
                                wire:model.live="montosComprobantes.{{ $idx }}"
                                type="number"
                                step="0.01"
                                min="0.01"
                                placeholder="0.00"
                                class="w-28"
                                size="sm"
                            />
                        </div>

                        <flux:button
                            size="xs"
                            variant="ghost"
                            icon="x-mark"
                            wire:click="removeArchivo({{ $idx }})"
                            title="Quitar"
                        />
                    </div>
                    <flux:error name="montosComprobantes.{{ $idx }}" />
                @endforeach

                {{-- Subtotal visual --}}
                @php
                    $subtotal = collect($montosComprobantes)
                        ->filter(fn($m) => is_numeric($m) && $m > 0)
                        ->sum(fn($m) => (float) $m);
                @endphp
                @if ($subtotal > 0)
                    <div class="flex justify-end">
                        <span class="text-xs font-mono text-zinc-500">
                            Total a registrar:
                            <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ Number::currency($subtotal, in: 'MXN') }}
                            </span>
                        </span>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
