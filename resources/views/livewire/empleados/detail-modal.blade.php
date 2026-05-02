<flux:modal name="empleado-detail" flyout variant="floating" class="md:w-lg">
    @if ($empleado)
    <div class="flex flex-col gap-6">

        {{-- ── Header ─────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <flux:avatar
                    :name="$empleado->nombre_completo"
                    :initials="$empleado->user?->initials()"
                    size="lg"
                />

                <div class="flex flex-col gap-0.5">
                    <flux:heading size="lg" class="leading-tight">
                        {{ $empleado->nombre_completo }}
                    </flux:heading>
                    <span class="text-xs text-zinc-400">
                        {{ $empleado->user?->email }}
                    </span>
                </div>
            </div>

            @if ($empleado->estatus)
                <flux:badge size="sm" color="green">Activo</flux:badge>
            @else
                <flux:badge size="sm" color="red">Inactivo</flux:badge>
            @endif
        </div>

        {{-- ── Contexto laboral ─────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-3">

            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                <span class="text-[10px] uppercase text-zinc-400">Puesto</span>
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    {{ $empleado->puesto ?? '—' }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                <span class="text-[10px] uppercase text-zinc-400">Área</span>
                <span class="text-sm text-zinc-700 dark:text-zinc-200">
                    {{ $empleado->area?->nombre ?? '—' }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                <span class="text-[10px] uppercase text-zinc-400">Centro de costo</span>
                <span class="text-sm text-zinc-700 dark:text-zinc-200">
                    {{ $empleado->centroCosto?->nombre ?? '—' }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                <span class="text-[10px] uppercase text-zinc-400">Rol</span>
                <flux:badge size="sm" color="blue">
                    {{ $empleado->user?->roles->first()->name ?? 'Sin rol' }}
                </flux:badge>
            </div>

        </div>

        <flux:separator />

        {{-- ── Información personal ─────────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                Información personal
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Teléfono</span>
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">
                        {{ $empleado->telefono ?? '—' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Fecha ingreso</span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $empleado->fecha_ingreso?->format('d/m/Y') ?? '—' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">RFC</span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $empleado->rfc ?? '—' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">CURP</span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $empleado->curp ?? '—' }}
                    </span>
                </div>

                <div class="col-span-2 flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">NSS</span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $empleado->nss ?? '—' }}
                    </span>
                </div>

            </div>
        </div>

        {{-- ── Información financiera ───────────────────────────────── --}}
        <div>
            <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                Información financiera
            </flux:subheading>

            <div class="grid grid-cols-2 gap-3">

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Nómina</span>
                    <span class="text-sm font-mono">
                        {{ $empleado->numero_nomina ?? '—' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Banco</span>
                    <span class="text-sm">
                        {{ $empleado->banco_nomina ?? '—' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">Cuenta</span>
                    <span class="text-sm font-mono">
                        {{ $empleado->cuenta_nomina ?? '—' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                    <span class="text-[10px] uppercase text-zinc-400">CLABE</span>
                    <span class="text-sm font-mono">
                        {{ $empleado->clabe_nomina ?? '—' }}
                    </span>
                </div>

            </div>

            {{-- ── Tarjeta corporativa ───────────────────────────── --}}
            <div class="mt-4 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 space-y-3">

                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase text-zinc-400">Tarjeta corporativa</span>

                    @if ($empleado->tarjeta_credito_corporativa_asignada)
                        <flux:badge size="sm" color="emerald">Asignada</flux:badge>
                    @else
                        <flux:badge size="sm" color="zinc">No asignada</flux:badge>
                    @endif
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-[10px] uppercase text-zinc-400">Límite de crédito</span>
                    <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                        {{ $empleado->limite_credito_tarjeta
                            ? Number::currency($empleado->limite_credito_tarjeta, in: 'MXN')
                            : '—' }}
                    </span>
                </div>

            </div>

        </div>

    </div>
    @endif
</flux:modal>
