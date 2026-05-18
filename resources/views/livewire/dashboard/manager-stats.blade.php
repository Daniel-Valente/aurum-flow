<div class="flex h-full w-full flex-1 flex-col gap-4">

    @if($data['aprobaciones']['pendientes'] > 0 || $data['aprobaciones']['excepciones_n1'] > 0)
        <flux:card class="border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/30">
            <div class="flex items-start gap-3">
                <flux:icon.bell-alert class="size-5 text-rose-500 shrink-0 mt-0.5" />
                <div class="flex-1">
                    <flux:heading size="sm" class="text-rose-800 dark:text-rose-200">
                        Requieren tu aprobación
                    </flux:heading>
                    <div class="mt-3 flex flex-wrap gap-4">
                        @if($data['aprobaciones']['pendientes'] > 0)
                            <div>
                                <span class="text-2xl font-bold text-rose-600 dark:text-rose-400">
                                    {{ $data['aprobaciones']['pendientes'] }}
                                </span>
                                <span class="ml-1 text-sm text-rose-700 dark:text-rose-300">
                                    solicitud(es) pendiente(s)
                                </span>
                            </div>
                        @endif
                        @if($data['aprobaciones']['excepciones_n1'] > 0)
                            <div>
                                <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                                    {{ $data['aprobaciones']['excepciones_n1'] }}
                                </span>
                                <span class="ml-1 text-sm text-amber-700 dark:text-amber-300">
                                    excepción(es) N1
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4">
                        <flux:button variant="primary" color="rose" href="{{ route('aprobaciones.index') }}">
                            Ir a autorizaciones
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Grid Principal --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">

        {{-- Salud del Área --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Presupuesto del Área</flux:heading>

                @if($data['salud_area']['tiene_presupuesto'])
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-baseline justify-between mb-1">
                                <span class="text-xs text-zinc-500">Gastado + Comprometido</span>
                                <span class="text-xl font-bold text-zinc-800 dark:text-zinc-100">
                                    {{ $data['salud_area']['porcentaje'] }}%
                                </span>
                            </div>
                            <div class="h-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div
                                    class="h-full transition-all {{ match($data['salud_area']['severidad']) {
                                        'critico' => 'bg-gradient-to-r from-rose-500 to-rose-600',
                                        'alerta' => 'bg-gradient-to-r from-amber-500 to-amber-600',
                                        default => 'bg-gradient-to-r from-blue-500 to-blue-600'
                                    } }}"
                                    style="width: {{ min($data['salud_area']['porcentaje'], 100) }}%"
                                ></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <span class="text-zinc-500">Presupuesto</span>
                                <p class="font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ Number::currency($data['salud_area']['presupuesto'], in: 'MXN') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-zinc-500">Gastado</span>
                                <p class="font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ Number::currency($data['salud_area']['gastado'], in: 'MXN') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-zinc-500">Disponible</span>
                                <p class="font-semibold text-emerald-600">
                                    {{ Number::currency($data['salud_area']['disponible'], in: 'MXN') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-zinc-500">Proyección</span>
                                <p class="font-semibold {{ $data['salud_area']['proyeccion'] > $data['salud_area']['presupuesto'] ? 'text-rose-600' : 'text-zinc-800 dark:text-zinc-100' }}">
                                    {{ Number::currency($data['salud_area']['proyeccion'], in: 'MXN') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="py-6 text-center">
                        <flux:icon.chart-bar class="mx-auto size-8 text-zinc-400" />
                        <p class="mt-2 text-sm text-zinc-500">Sin presupuesto asignado</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Eficiencia Operativa --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Eficiencia Operativa</flux:heading>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500">Tiempo aprobación</span>
                        <div class="text-right">
                            <span class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ $data['eficiencia']['tiempo_aprobacion_dias'] }}
                            </span>
                            <span class="text-xs text-zinc-500">días</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500">Tasa de rechazo</span>
                        <span class="text-lg font-semibold {{ $data['eficiencia']['tasa_rechazo_pct'] < 10 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $data['eficiencia']['tasa_rechazo_pct'] }}%
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500">Comprobación a tiempo</span>
                        <span class="text-lg font-semibold {{ $data['eficiencia']['comprobacion_tiempo_pct'] >= 90 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $data['eficiencia']['comprobacion_tiempo_pct'] }}%
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500">Excepciones rechazadas</span>
                        <span class="text-lg font-semibold {{ $data['eficiencia']['excepciones_rechazadas_pct'] < 15 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $data['eficiencia']['excepciones_rechazadas_pct'] }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Gastadores --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Top Gastadores</flux:heading>
                @if(!empty($data['salud_area']['top_gastadores']))
                    <div class="space-y-3">
                        @foreach($data['salud_area']['top_gastadores'] as $index => $gastador)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ $index + 1 }}. {{ $gastador['nombre'] }}
                                    </span>
                                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                        {{ Number::currency($gastador['gastado'], in: 'MXN') }}
                                    </span>
                                </div>
                                @if($gastador['pct_presupuesto'] > 0)
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                            <div
                                                class="h-full {{ $gastador['pct_presupuesto'] > 90 ? 'bg-rose-500' : 'bg-blue-500' }}"
                                                style="width: {{ min($gastador['pct_presupuesto'], 100) }}%"
                                            ></div>
                                        </div>
                                        <span class="text-xs text-zinc-500 shrink-0">
                                            {{ $gastador['pct_presupuesto'] }}%
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-zinc-500 text-center py-4">Sin datos</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Sección Inferior: 2 columnas --}}
    <div class="grid gap-4 md:grid-cols-2">

        {{-- Top Conceptos --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Conceptos más Usados</flux:heading>
                @if(!empty($data['salud_area']['top_conceptos']))
                    <div class="space-y-3">
                        @foreach($data['salud_area']['top_conceptos'] as $concepto)
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $concepto['concepto'] }}
                                    </span>
                                    <div class="mt-1 h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-gradient-to-r from-blue-500 to-blue-600"
                                            style="width: {{ $concepto['pct'] }}%"
                                        ></div>
                                    </div>
                                </div>
                                <div class="ml-4 text-right shrink-0">
                                    <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                        {{ Number::currency($concepto['monto'], in: 'MXN') }}
                                    </p>
                                    <p class="text-xs text-zinc-500">{{ $concepto['pct'] }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-zinc-500 text-center py-4">Sin datos</p>
                @endif
            </div>
        </div>

        {{-- Actividad Reciente --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Actividad Reciente</flux:heading>
                @if(!empty($data['actividad_reciente']))
                    <div class="space-y-2">
                        @foreach($data['actividad_reciente'] as $actividad)
                            <div class="flex items-start gap-2 text-sm">
                                <flux:icon.clock class="size-4 text-zinc-400 shrink-0 mt-0.5" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-zinc-700 dark:text-zinc-300">
                                        <span class="font-medium">{{ $actividad['empleado'] }}</span>
                                        {{ $actividad['tipo'] }}
                                        <span class="font-mono text-zinc-500">{{ $actividad['folio'] }}</span>
                                    </p>
                                    <p class="text-xs text-zinc-500 mt-0.5">
                                        {{ Number::currency($actividad['monto'], in: 'MXN') }} · {{ $actividad['tiempo'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-zinc-500 text-center py-4">Sin actividad reciente</p>
                @endif
            </div>
        </div>
    </div>

</div>
