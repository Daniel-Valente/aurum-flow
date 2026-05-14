<flux:modal name="proyecto-detail" flyout variant="floating" class="md:w-lg">
    @if ($proyecto)
    <div class="flex flex-col gap-6">

        {{-- ── Header ───────────────────────────────────────────── --}}
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <flux:avatar
                    :name="$proyecto->nombre"
                    :initials="strtoupper(substr($proyecto->nombre, 0, 1))"
                    size="lg"
                />

                <div class="flex flex-col gap-0.5">
                    <flux:heading size="lg" class="leading-tight">
                        {{ $proyecto->nombre }}
                    </flux:heading>
                    <span class="text-xs font-mono text-zinc-400 tracking-wide">
                        {{ $proyecto->codigo }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <flux:badge
                    size="sm"
                    color="{{
                        $proyecto->tipo === 'Proyecto' ? 'orange' :
                        ($proyecto->tipo === 'Ruta' ? 'purple' : 'pink')
                    }}"
                >
                    {{ $proyecto->tipo }}
                </flux:badge>

                @if ($proyecto->estatus)
                    <flux:badge size="sm" color="green">Activo</flux:badge>
                @else
                    <flux:badge size="sm" color="red">Inactivo</flux:badge>
                @endif
            </div>
        </div>

        {{-- ── Descripción ─────────────────────────────────────── --}}
        @if ($proyecto->descripcion)
            <flux:text class="text-sm text-zinc-500 leading-relaxed">
                {{ $proyecto->descripcion }}
            </flux:text>
        @endif

        <flux:separator />

        {{-- ── Configuración ───────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                Configuración
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">

                {{-- Centro de costo --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50  dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Referencia contable</span>
                    <span class="text-sm text-zinc-700">
                        {{ ($proyecto->centroCosto?->nombre ?: $proyecto->centroCosto?->cuenta_contable) ?? '—' }}
                    </span>
                </div>

                {{-- Responsable --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50  dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Responsable</span>
                    <span class="text-sm text-zinc-700">
                        {{ $proyecto->responsable?->nombre_completo ?? '—' }}
                    </span>
                </div>

                {{-- Cliente --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50  dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Cliente</span>
                    <span class="text-sm text-zinc-700">
                        {{ $proyecto->cliente ?? '—' }}
                    </span>
                </div>

                {{-- Presupuesto --}}
                <div class="col-span-2 flex flex-col gap-1 rounded-lg bg-zinc-50  dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Presupuesto total</span>
                    <span class="text-sm font-mono text-zinc-700">
                        {{ Number::currency($proyecto->presupuesto_total ?? 0, in: 'MXN') }}
                    </span>
                </div>

            </div>
        </div>

        {{-- ── Fechas ───────────────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                Vigencia
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50  dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Inicio</span>
                    <span class="text-sm font-mono">
                        {{ $proyecto->fecha_inicio?->format('d/m/Y') ?? '—' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50  dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Fin</span>
                    <span class="text-sm font-mono">
                        {{ $proyecto->fecha_fin?->format('d/m/Y') ?? '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ── Ubicación ───────────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                Ubicación
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50  dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Ciudad</span>
                    <span class="text-sm">{{ $proyecto->ciudad ?? '—' }}</span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50  dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Estado</span>
                    <span class="text-sm">{{ $proyecto->estado ?? '—' }}</span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50  dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Región</span>
                    <span class="text-sm">{{ $proyecto->region ?? '—' }}</span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50  dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">País</span>
                    <span class="text-sm">{{ $proyecto->pais ?? '—' }}</span>
                </div>

            </div>
        </div>

    </div>
    @endif
</flux:modal>
