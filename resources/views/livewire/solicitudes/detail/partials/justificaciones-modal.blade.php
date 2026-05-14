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
            @if ($detalle['semaforo'] === 'excedido' && empty($detalle['justificacion_exceso']) && !$detalle['requiere_extension_tarjeta'])
                <div class="rounded-lg border border-rose-200 bg-rose-50 dark:border-rose-800 dark:bg-rose-900/10 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-sm">{{ $detalle['concepto_nombre'] }}</span>
                        <div class="flex items-center gap-2 text-xs font-mono">
                            <span class="text-zinc-400 line-through">
                                {{ Number::currency($detalle['limite_politica'], in: 'MXN') }}
                                -
                                <span class="text-xs">
                                    {{ $detalle['tipo_limite_politica'] ?? '-' }}
                                </span>
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
