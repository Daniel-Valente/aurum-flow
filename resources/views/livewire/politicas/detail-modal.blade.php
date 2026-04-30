<flux:modal name="politica-detail" flyout variant="floating" class="md:w-lg">
    @if ($politica)
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row gap-3 sm:justify-between items-start">
            <x-components.detail-item icon="user" label="Rol">
                {{ $politica->role?->name ?? '-' }}
            </x-components.detail-item>
            <x-components.detail-item icon="building-office" label="Centro de Costos">
                {{ $politica->concepto?->nombre ?? '-' }}
            </x-components.detail-item>
        </div>

        <div class="flex items-start gap-4 text-sm leading-tight">
            <div class="truncate fonto-light text-xs">
                Vigencia: {{ $politica->vigencia_desde?->format('Y-m-d') ?? '-' }} - {{ $politica->vigencia_hasta?->format('Y-m-d') ?? '-' }}
            </div>
        </div>

        @if ($politica->motivo)
        <div class="flex items-start gap-4 text-sm leading-tight">
            <x-components.detail-item icon="clipboard" label="Motivo">
                <flux:text style="max-width: 16rem">{{ $proyecto->motivo ?? '-' }}</flux:text>
            </x-components.detail-item>
        </div>
        @endif

        <flux:separator />

        <div class="flex items-start gap-4 text-start text-xs leading-tight justify-between">
            <x-components.detail-item icon="credit-card" label="Presupuesto Total">
                {{ Number::currency($politica->monto_max ?? 0.00, in: 'MXN') }}
            </x-components.detail-item>
            <x-components.detail-item icon="bookmark-square" label="Tipo de Límite">
                {{ $politica->tipo_limite ?? '-' }}
            </x-components.detail-item>
        </div>

        <div class="flex items-start gap-4 text-sm leading-tight">
            <x-components.detail-item icon="shield-exclamation" label="Permite Excepción">
                {{ $politica->excepcion ? 'SI' : 'NO' }}
            </x-components.detail-item>
        </div>
    </div>
    @endif
</flux:modal>
