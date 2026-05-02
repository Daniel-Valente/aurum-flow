<flux:modal name="concepto-detail" flyout variant="floating" class="md:w-lg">
    @if ($concepto)
    <div class="flex flex-col gap-6">

        {{-- ── Header ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <flux:avatar
                    :name="$concepto->nombre"
                    :initials="strtoupper(substr($concepto->nombre, 0, 1))"
                    size="lg"
                />
                <div class="flex flex-col gap-0.5">
                    <flux:heading size="lg" class="leading-tight">
                        {{ $concepto->nombre }}
                    </flux:heading>
                    <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500 tracking-wide">
                        {{ $concepto->codigo }}
                    </span>
                </div>
            </div>

            @if ($concepto->estatus)
                <flux:badge size="sm" color="green" inset="top bottom">Activo</flux:badge>
            @else
                <flux:badge size="sm" color="red" inset="top bottom">Inactivo</flux:badge>
            @endif
        </div>

        {{-- ── Descripción ─────────────────────────────────────────────────── --}}
        @if ($concepto->descripcion)
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
                {{ $concepto->descripcion }}
            </flux:text>
        @endif

        <flux:separator />

        {{-- ── Clasificación ───────────────────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                Clasificación
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">

                {{-- Tipo de aplicación --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        Tipo
                    </span>
                    <div class="flex items-center gap-1.5">
                        <flux:icon.bookmark-square class="size-3.5 text-zinc-400 dark:text-zinc-500" />
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            {{ $concepto->tipo_aplicacion ?? '-' }}
                        </span>
                    </div>
                </div>

                {{-- Categoría --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        Categoría
                    </span>
                    <div class="flex items-center gap-1.5">
                        <flux:icon.tag class="size-3.5 text-zinc-400 dark:text-zinc-500" />
                        @if ($concepto->categoria)
                            <flux:badge size="sm" color="blue" inset="top bottom">{{ $concepto->categoria }}</flux:badge>
                        @else
                            <span class="text-sm text-zinc-400">—</span>
                        @endif
                    </div>
                </div>

                {{-- Tope de referencia --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        Tope de referencia
                    </span>
                    <div class="flex items-center gap-1.5">
                        <flux:icon.currency-dollar class="size-3.5 text-zinc-400 dark:text-zinc-500" />
                        <span class="text-sm font-medium font-mono text-zinc-700 dark:text-zinc-200">
                            {{ $concepto->tope_referencia
                                ? Number::currency($concepto->tope_referencia, in: 'MXN')
                                : '—' }}
                        </span>
                    </div>
                </div>

                {{-- Aplica IVA --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        IVA
                    </span>
                    <div class="flex items-center gap-1.5">
                        <flux:icon.receipt-percent class="size-3.5 text-zinc-400 dark:text-zinc-500" />
                        @if ($concepto->aplica_iva)
                            <flux:badge size="sm" color="emerald" inset="top bottom">Aplica</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc" inset="top bottom">No aplica</flux:badge>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Vigencia ─────────────────────────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                Vigencia
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        Desde
                    </span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $concepto->vigencia_desde?->format('d/m/Y') ?? '—' }}
                    </span>
                </div>
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        Hasta
                    </span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $concepto->vigencia_hasta?->format('d/m/Y') ?? 'Sin expiración' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ── Roles con acceso ─────────────────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                Roles con acceso
            </flux:subheading>

            @if ($concepto->roles->isEmpty())
                <div class="flex items-center gap-2 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <flux:icon.users class="size-3.5 text-zinc-400" />
                    <span class="text-xs text-zinc-400 dark:text-zinc-500">
                        Disponible para todos los roles
                    </span>
                </div>
            @else
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($concepto->roles as $role)
                        <flux:badge size="sm" color="blue" inset="top bottom">
                            {{ $role->name }}
                        </flux:badge>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
    @endif
</flux:modal>
