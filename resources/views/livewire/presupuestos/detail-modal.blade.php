<flux:modal name="presupuesto-detail" flyout variant="floating" class="md:w-2xl">
    @if($detalle)
        @php
            $presupuesto = $detalle['presupuesto'];
            $severidad = $detalle['severidad'];
        @endphp

        <div class="flex flex-col gap-6">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <flux:heading size="lg" class="leading-tight">
                            {{ $presupuesto->codigo }}
                        </flux:heading>
                        <flux:badge :color="match($presupuesto->estatus) {
                            'activo' => 'green',
                            'borrador' => 'zinc',
                            'agotado' => 'red',
                            'vencido' => 'orange',
                            'cancelado' => 'red',
                            default => 'zinc'
                        }" size="sm">
                            {{ ucfirst($presupuesto->estatus) }}
                        </flux:badge>
                    </div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $presupuesto->nombre }}
                    </p>
                    @if($presupuesto->descripcion)
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ $presupuesto->descripcion }}
                        </p>
                    @endif
                </div>

                <flux:badge :color="match($presupuesto->tipo) {
                    'empresa' => 'blue',
                    'area' => 'purple',
                    'empleado' => 'green',
                    'proyecto' => 'orange',
                    default => 'zinc'
                }" size="sm">
                    {{ ucfirst($presupuesto->tipo) }}
                </flux:badge>
            </div>

            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-900 px-4 py-3">
                <span class="text-[10px] uppercase text-zinc-400">Asignado a</span>
                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100 mt-1">
                    @if($presupuesto->tipo === 'empresa' && $presupuesto->empresa)
                        {{ $presupuesto->empresa->nombre }}
                    @elseif($presupuesto->tipo === 'area' && $presupuesto->area)
                        {{ $presupuesto->area->nombre }}
                    @elseif($presupuesto->tipo === 'empleado' && $presupuesto->empleado)
                        {{ $presupuesto->empleado->nombre_completo }}
                    @elseif($presupuesto->tipo === 'proyecto' && $presupuesto->proyecto)
                        {{ $presupuesto->proyecto->nombre }} ({{ $presupuesto->proyecto->codigo }})
                    @else
                        —
                    @endif
                </p>
            </div>

            <flux:separator />

            <div>
                <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                    Resumen Financiero
                </flux:subheading>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Presupuesto total</span>
                        <span class="text-xl font-bold text-zinc-800 dark:text-zinc-100">
                            {{ Number::currency($detalle['monto_total'], in: 'MXN') }}
                        </span>
                    </div>
                    <div>
                        <div class="h-3 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden flex">
                            @php
                                $total = $detalle['monto_total'];
                                $pctGastado = $total > 0 ? ($detalle['monto_gastado'] / $total) * 100 : 0;
                                $pctComprometido = $total > 0 ? ($detalle['monto_comprometido'] / $total) * 100 : 0;
                            @endphp
                            <div class="bg-rose-500" style="width: {{ $pctGastado }}%"></div>
                            <div class="bg-amber-500" style="width: {{ $pctComprometido }}%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2 text-xs">
                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                                    Gastado
                                </span>
                                <span class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                                    Comprometido
                                </span>
                                <span class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                    Disponible
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="flex flex-col gap-1 rounded-lg bg-rose-50 dark:bg-rose-950/30 px-3 py-2.5">
                            <span class="text-[10px] uppercase text-rose-600 dark:text-rose-400">Gastado</span>
                            <span class="text-sm font-semibold text-rose-700 dark:text-rose-300">
                                {{ Number::currency($detalle['monto_gastado'], in: 'MXN') }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-1 rounded-lg bg-amber-50 dark:bg-amber-950/30 px-3 py-2.5">
                            <span class="text-[10px] uppercase text-amber-600 dark:text-amber-400">Comprometido</span>
                            <span class="text-sm font-semibold text-amber-700 dark:text-amber-300">
                                {{ Number::currency($detalle['monto_comprometido'], in: 'MXN') }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 px-3 py-2.5">
                            <span class="text-[10px] uppercase text-emerald-600 dark:text-emerald-400">Disponible</span>
                            <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                                {{ Number::currency($detalle['monto_disponible'], in: 'MXN') }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-lg border {{ match($severidad) {
                        'agotado', 'critico' => 'border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/30',
                        'alerta' => 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30',
                        default => 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/30'
                    } }} p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase {{ match($severidad) {
                                'agotado', 'critico' => 'text-rose-600 dark:text-rose-400',
                                'alerta' => 'text-amber-600 dark:text-amber-400',
                                default => 'text-emerald-600 dark:text-emerald-400'
                            } }}">
                                Consumo
                            </span>
                            <span class="text-2xl font-bold {{ match($severidad) {
                                'agotado', 'critico' => 'text-rose-700 dark:text-rose-300',
                                'alerta' => 'text-amber-700 dark:text-amber-300',
                                default => 'text-emerald-700 dark:text-emerald-300'
                            } }}">
                                {{ $detalle['pct_consumido'] }}%
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            <flux:separator />

            <div>
                <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                    Vigencia
                </flux:subheading>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Período</span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                            {{ ucfirst($presupuesto->periodo) }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Días restantes</span>
                        <span class="text-sm font-semibold {{ $detalle['dias_restantes'] !== null && $detalle['dias_restantes'] <= 7 ? 'text-amber-600' : 'text-zinc-700 dark:text-zinc-200' }}">
                            {{ $detalle['dias_restantes'] ?? '—' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Fecha inicio</span>
                        <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                            {{ $presupuesto->fecha_inicio->format('d/m/Y') }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Fecha fin</span>
                        <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                            {{ $presupuesto->fecha_fin->format('d/m/Y') }}
                        </span>
                    </div>
                </div>

                @if($presupuesto->renovable)
                    <div class="mt-3 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/30 p-3">
                        <div class="flex items-center gap-2">
                            <flux:icon.arrow-path class="size-4 text-blue-600" />
                            <span class="text-xs text-blue-700 dark:text-blue-300">
                                Renovable: {{ ucfirst($presupuesto->frecuencia_renovacion) }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            @if(!empty($detalle['alertas_activas']) && count($detalle['alertas_activas']) > 0)
                <flux:separator />

                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon.bell-alert class="size-4 text-rose-500" />
                        <flux:subheading class="text-xs uppercase tracking-widest text-rose-600 dark:text-rose-400">
                            Alertas Activas
                        </flux:subheading>
                    </div>

                    <div class="space-y-2">
                        @foreach($detalle['alertas_activas'] as $alerta)
                            <div class="rounded-lg border {{ match($alerta->severidad) {
                                'critical' => 'border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/30',
                                'danger' => 'border-rose-200 dark:border-rose-800 bg-rose-50/50 dark:bg-rose-950/20',
                                'warning' => 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30',
                                default => 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/30'
                            } }} p-3">
                                <div class="flex items-start gap-3">
                                    <flux:icon.exclamation-triangle class="size-4 {{ match($alerta->severidad) {
                                        'critical', 'danger' => 'text-rose-500',
                                        'warning' => 'text-amber-500',
                                        default => 'text-blue-500'
                                    } }} shrink-0 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-xs font-medium {{ match($alerta->severidad) {
                                            'critical', 'danger' => 'text-rose-800 dark:text-rose-200',
                                            'warning' => 'text-amber-800 dark:text-amber-200',
                                            default => 'text-blue-800 dark:text-blue-200'
                                        } }}">
                                            {{ $alerta->titulo }}
                                        </p>
                                        <p class="text-xs {{ match($alerta->severidad) {
                                            'critical', 'danger' => 'text-rose-600 dark:text-rose-400',
                                            'warning' => 'text-amber-600 dark:text-amber-400',
                                            default => 'text-blue-600 dark:text-blue-400'
                                        } }} mt-1">
                                            {{ $alerta->mensaje }}
                                        </p>
                                        <p class="text-xs text-zinc-400 mt-1">
                                            {{ $alerta->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($detalle['solicitudes']) && count($detalle['solicitudes']) > 0)
                <flux:separator />

                <div>
                    <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                        Solicitudes Vinculadas
                    </flux:subheading>

                    <div class="space-y-2">
                        @foreach($detalle['solicitudes'] as $rel)
                            @php
                                $sol = $rel->solicitud;
                            @endphp
                            <div class="flex items-center justify-between p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                            {{ $sol->folio }}
                                        </span>
                                        <flux:badge :color="match($sol->estatus) {
                                            'Autorizado', 'Comprobado' => 'green',
                                            'Pendiente' => 'yellow',
                                            'Rechazado' => 'red',
                                            default => 'zinc'
                                        }" size="sm">
                                            {{ $sol->estatus }}
                                        </flux:badge>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 text-xs text-zinc-500">
                                        <span>{{ $sol->empleado->nombre_completo }}</span>
                                        <span>•</span>
                                        <span>{{ ucfirst($rel->estatus) }}: {{ Number::currency($rel->monto_comprometido, in: 'MXN') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($detalle['movimientos']) && count($detalle['movimientos']) > 0)
                <flux:separator />
                <div>
                    <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                        Últimos Movimientos
                    </flux:subheading>

                    <div class="space-y-2">
                        @foreach($detalle['movimientos'] as $mov)
                            <div class="flex items-start gap-3 text-sm">
                                <div class="shrink-0 mt-0.5">
                                    @if(in_array($mov->tipo, ['gasto', 'ajuste_decremento', 'transferencia_out']))
                                        <flux:icon.arrow-trending-down class="size-4 text-rose-500" />
                                    @else
                                        <flux:icon.arrow-trending-up class="size-4 text-emerald-500" />
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-zinc-700 dark:text-zinc-300">
                                        <span class="font-medium">{{ $mov->getDescripcionTipoAttribute() }}</span>
                                        por {{ Number::currency($mov->monto, in: 'MXN') }}
                                    </p>
                                    @if($mov->concepto)
                                        <p class="text-xs text-zinc-500 mt-0.5">{{ $mov->concepto }}</p>
                                    @endif
                                    <div class="flex items-center gap-2 mt-1 text-xs text-zinc-400">
                                        <span>{{ $mov->actor?->name ?? 'Sistema' }}</span>
                                        <span>•</span>
                                        <span>{{ $mov->fecha_movimiento->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <flux:separator />

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="flex flex-col gap-1">
                    <span class="text-zinc-400">Creado por</span>
                    <span class="text-zinc-700 dark:text-zinc-300">
                        {{ $presupuesto->creadoPor?->name ?? '—' }}
                    </span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-zinc-400">Creado el</span>
                    <span class="text-zinc-700 dark:text-zinc-300">
                        {{ $presupuesto->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                @if($presupuesto->aprobado_por)
                    <div class="flex flex-col gap-1">
                        <span class="text-zinc-400">Aprobado por</span>
                        <span class="text-zinc-700 dark:text-zinc-300">
                            {{ $presupuesto->aprobadoPor?->name ?? '—' }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-zinc-400">Aprobado el</span>
                        <span class="text-zinc-700 dark:text-zinc-300">
                            {{ $presupuesto->aprobado_en?->format('d/m/Y H:i') ?? '—' }}
                        </span>
                    </div>
                @endif
            </div>

            @if($presupuesto->notas)
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                    <p class="text-xs uppercase text-zinc-400 mb-2">Notas</p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $presupuesto->notas }}</p>
                </div>
            @endif

        </div>
    @endif
</flux:modal>
