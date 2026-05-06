<div class="space-y-6">
    @php
        $steps = [
            1 => ['label' => 'Borrador',   'icon' => 'clipboard-document'],
            2 => ['label' => 'Aprobación', 'icon' => 'check-circle'],
            3 => ['label' => 'Viaje',      'icon' => 'globe-americas'],
        ];

        function stepStatus($current, $step, $estatus) {
            // Si ya está comprobado, todo es complete
            if ($estatus === 'Comprobado') {
                return 'complete';
            }

            return $current > $step
                ? 'complete'
                : ($current === $step ? 'current' : 'incomplete');
        }
    @endphp

    <flux:timeline horizontal>
        @foreach ($steps as $number => $step)
            <flux:timeline.item status="{{ stepStatus($stepActual, $number, $solicitud->estatus) }}">

                <flux:timeline.indicator>
                    <flux:icon :name="$step['icon']" variant="micro" />
                </flux:timeline.indicator>

                <flux:timeline.content>
                    <flux:heading>{{ $step['label'] }}</flux:heading>
                </flux:timeline.content>

            </flux:timeline.item>
        @endforeach
    </flux:timeline>

    <div class="pt-4">
        @if ($stepActual === 1)
            @include('livewire.solicitudes.detail.partials.step1')
        @elseif ($stepActual === 2)
            @include('livewire.solicitudes.detail.partials.step2')
        @elseif ($stepActual === 3)
            @include('livewire.solicitudes.detail.partials.step3')
        @endif
    </div>

    {{-- Modal de justificaciones — solo en step 1 --}}
    @if ($stepActual === 1)
        @include('livewire.solicitudes.detail.partials.justificaciones-modal')
    @endif
</div>
