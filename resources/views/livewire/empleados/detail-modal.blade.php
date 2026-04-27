<flux:modal name="empleado-detail" flyout variant="floating" class="md:w-lg">
    @if ($empleado)
    <div class="space-y-6">
        <div class="flex items-start gap-4">
            <div class="flex size-14 items-center justify-center rounded-full">
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar
                            :name="$empleado->nombre_completo"
                            :initials="$empleado->user?->initials()"
                        />

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate">{{ $empleado->nombre_completo }}</flux:heading>
                            <flux:text class="truncate">{{ $empleado->user?->email }}</flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-start gap-4 text-start text-xs leading-tight justify-between">
            <flux:text class="truncate font-light text-xs">{{ $empleado->puesto }} - {{ $empleado->area?->nombre }}</flux:text>
            @if ($empleado->estatus)
            <flux:badge size="sm" color="green" inset="top bottom">Activo</flux:badge>
            @else
            <flux:badge size="sm" color="red" inset="top bottom">Inactivo</flux:badge>
            @endif
        </div>
        <div class="flex items-start gap-4 text-start text-xs leading-tight justify-between">
            <x-components.detail-item icon="building-office" label="Centro de Costo">
                {{ $empleado->centroCosto?->nombre ?? '-' }}
            </x-components.detail-item>
            <flux:badge size="sm" color="blue" inset="top bottom">
                {{ $empleado->user?->roles->first()->name ?? 'Sin rol' }}
            </flux:badge>
        </div>

        <div class="space-y-3">
            <flux:separator text="Información Personal" />

            <div class="flex flex-col sm:flex-row gap-3 sm:justify-between items-start">
                <x-components.detail-item icon="phone" label="Teléfono">
                    {{ $empleado->telefono ?? '-' }}
                </x-components.detail-item>
                <x-components.detail-item icon="calendar" label="Fecha de ingreso">
                    {{ $empleado->fecha_ingreso?->isoFormat('D [de] MMMM [de] YYYY') ?? '-' }}
                </x-components.detail-item>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 sm:justify-between items-start">
                <x-components.detail-item icon="identification" label="RFC">
                    {{ $empleado->rfc ?? '-' }}
                </x-components.detail-item>
                <x-components.detail-item icon="identification" label="CURP">
                    {{ $empleado->curp ?? '-' }}
                </x-components.detail-item>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 sm:justify-between items-start">
                <x-components.detail-item icon="identification" label="NSS">
                    {{ $empleado->nss ?? '-' }}
                </x-components.detail-item>
            </div>
        </div>

        <div class="space-y-3">
            <flux:separator text="Información Financiera" />

            <div class="flex flex-col sm:flex-row gap-3 sm:justify-between items-start">
                <x-components.detail-item icon="hashtag" label="Número de nómina">
                    <span class="font-mono">{{ $empleado->numero_nomina?? '-' }}</span>
                </x-components.detail-item>
                <x-components.detail-item icon="building-library" label="Banco">
                    {{ $empleado->banco_nomina ?? '-' }}
                </x-components.detail-item>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 sm:justify-between items-start">
                <x-components.detail-item icon="credit-card" label="Cuenta">
                    <span class="font-mono">
                        {{ $empleado->cuenta_nomina ?? '-' }}
                    </span>
                </x-components.detail-item>
                <x-components.detail-item icon="identification" label="CLABE">
                    <span class="font-mono">{{ $empleado->clabe_nomina ?? '-' }}</span>
                </x-components.detail-item>
            </div>
        </div>
    </div>
    @endif
</flux:modal>
