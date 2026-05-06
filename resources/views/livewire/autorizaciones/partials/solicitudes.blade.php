<flux:card>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:field>
                <flux:label>Búsqueda</flux:label>
                <flux:input wire:model.live.debounce.300ms="search"
                    placeholder="Folio, empleado..." icon="magnifying-glass" clearable />
            </flux:field>
        </div>
        <div class="sm:w-44">
            <flux:field>
                <flux:label>Proyectos</flux:label>
                <flux:select variant="listbox" wire:model.live="proyecto_id">
                    <flux:select.option value="">Todos</flux:select.option>
                    @foreach ($proyectos as $p)
                        <flux:select.option value="{{ $p['id'] }}">{{ $p['nombre'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>
        <div class="sm:w-44">
            <flux:field>
                <flux:label>Áreas</flux:label>
                <flux:select variant="listbox" wire:model.live="area_id">
                    <flux:select.option value="">Todos</flux:select.option>
                    @foreach ($areas as $a)
                        <flux:select.option value="{{ $a['id'] }}">{{ $a['nombre'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>
    </div>
</flux:card>

<flux:card class="p-0">
    <div class="flex flex-col gap-1 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
        <flux:text size="sm" class="text-zinc-500">
            Total: <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $autorizaciones->total() }}</span>
        </flux:text>
        <flux:text size="sm" class="text-zinc-500">
            Página {{ $autorizaciones->currentPage() }} de {{ $autorizaciones->lastPage() }}
        </flux:text>
    </div>

    <flux:table :paginate="$autorizaciones">
        <flux:table.columns>
            <flux:table.column class="pl-4">
                <span class="pl-4">Folio</span>
            </flux:table.column>
            <flux:table.column>Empleado</flux:table.column>
            <flux:table.column>Proyecto</flux:table.column>
            <flux:table.column>Fechas</flux:table.column>
            <flux:table.column>Presupuesto</flux:table.column>
            <flux:table.column>Excepciones</flux:table.column>
            <flux:table.column>Estatus</flux:table.column>
            <flux:table.column class="flex justify-end pr-4">
                <span class="pr-4">Acciones</span>
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($autorizaciones as $autorizacion)
                @php
                    $estatusColor = match($autorizacion->estatus) {
                        'Borrador'   => 'zinc',
                        'Pendiente'  => 'yellow',
                        'Autorizado' => 'green',
                        'Rechazado'  => 'red',
                        'Comprobado' => 'blue',
                        'Cancelado'  => 'zinc',
                        default      => 'zinc',
                    };
                @endphp

                <flux:table.row :key="$autorizacion->id">
                    <flux:table.cell class="pl-4">
                        <span class="pl-4 font-mono text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $autorizacion->folio }}
                        </span>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex flex-col text-sm">
                            <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ $autorizacion->empleado_nombre }}
                            </span>
                            <span class="text-xs text-zinc-400">
                                {{ $autorizacion->empleado?->numero_nomina ?? '-' }}
                            </span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="font-semibold text-sm text-zinc-800 dark:text-zinc-100">
                            {{ $autorizacion->proyecto_nombre ?? '—' }}
                        </span>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex flex-col text-xs text-zinc-500">
                            <span>{{ \Carbon\Carbon::parse($autorizacion->fecha_inicio)->format('d/M/Y') }}</span>
                            <span>{{ \Carbon\Carbon::parse($autorizacion->fecha_fin)->format('d/M/Y') }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="font-semibold text-sm text-zinc-800 dark:text-zinc-100">
                            {{ Number::currency($autorizacion->monto_total ?? 0, in: 'MXN') }}
                        </span>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex flex-col text-xs text-zinc-500">
                            <span>N1: {{ $autorizacion->excepciones_n1 ?? 0 }}</span>
                            <span>N2: {{ $autorizacion->excepciones_n2 ?? 0 }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge :color="$estatusColor" size="sm" inset="top bottom">
                            {{ $autorizacion->estatus }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="text-right">
                        <div class="pr-4 text-right">
                            <flux:button
                                size="sm" variant="ghost" icon="eye"
                                wire:click="openDetail({{ $autorizacion->id }})"
                                title="Ver detalle"
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <flux:icon name="inbox" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            <flux:text class="text-zinc-400">No se encontraron autorizaciones</flux:text>
                            @if ($search || $proyecto_id || $area_id)
                                <flux:button size="sm" variant="ghost" wire:click="clearFilters">
                                    Limpiar filtros
                                </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
