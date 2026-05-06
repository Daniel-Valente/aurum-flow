<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Autorizaciones</flux:heading>
            <flux:subheading>Gestiona la revisión y aprobación de solicitudes de viáticos</flux:subheading>
        </div>
    </div>

    {{-- TABS --}}
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
                Comprobantes
                @if ($totalComprobantes > 0)
                    <flux:badge color="yellow" size="sm" class="ml-1">{{ $totalComprobantes }}</flux:badge>
                @endif
            </flux:tab>
        @endif
    </flux:tabs>

    {{-- ── TAB: SOLICITUDES ── --}}
    @if ($tab === 'solicitudes')

        @include('livewire.autorizaciones.partials.solicitudes')

    @endif

    {{-- ── TAB: EXCEPCIONES ── --}}
    @if ($tab === 'excepciones')

        @include('livewire.autorizaciones.partials.excepciones')

    @endif

    {{-- ── TAB: COMPROBANTES (solo finanzas) ── --}}
    @if ($tab === 'comprobantes' && $puedeValidarComprobantes)

        @include('livewire.autorizaciones.partials.comprobantes')

    @endif

    @livewire('autorizaciones.detail-modal')
    @livewire('autorizaciones.excepcion-modal')
    @livewire('autorizaciones.comprobante-modal')
</div>
