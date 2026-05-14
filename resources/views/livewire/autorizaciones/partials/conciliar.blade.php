<div class="flex items-center gap-2 rounded-lg border border-purple-200 bg-purple-50 px-4 py-3
            dark:border-purple-800 dark:bg-purple-900/20">
    <flux:icon.credit-card class="size-4 text-purple-500 shrink-0" />

    <flux:text size="sm" class="text-purple-700 dark:text-purple-400">
        Concilia los gastos del periodo de tarjeta corporativa y valida cada comprobación antes de aprobar o rechazar los movimientos.
    </flux:text>
</div>

<flux:card class="p-0">
    <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:bg-zinc-700">
        <flux:text size="sm" class="text-zinc-500">
            Pendientes: <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                {{ $totalConciliaciones }}
            </span>
        </flux:text>
    </div>

    <flux:table :paginate="$conciliaciones instanceof \Illuminate\Pagination\LengthAwarePaginator ? $conciliaciones : null">
        <flux:table.columns>
            <flux:table.column>
                <span class="pl-4">Empleado</span>
            </flux:table.column>
            <flux:table.column>Folio</flux:table.column>
            <flux:table.column>Periodo</flux:table.column>
            <flux:table.column>Proyecto</flux:table.column>
            <flux:table.column>Monto total</flux:table.column>
            <flux:table.column>
                <span class="pr-4 text-right justify-end">Acción</span>
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($conciliaciones as $con)
                <flux:table.row :key="$con->id">
                    <flux:table.cell>
                        <span class="pl-4 font-semibold">
                            {{ $con->empleado_nombre }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>{{ $con->folio }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-col text-xs text-zinc-500">
                            <span>{{ \Carbon\Carbon::parse($con->fecha_inicio)->format('d/M/Y') }}</span> -
                            <span>{{ \Carbon\Carbon::parse($con->fecha_fin)->format('d/M/Y') }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $con->proyecto_nombre ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ Number::currency($con->monto_total ?? 0, in: 'MXN') }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="pr-4 text-right justify-end">
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="document-currency-dollar"
                                wire:click="showComprobacionTarjeta({{ $con->id }})"
                                title="Conciliar gastos"
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <flux:icon name="check-circle" class="size-8 text-emerald-300" />
                            <flux:text class="text-zinc-400">Sin conciliaciones pendientes</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
