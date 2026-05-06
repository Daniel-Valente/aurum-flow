<flux:modal name="excepcion-detail" flyout variant="floating" class="md:w-lg">
    @if ($excepcion)
        @php
            $gasto     = $excepcion->gasto;
            $solicitud = $gasto->solicitud;
            $nivel     = $excepcion->nivel;
        @endphp

        <div class="flex flex-col gap-6">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-3">
                <div class="flex flex-col gap-0.5">
                    <flux:heading size="lg">Excepción — Nivel {{ $nivel }}</flux:heading>
                    <span class="text-xs font-mono text-zinc-400">{{ $solicitud->folio }}</span>
                </div>
                <flux:badge color="{{ $nivel === 1 ? 'yellow' : 'orange' }}" size="sm">
                    N{{ $nivel }} — {{ $nivel === 1 ? 'Gerente' : 'Administración' }}
                </flux:badge>
            </div>

            <flux:separator />

            {{-- Info del empleado --}}
            <div>
                <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                    Solicitante
                </flux:subheading>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Empleado</span>
                        <span class="text-sm font-medium">{{ $solicitud->empleado->nombre_completo }}</span>
                    </div>
                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Proyecto</span>
                        <span class="text-sm">{{ $solicitud->proyecto->nombre ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Info del gasto excedido --}}
            <div>
                <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                    Gasto excedido
                </flux:subheading>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Concepto</span>
                        <span class="text-sm font-medium">{{ $gasto->concepto->nombre }}</span>
                    </div>
                    <div class="flex flex-col gap-1 rounded-lg bg-rose-50 dark:bg-rose-900/20 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-rose-400">Monto registrado</span>
                        <span class="text-sm font-mono font-bold text-rose-600">
                            {{ Number::currency($gasto->monto, in: 'MXN') }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                    Justificación del exceso
                </flux:subheading>

                <div class="rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-sm">
                        {{ $gasto->detalle->justificacion_exceso ?? 'Sin justificación' }}
                    </span>
                </div>
            </div>

            {{-- Si es N2, muestra quién aprobó en N1 --}}
            @if ($nivel === 2)
                @php
                    $excN1 = $gasto->excepciones()->where('nivel', 1)->where('estatus', 'aprobado')->first();
                @endphp
                @if ($excN1)
                    <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2.5
                                dark:border-emerald-800 dark:bg-emerald-900/20">
                        <flux:icon.check-circle class="size-4 text-emerald-500 shrink-0" />
                        <flux:text size="sm" class="text-emerald-700 dark:text-emerald-400">
                            Aprobado en N1 por
                            <span class="font-semibold">{{ $excN1->aprobador->name ?? '—' }}</span>
                            el {{ $excN1->resuelto_en?->format('d/m/Y H:i') }}
                        </flux:text>
                    </div>
                @endif
            @endif

            <flux:separator />

            {{-- Acciones --}}
            <div>
                @if ($confirmandoRechazo)
                    <div class="flex flex-col gap-3">
                        <flux:field>
                            <flux:label badge="Requerido">Motivo de rechazo</flux:label>
                            <flux:textarea
                                wire:model="comentario"
                                placeholder="Explica por qué rechazas esta excepción..."
                                resize="none"
                                rows="3"
                            />
                            <flux:error name="comentario" />
                        </flux:field>
                        <div class="flex justify-between gap-2">
                            <flux:button variant="ghost" wire:click="cancelarRechazo">
                                Cancelar
                            </flux:button>
                            <flux:button variant="danger" icon="x-mark"
                                wire:click="rechazar"
                                wire:loading.attr="disabled" wire:target="rechazar">
                                <span wire:loading.remove wire:target="rechazar">Confirmar rechazo</span>
                                <span wire:loading wire:target="rechazar">Rechazando…</span>
                            </flux:button>
                        </div>
                    </div>
                @else
                    <div class="flex justify-between gap-3">
                        <flux:button variant="danger" icon="x-mark" wire:click="iniciarRechazo">
                            Rechazar excepción
                        </flux:button>
                        <flux:button variant="primary" color="green" icon="check"
                            wire:click="aprobar"
                            wire:loading.attr="disabled" wire:target="aprobar">
                            <span wire:loading.remove wire:target="aprobar">
                                {{ $nivel === 1 ? 'Aprobar y escalar a N2' : 'Aprobar definitivamente' }}
                            </span>
                            <span wire:loading wire:target="aprobar">Procesando…</span>
                        </flux:button>
                    </div>
                @endif
            </div>

        </div>
    @endif
</flux:modal>
