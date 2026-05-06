<div class="flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3
            dark:border-blue-800 dark:bg-blue-900/20">
    <flux:icon.document-magnifying-glass class="size-4 text-blue-500 shrink-0" />
    <flux:text size="sm" class="text-blue-700 dark:text-blue-400">
        Comprobantes de tipo <span class="font-semibold">ticket o recibo</span> que requieren validación manual antes de cerrar el gasto.
    </flux:text>
</div>

<flux:card class="p-0">
    <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
        <flux:text size="sm" class="text-zinc-500">
            Pendientes: <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $totalComprobantes }}</span>
        </flux:text>
    </div>

    <flux:table :paginate="$comprobantes instanceof \Illuminate\Pagination\LengthAwarePaginator ? $comprobantes : null">
        <flux:table.columns>
            <flux:table.column class="pl-4"><span class="pl-4">Empleado</span></flux:table.column>
            <flux:table.column>Concepto</flux:table.column>
            <flux:table.column>Proyecto</flux:table.column>
            <flux:table.column>Tipo</flux:table.column>
            <flux:table.column>Monto</flux:table.column>
            <flux:table.column>Folio</flux:table.column>
            <flux:table.column>Subido</flux:table.column>
            <flux:table.column class="pr-4 text-right">Acción</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($comprobantes as $comp)
                @php
                    $tipoLabel = match($comp->tipo) {
                        'pdf'    => 'PDF',
                        'recibo' => 'Recibo',
                        default  => ucfirst($comp->tipo),
                    };
                    $tipoColor = match($comp->tipo) {
                        'pdf'    => 'blue',
                        'recibo' => 'yellow',
                        default  => 'zinc',
                    };
                @endphp

                <flux:table.row :key="$comp->id">
                    <flux:table.cell class="pl-4">
                        <div class="pl-4 flex flex-col text-sm">
                            <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ $comp->gasto->solicitud->empleado->nombre_completo }}
                            </span>
                            <span class="text-xs text-zinc-400">
                                {{ $comp->gasto->solicitud->empleado->numero_nomina ?? '—' }}
                            </span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="text-sm">{{ $comp->gasto->concepto->nombre }}</span>
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="text-sm">{{ $comp->gasto->solicitud->proyecto->nombre ?? '—' }}</span>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge :color="$tipoColor" size="sm">{{ $tipoLabel }}</flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="font-mono text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ Number::currency($comp->monto, in: 'MXN') }}
                        </span>
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="font-mono text-xs text-zinc-400">
                            {{ $comp->gasto->solicitud->folio }}
                        </span>
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="text-xs text-zinc-400">
                            {{ $comp->created_at->format('d/m/Y H:i') }}
                        </span>
                    </flux:table.cell>

                    <flux:table.cell class="pr-4 text-right">
                        <div class="pr-4 text-right">
                            <flux:button
                                size="sm" variant="ghost" icon="eye"
                                wire:click="openComprobante({{ $comp->id }})"
                                title="Revisar comprobante"
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <flux:icon name="check-circle" class="size-8 text-emerald-300" />
                            <flux:text class="text-zinc-400">Sin comprobantes pendientes de validación</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
