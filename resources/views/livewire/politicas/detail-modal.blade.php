<flux:modal name="politica-detail" flyout variant="floating" class="md:w-lg">
    @if ($politica)
    <div class="flex flex-col gap-6">

        {{-- ── Header ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <flux:avatar
                    :name="$politica->role?->name"
                    :initials="strtoupper(substr($politica->role?->name ?? 'R', 0, 1))"
                    size="lg"
                />
                <div class="flex flex-col gap-0.5">
                    <flux:heading size="lg" class="leading-tight">
                        {{ $politica->role?->name ?? '—' }}
                    </flux:heading>
                    <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500 tracking-wide">
                        {{ $politica->concepto?->nombre ?? 'Sin concepto' }}
                    </span>
                </div>
            </div>

            @if ($politica->estatus)
                <flux:badge size="sm" color="green" inset="top bottom">Activa</flux:badge>
            @else
                <flux:badge size="sm" color="red" inset="top bottom">Inactiva</flux:badge>
            @endif
        </div>

        <flux:separator />

        {{-- ── Configuración general ───────────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                Configuración
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">

                {{-- Tipo de límite --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase tracking-wider text-zinc-400">Tipo</span>
                    <div class="flex items-center gap-1.5">
                        <flux:icon.bookmark-square class="size-3.5 text-zinc-400" />
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            {{ $politica->tipo_limite }}
                        </span>
                    </div>
                </div>

                {{-- Monto máximo --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase tracking-wider text-zinc-400">Tope máximo</span>
                    <div class="flex items-center gap-1.5">
                        <flux:icon.currency-dollar class="size-3.5 text-zinc-400" />
                        <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                            {{ Number::currency($politica->monto_max, in: 'MXN') }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Reglas de comprobación ─────────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                Reglas de comprobación
            </flux:subheading>

            <div class="grid grid-cols-3 gap-3">

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Libre</span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $politica->monto_libre ? Number::currency($politica->monto_libre, in: 'MXN') : '—' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Comprobante</span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $politica->monto_comprobante ? Number::currency($politica->monto_comprobante, in: 'MXN') : '—' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Factura</span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $politica->monto_factura ? Number::currency($politica->monto_factura, in: 'MXN') : '—' }}
                    </span>
                </div>

            </div>
        </div>

        {{-- ── Reglas operativas ───────────────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                Reglas operativas
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">

                {{-- SAT --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Validación SAT</span>
                    @if ($politica->valida_sat)
                        <flux:badge size="sm" color="emerald">Activa</flux:badge>
                    @else
                        <flux:badge size="sm" color="zinc">No</flux:badge>
                    @endif
                </div>

                {{-- Acumulable --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Acumulable día</span>
                    @if ($politica->acumulable_dia)
                        <flux:badge size="sm" color="blue">Sí</flux:badge>
                    @else
                        <flux:badge size="sm" color="zinc">No</flux:badge>
                    @endif
                </div>

                {{-- Excepción --}}
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5 col-span-2">
                    <span class="text-[10px] uppercase text-zinc-400">Permite excepción</span>
                    @if ($politica->permite_excepcion)
                        <flux:badge size="sm" color="amber">Con aprobación</flux:badge>
                    @else
                        <flux:badge size="sm" color="zinc">No permitido</flux:badge>
                    @endif
                </div>

            </div>
        </div>

        {{-- ── Vigencia ───────────────────────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                Vigencia
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Desde</span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $politica->vigencia_desde?->format('d/m/Y') ?? '—' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Hasta</span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $politica->vigencia_hasta?->format('d/m/Y') ?? 'Sin expiración' }}
                    </span>
                </div>
            </div>
        </div>

    </div>
    @endif
</flux:modal>
