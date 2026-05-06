<div class="grid gap-3 grid-cols-2 sm:grid-cols-4">
    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <div>
            <span class="text-xs uppercase text-zinc-400">Dentro de límite</span>
            <p class="text-2xl font-semibold text-emerald-600">{{ $kpi_ok }}</p>
        </div>
        <flux:icon.check-circle class="size-5 text-emerald-500" />
    </div>
    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <div>
            <span class="text-xs uppercase text-zinc-400">Al límite</span>
            <p class="text-2xl font-semibold text-amber-500">{{ $kpi_limite }}</p>
        </div>
        <flux:icon.exclamation-circle class="size-5 text-amber-500" />
    </div>
    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <div>
            <span class="text-xs uppercase text-zinc-400">Excedidos</span>
            <p class="text-2xl font-semibold text-rose-500">{{ $kpi_excedido }}</p>
        </div>
        <flux:icon.x-circle class="size-5 text-rose-500" />
    </div>
    <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <div>
            <span class="text-xs uppercase text-zinc-400">Sin política</span>
            <p class="text-2xl font-semibold text-zinc-500">{{ $kpi_sin_politica }}</p>
        </div>
        <flux:icon.question-mark-circle class="size-5 text-zinc-400" />
    </div>
</div>
