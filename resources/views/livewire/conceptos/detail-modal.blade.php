<flux:modal name="concepto-detail" flyout variant="floating" class="md:w-lg">
    @if ($concepto)
    <div class="space-y-6">
        <div class="flex items-start gap-4">
            <div class="flex size-14 items-center justify-center rounded-full">
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar :name="$concepto->nombre" :initials="strtoupper(substr($concepto->nombre, 0, 1))" />
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate">
                                {{ $concepto->nombre }}
                            </flux:heading>
                            <flux:text class="font-mono text-sm">
                                {{ $concepto->codigo }}
                            </flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-start gap-4 text-sm leading-tight">
            <x-components.detail-item icon="clipboard" label="Descripción">
                {{ $concepto->descripcion ?? '-' }}
            </x-components.detail-item>
        </div>

        <div class="mt-2 flex items-start gap-4 text-start text-xs leading-tight justify-between">
            <div class="truncate fonto-light text-xs">
                Vigencia: {{ $concepto->vigencia_desde?->format('Y-m-d') ?? '-' }} - {{ $concepto->vigencia_hasta?->format('Y-m-d') ?? '-' }}
            </div>
            @if ($concepto->estatus)
                <flux:badge size="sm" color="green" inset="top bottom">Activo</flux:badge>
            @else
                <flux:badge size="sm" color="red" inset="top bottom">Inactivo</flux:badge>
            @endif
        </div>

        <div class="flex items-start gap-4 text-start text-xs leading-tight justify-between">
            <x-components.detail-item icon="bookmark-square" label="Tope de aplicación">
                {{ $concepto->tipo_aplicacion ?? '-' }}
            </x-components.detail-item>
            @if ($concepto->roles->isEmpty())
                <flux:badge size="sm" color="zinc" inset="top bottom">-</flux:badge>
            @else
                <div class="flex flex-wrap gap-1">
                    @foreach ($concepto->roles as $role)
                        <flux:badge size="sm" color="blue" inset="top bottom">
                            {{ $role->name }}
                        </flux:badge>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex items-start gap-4 text-start text-xs leading-tight justify-between">
            <x-components.detail-item icon="currency-dollar" label="Tope de referencia">
                {{ Number::currency($concepto->tope_referencia ?? 0.00, in: 'MXN') }}
            </x-components.detail-item>
            <flux:badge size="sm" color="blue" inset="top bottom">
                {{ $concepto->categoria ?? '-' }}
            </flux:badge>
        </div>

        <flux:separator />

        <div class="flex items-start gap-4 text-start text-xs leading-tight">
            <x-components.detail-item icon="document-currency-dollar" label="Se requiere Factura (PDF/XML)">
                {{ $concepto->requiere_factura ? 'SI' : 'NO' }}
            </x-components.detail-item>
        </div>

        <div class="flex items-start gap-4 text-start text-xs leading-tight">
            <x-components.detail-item icon="document-currency-dollar" label="Se requiere UUID de Factura">
                {{ $concepto->requiere_uuid ? 'SI' : 'NO' }}
            </x-components.detail-item>
        </div>

        <div class="flex items-start gap-4 text-start text-xs leading-tight">
            <x-components.detail-item icon="document-currency-dollar" label="Se requiere Comprobante">
                {{ $concepto->requiere_comprobante ? 'SI' : 'NO' }}
            </x-components.detail-item>
        </div>

        <div class="flex items-start gap-4 text-start text-xs leading-tight">
            <x-components.detail-item icon="document-currency-dollar" label="Permitir sin Factura">
                {{ $concepto->permite_sin_factura ? 'SI' : 'NO' }}
            </x-components.detail-item>
        </div>

        <div class="flex items-start gap-4 text-start text-xs leading-tight">
            <x-components.detail-item icon="document-currency-dollar" label="Aplica IVA">
                {{ $concepto->aplica_iva ? 'SI' : 'NO' }}
            </x-components.detail-item>
        </div>

        <div class="flex items-start gap-4 text-start text-xs leading-tight">
            <x-components.detail-item icon="calendar" label="Es Acumulable en Día">
                {{ $concepto->acumulable_dia ? 'SI' : 'NO' }}
            </x-components.detail-item>
        </div>
    </div>
    @endif
</flux:modal>
