<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Autorizaciones</flux:heading>
            <flux:subheading>
                Gestiona autorizaciones, excepciones y comprobaciones de gastos
            </flux:subheading>
        </div>
    </div>

    <flux:tabs wire:model.live="tab">
        <flux:tab name="solicitudes" icon="document-check">
            Solicitudes
        </flux:tab>

        <flux:tab name="excepciones" icon="exclamation-triangle">
            Excepciones
            @if ($totalExcepciones > 0)
                <flux:badge color="red" size="sm" class="ml-1">{{ $totalExcepciones }}</flux:badge>
            @endif
        </flux:tab>

        @if ($puedeValidarComprobantes)
            <flux:tab name="comprobantes" icon="document-magnifying-glass">
                Comprobaciones
                @if ($totalComprobantes > 0)
                    <flux:badge color="yellow" size="sm" class="ml-1">{{ $totalComprobantes }}</flux:badge>
                @endif
            </flux:tab>
        @endif

        @if ($puedeConciliarComprobacion)
            <flux:tab name="tarjeta" icon="credit-card">
                Tarjetas corporativas
            </flux:tab>
        @endif
    </flux:tabs>

    @if ($tab === 'solicitudes')
        @include('livewire.autorizaciones.partials.solicitudes')
    @endif

    @if ($tab === 'excepciones')
        @include('livewire.autorizaciones.partials.excepciones')
    @endif

    @if ($tab === 'comprobantes' && $puedeValidarComprobantes)
        @include('livewire.autorizaciones.partials.comprobantes')
    @endif

    @if ($tab === 'tarjeta' && $puedeConciliarComprobacion)
        @include('livewire.autorizaciones.partials.conciliar')
    @endif

    @livewire('autorizaciones.detail-modal')
    @livewire('autorizaciones.excepcion-modal')
    @livewire('autorizaciones.comprobante-modal')
</div>
