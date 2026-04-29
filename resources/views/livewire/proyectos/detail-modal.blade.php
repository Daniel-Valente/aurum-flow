<flux:modal name="proyecto-detail" flyout variant="floating" class="md:w-lg">
    @if ($proyecto)
    <div class="space-y-6">
        <div class="flex items-start gap-4">
            <div class="flex size-14 items-center justify-center rounded-full">
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar :name="$proyecto->nombre" :initials="strtoupper(substr($proyecto->nombre, 0, 1))" />
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate">
                                {{ $proyecto->nombre }}
                            </flux:heading>
                            <div class=" mt-1 flex items-start gap-4 text-start justify-between">
                                <flux:text class="font-mono text-sm flex-1">
                                    {{ $proyecto->codigo }}
                                </flux:text>
                                <flux:badge
                                    color="{{
                                        $proyecto->tipo === 'Proyecto' ? 'orange' :
                                        ($proyecto->tipo === 'Ruta' ? 'purple' : 'pink')
                                    }}"
                                    size="sm"
                                    inset="top bottom"
                                >
                                    {{ $proyecto->tipo }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-start gap-4 text-sm leading-tight">
            <x-components.detail-item icon="cube-transparent" label="Prioridad">
                {{ $proyecto->prioridad }}
            </x-components.detail-item>
        </div>

        <div class="flex items-start gap-4 text-sm leading-tight">
            <x-components.detail-item icon="clipboard" label="Descripción">
                <flux:text style="max-width: 16rem">{{ $proyecto->descripcion ?? '-' }}</flux:text>
            </x-components.detail-item>
        </div>

        <div class="mt-2 flex items-start gap-4 text-start text-xs leading-tight justify-between">
            <div class="truncate font-light text-sm">
                {{ $proyecto->fecha_inicio?->format('Y-m-d') ?? '-' }} a {{ $proyecto->fecha_fin?->format('Y-m-d') ?? '-' }}
            </div>
            @if ($proyecto->estatus)
                <flux:badge size="sm" color="green" inset="top bottom">Activo</flux:badge>
            @else
                <flux:badge size="sm" color="red" inset="top bottom">Inactivo</flux:badge>
            @endif
        </div>

        <flux:separator />

        <div class="spayce-y-3">
            <div class="flex flex-col sm:flex-row gap-3 sm:justify-between items-start">
                <x-components.detail-item icon="user" label="Cliente">
                    {{ $proyecto->cliente ?? '-' }}
                </x-components.detail-item>
                <x-components.detail-item icon="building-office" label="Centro de Costos">
                    {{ $proyecto->centroCosto?->nombre ?? '-' }}
                </x-components.detail-item>
            </div>
        </div>

        <div class="spayce-y-3">
            <div class="flex flex-col sm:flex-row gap-3 sm:justify-between items-start">
                <x-components.detail-item icon="user" label="Responsable">
                    {{ $proyecto->responsable?->nombre_completo ?? '-' }}
                </x-components.detail-item>
                <x-components.detail-item icon="credit-card" label="Presupuesto Total">
                    {{ $proyecto->presupuesto_total ?? '0.00' }}
                </x-components.detail-item>
            </div>
        </div>

        <flux:separator />

        <div class="spayce-y-3">
            <div class="flex flex-col sm:flex-row gap-3 sm:justify-between items-start">
                <x-components.detail-item icon="globe-americas" label="Ciudad">
                    {{ $proyecto->ciudad ?? '-' }}
                </x-components.detail-item>
                <x-components.detail-item icon="globe-americas" label="Estado">
                    {{ $proyecto->estado ?? '-' }}
                </x-components.detail-item>
            </div>
        </div>

        <div class="spayce-y-3">
            <div class="flex flex-col sm:flex-row gap-3 sm:justify-between items-start">
                <x-components.detail-item icon="globe-americas" label="Región">
                    {{ $proyecto->region ?? '-' }}
                </x-components.detail-item>
                <x-components.detail-item icon="globe-americas" label="País">
                    ${{ $proyecto->pais ?? '-' }}
                </x-components.detail-item>
            </div>
        </div>
    </div>
    @endif
</flux:modal>
