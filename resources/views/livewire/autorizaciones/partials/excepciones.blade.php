@if (!$nivelFiltro)
    <flux:card>
        <div class="flex flex-col items-center gap-3 py-10 text-center">
            <flux:icon name="lock-closed" class="size-8 text-zinc-300" />
            <flux:text class="text-zinc-400">Tu rol no tiene excepciones asignadas para resolver.</flux:text>
        </div>
    </flux:card>
@else
    <div class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3
                dark:border-amber-800 dark:bg-amber-900/20">
        <flux:icon.exclamation-triangle class="size-4 text-amber-500 shrink-0" />
        <flux:text size="sm" class="text-amber-700 dark:text-amber-400">
            Eres <span class="font-semibold">Nivel {{ $nivelFiltro }}</span> en el flujo de excepciones.
            @if ($nivelFiltro === 1)
                Revisa y decide si escalar cada gasto excedido a administración.
            @else
                Tienes la decisión final sobre los gastos aprobados por el manager.
            @endif
        </flux:text>
    </div>

    <flux:card class="p-0">
        <flux:table :paginate="$excepciones instanceof \Illuminate\Pagination\LengthAwarePaginator ? $excepciones : null">
            <flux:table.columns>
                <flux:table.column class="pl-4"><span class="pl-4">Empleado</span></flux:table.column>
                <flux:table.column>Concepto</flux:table.column>
                <flux:table.column>Proyecto</flux:table.column>
                <flux:table.column>Monto</flux:table.column>
                <flux:table.column>Nivel</flux:table.column>
                <flux:table.column>Folio solicitud</flux:table.column>
                <flux:table.column class="pr-4 text-right">Acción</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($excepciones as $exc)
                    <flux:table.row :key="$exc->id">
                        <flux:table.cell class="pl-4">
                            <div class="pl-4 flex flex-col text-sm">
                                <span class="font-semibold">
                                    {{ $exc->gasto->empleado->nombre_completo }}
                                </span>
                                <span class="text-xs text-zinc-400">
                                    {{ $exc->gasto->empleado->numero_nomina ?? '—' }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm pl-4">{{ $exc->gasto->concepto->nombre }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm">{{ $exc->gasto->solicitud->proyecto->nombre ?? '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="font-mono text-sm font-semibold text-rose-600">
                                {{ Number::currency($exc->gasto->monto, in: 'MXN') }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="{{ $exc->nivel === 1 ? 'yellow' : 'orange' }}" size="sm">
                                N{{ $exc->nivel }} — {{ $exc->nivel === 1 ? 'Gerente' : 'Administración' }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="font-mono text-xs text-zinc-400">
                                {{ $exc->gasto->solicitud->folio }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell class="pr-4 text-right">
                            <div class="pr-4 text-right">
                                <flux:button
                                    size="sm" variant="ghost" icon="eye"
                                    wire:click="openExcepcion({{ $exc->id }})"
                                    title="Revisar excepción"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="check-circle" class="size-8 text-emerald-300" />
                                <flux:text class="text-zinc-400">Sin excepciones pendientes</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
@endif
