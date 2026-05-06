<flux:card>
    <div class="flex flex-col gap-4">

        <div class="flex items-start justify-between gap-3">
            <div class="flex flex-col gap-0.5">
                <flux:heading size="lg">{{ $solicitud->proyecto_nombre ?? $solicitud->proyecto?->nombre }}</flux:heading>
                <span class="text-xs font-mono text-zinc-400">{{ $solicitud->folio }}</span>
            </div>
            <flux:badge color="{{ $badgeColor }}" size="sm">{{ $badge }}</flux:badge>
        </div>

        @if ($stepActual === 2)
            <div class="flex items-center gap-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-3 py-2.5">
                <flux:icon.clock class="size-4 text-amber-500 shrink-0" />
                <span class="text-sm text-amber-700 dark:text-amber-400">
                    Tu solicitud está en revisión. Se requieren
                    <span class="font-semibold">{{ $aprobacionesTotal }} / {{ $aprobacionesMinimo }} aprobaciones</span>
                    para continuar.
                    @if ($aprobacionesFaltan > 0)
                        <span class="text-xs opacity-75">· Faltan {{ $aprobacionesFaltan }}</span>
                    @endif
                </span>
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Presupuesto</span>
                <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                    {{ Number::currency($solicitud->monto_total ?? 0, in: 'MXN') }}
                </span>
            </div>
            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Conceptos</span>
                <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                    {{ count($detalles) }}
                </span>
            </div>
            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Inicio</span>
                <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                    {{ $solicitud->fecha_inicio?->format('d/m/Y') ?? '—' }}
                </span>
            </div>
            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Fin</span>
                <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                    {{ $solicitud->fecha_fin?->format('d/m/Y') ?? '—' }}
                </span>
            </div>
        </div>

        @if ($solicitud->motivo)
            <div>
                <span class="text-[10px] uppercase tracking-widest text-zinc-400">Motivo</span>
                <p class="mt-1 text-sm text-zinc-500 leading-relaxed">{{ $solicitud->motivo }}</p>
            </div>
        @endif
    </div>
</flux:card>
