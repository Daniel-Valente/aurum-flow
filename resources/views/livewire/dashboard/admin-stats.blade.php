<div class="flex h-full w-full flex-1 flex-col gap-4">
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-zinc-500">Gasto Total (Mes)</span>
                    <flux:icon.chart-bar class="size-4 text-zinc-400" />
                </div>
                <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">
                    {{ Number::currency($data['kpis_estrategicos']['gasto_total'], in: 'MXN') }}
                </p>
                <div class="flex items-center gap-1 mt-2">
                    <span class="text-xs {{ $data['kpis_estrategicos']['variacion_anual_pct'] >= 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $data['kpis_estrategicos']['variacion_anual_pct'] >= 0 ? '↗' : '↘' }}
                        {{ abs($data['kpis_estrategicos']['variacion_anual_pct']) }}% vs año anterior
                    </span>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-zinc-500">Presupuesto</span>
                    <flux:badge :color="$data['kpis_estrategicos']['porcentaje_gastado'] > 90 ? 'red' : ($data['kpis_estrategicos']['porcentaje_gastado'] > 80 ? 'yellow' : 'green')" size="sm">
                        {{ $data['kpis_estrategicos']['porcentaje_gastado'] }}%
                    </flux:badge>
                </div>
                <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">
                    {{ Number::currency($data['kpis_estrategicos']['presupuesto_total'], in: 'MXN') }}
                </p>
                <div class="mt-2 h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                    <div
                        class="h-full transition-all {{ $data['kpis_estrategicos']['porcentaje_gastado'] > 90 ? 'bg-linear-to-r from-rose-500 to-rose-600' : ($data['kpis_estrategicos']['porcentaje_gastado'] > 80 ? 'bg-linear-to-r from-amber-500 to-amber-600' : 'bg-linear-to-r from-blue-500 to-blue-600') }}"
                        style="width: {{ min($data['kpis_estrategicos']['porcentaje_gastado'], 100) }}%"
                    ></div>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-zinc-500">Proyección Cierre</span>
                    <flux:icon.calculator class="size-4 text-zinc-400" />
                </div>
                <p class="text-2xl font-bold {{ $data['kpis_estrategicos']['proyeccion_cierre'] > $data['kpis_estrategicos']['presupuesto_total'] ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ Number::currency($data['kpis_estrategicos']['proyeccion_cierre'], in: 'MXN') }}
                </p>
                <div class="flex items-center gap-1 mt-2">
                    <span class="text-xs text-zinc-500">
                        Tendencia 3M: {{ $data['kpis_estrategicos']['tendencia_3meses'] }}
                    </span>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-zinc-500">Alertas Críticas</span>
                    <flux:icon.bell-alert class="size-4 text-rose-500" />
                </div>
                <p class="text-2xl font-bold {{ $data['kpis_estrategicos']['alertas_criticas'] > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $data['kpis_estrategicos']['alertas_criticas'] }}
                </p>
                <div class="flex items-center gap-1 mt-2">
                    <span class="text-xs text-zinc-500">
                        Presupuestos en riesgo
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">

        <div class="relative overflow-hidden rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30  md:col-span-2">
            <div class="p-6">
                <div class="flex items-center gap-2 mb-4">
                    <flux:icon.shield-exclamation class="size-5 text-amber-500" />
                    <flux:heading size="sm" class="text-amber-800 dark:text-amber-200">
                        Excepciones N2
                    </flux:heading>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-amber-700 dark:text-amber-300">Pendientes</span>
                        <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                            {{ $data['excepciones_n2']['pendientes'] }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-amber-700 dark:text-amber-300">Tasa aprobación</span>
                        <span class="text-lg font-semibold text-amber-600 dark:text-amber-400">
                            {{ $data['excepciones_n2']['tasa_aprobacion_pct'] }}%
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-amber-700 dark:text-amber-300">Monto total</span>
                        <span class="text-lg font-semibold text-amber-600 dark:text-amber-400">
                            {{ Number::currency($data['excepciones_n2']['monto_total'], in: 'MXN') }}
                        </span>
                    </div>
                </div>
                @if($data['excepciones_n2']['pendientes'] > 0)
                    <div class="mt-4">
                        <flux:button class="w-full" variant="primary" color="amber" href="{{ route('autorizaciones.index') }}">
                            Revisar excepciones
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 md:col-span-2">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Presupuestos (Global)</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs text-zinc-500">Presupuestos Activos</span>
                        <p class="text-3xl font-bold text-zinc-800 dark:text-zinc-100 mt-1">
                            {{ $data['presupuestos_overview']['total_activos'] }}
                        </p>
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach($data['presupuestos_overview']['por_tipo'] as $tipo => $total)
                                <flux:badge color="zinc" size="sm">
                                    {{ ucfirst($tipo) }}: {{ $total }}
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-500">Consumo Global</span>
                        <p class="text-3xl font-bold {{ $data['presupuestos_overview']['pct_consumo_global'] > 90 ? 'text-rose-600' : 'text-zinc-800 dark:text-zinc-100' }} mt-1">
                            {{ $data['presupuestos_overview']['pct_consumo_global'] }}%
                        </p>
                        <div class="mt-2 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                            <div
                                class="h-full transition-all {{ $data['presupuestos_overview']['pct_consumo_global'] > 90 ? 'bg-gradient-to-r from-rose-500 to-rose-600' : 'bg-gradient-to-r from-blue-500 to-blue-600' }}"
                                style="width: {{ min($data['presupuestos_overview']['pct_consumo_global'], 100) }}%"
                            ></div>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-500">Total Asignado</span>
                        <p class="text-lg font-semibold text-zinc-800 dark:text-zinc-100 mt-1">
                            {{ Number::currency($data['presupuestos_overview']['monto_total'], in: 'MXN') }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-500">Gastado + Comprometido</span>
                        <p class="text-lg font-semibold text-zinc-800 dark:text-zinc-100 mt-1">
                            {{ Number::currency($data['presupuestos_overview']['monto_gastado'] + $data['presupuestos_overview']['monto_comprometido'], in: 'MXN') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="sm">Desempeño por Área</flux:heading>
                <!--<flux:button size="sm" variant="ghost" icon="arrow-right" icon-trailing>
                    Ver detalle
                </flux:button>-->
            </div>

            @if(!empty($data['desempeno_areas']))
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="text-left py-2 px-3 text-xs font-medium text-zinc-500">Área</th>
                                <th class="text-right py-2 px-3 text-xs font-medium text-zinc-500">Budget</th>
                                <th class="text-right py-2 px-3 text-xs font-medium text-zinc-500">Gastado</th>
                                <th class="text-right py-2 px-3 text-xs font-medium text-zinc-500">%</th>
                                <th class="text-center py-2 px-3 text-xs font-medium text-zinc-500">Eficiencia</th>
                                <th class="text-center py-2 px-3 text-xs font-medium text-zinc-500">Riesgo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['desempeno_areas'] as $area)
                                <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="py-3 px-3 font-medium text-zinc-800 dark:text-zinc-100">
                                        {{ $area['area'] }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono text-zinc-700 dark:text-zinc-300">
                                        {{ Number::currency($area['presupuesto'], in: 'MXN') }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono text-zinc-700 dark:text-zinc-300">
                                        {{ Number::currency($area['gastado'], in: 'MXN') }}
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-16 h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                                <div
                                                    class="h-full {{ $area['porcentaje'] > 90 ? 'bg-rose-500' : ($area['porcentaje'] > 80 ? 'bg-amber-500' : 'bg-blue-500') }}"
                                                    style="width: {{ min($area['porcentaje'], 100) }}%"
                                                ></div>
                                            </div>
                                            <span class="font-semibold {{ $area['porcentaje'] > 90 ? 'text-rose-600' : ($area['porcentaje'] > 80 ? 'text-amber-600' : 'text-emerald-600') }}">
                                                {{ $area['porcentaje'] }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center justify-center w-12 h-6 rounded-full text-xs font-semibold {{ $area['eficiencia_pct'] >= 90 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : ($area['eficiencia_pct'] >= 70 ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400') }}">
                                            {{ $area['eficiencia_pct'] }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <flux:badge :color="match($area['riesgo']) {
                                            'critico' => 'red',
                                            'alerta' => 'yellow',
                                            default => 'green'
                                        }" size="sm">
                                            @if($area['riesgo'] === 'critico')
                                                🔴 Crítico
                                            @elseif($area['riesgo'] === 'alerta')
                                                🟡 Alerta
                                            @else
                                                🟢 Normal
                                            @endif
                                        </flux:badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-8 text-center">
                    <p class="text-sm text-zinc-500">No hay datos de áreas</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Insights y Recomendaciones --}}
    @if(!empty($data['insights']))
        <div class="relative overflow-hidden rounded-xl border border-blue-200 dark:border-blue-800 bg-linear-to-br from-blue-50 to-white dark:from-blue-950 dark:to-zinc-900">
            <div class="p-6">
                <div class="flex items-center gap-2 mb-4">
                    <flux:icon.light-bulb class="size-5 text-blue-500" />
                    <flux:heading size="sm" class="text-blue-800 dark:text-blue-200">
                        Insights y Recomendaciones
                    </flux:heading>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach($data['insights'] as $insight)
                        <div class="p-4 rounded-lg border {{ match($insight['severidad']) {
                            'critical' => 'border-rose-300 dark:border-rose-700 bg-rose-50 dark:bg-rose-950/30',
                            'danger' => 'border-rose-200 dark:border-rose-800 bg-rose-50/50 dark:bg-rose-950/20',
                            'warning' => 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30',
                            default => 'border-blue-200 dark:border-blue-800 bg-white dark:bg-blue-950/20'
                        } }}">
                            <div class="flex items-start gap-3">
                                <div class="shrink-0 mt-0.5">
                                    @if($insight['tipo'] === 'alerta')
                                        <flux:icon.exclamation-triangle class="size-5 {{ match($insight['severidad']) {
                                            'critical', 'danger' => 'text-rose-500',
                                            'warning' => 'text-amber-500',
                                            default => 'text-blue-500'
                                        } }}" />
                                    @elseif($insight['tipo'] === 'exito')
                                        <flux:icon.check-circle class="size-5 text-emerald-500" />
                                    @elseif($insight['tipo'] === 'critico')
                                        <flux:icon.shield-exclamation class="size-5 text-rose-500" />
                                    @else
                                        <flux:icon.information-circle class="size-5 text-blue-500" />
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-sm {{ match($insight['severidad']) {
                                        'critical', 'danger' => 'text-rose-800 dark:text-rose-200',
                                        'warning' => 'text-amber-800 dark:text-amber-200',
                                        default => 'text-blue-800 dark:text-blue-200'
                                    } }}">
                                        {{ $insight['titulo'] }}
                                    </p>
                                    <p class="text-xs mt-1 {{ match($insight['severidad']) {
                                        'critical', 'danger' => 'text-rose-600 dark:text-rose-400',
                                        'warning' => 'text-amber-600 dark:text-amber-400',
                                        default => 'text-blue-600 dark:text-blue-400'
                                    } }}">
                                        {{ $insight['descripcion'] }}
                                    </p>
                                    <p class="text-xs mt-2 italic {{ match($insight['severidad']) {
                                        'critical', 'danger' => 'text-rose-700 dark:text-rose-300',
                                        'warning' => 'text-amber-700 dark:text-amber-300',
                                        default => 'text-blue-700 dark:text-blue-300'
                                    } }}">
                                        → {{ $insight['recomendacion'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Grid Inferior: Tendencias + Salud del Sistema + Governance --}}
    <div class="grid gap-4 md:grid-cols-3">

        {{-- Tendencias --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Tendencias (6 meses)</flux:heading>
                @if(!empty($data['tendencias']['gasto_mensual']))
                    <div class="space-y-3">
                        @foreach(array_slice($data['tendencias']['gasto_mensual'], -3) as $mes)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-zinc-500">{{ $mes['mes'] }}</span>
                                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                        {{ Number::currency($mes['gasto'], in: 'MXN') }}
                                    </span>
                                </div>
                                <div class="h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                    @php
                                        $pct = $mes['presupuesto'] > 0 ? ($mes['gasto'] / $mes['presupuesto']) * 100 : 0;
                                    @endphp
                                    <div
                                        class="h-full {{ $pct > 90 ? 'bg-rose-500' : ($pct > 80 ? 'bg-amber-500' : 'bg-blue-500') }}"
                                        style="width: {{ min($pct, 100) }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-zinc-500 text-center py-4">Sin datos históricos</p>
                @endif
            </div>
        </div>

        {{-- Salud del Sistema --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Salud del Sistema</flux:heading>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500">Usuarios activos hoy</span>
                        <span class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ $data['salud_sistema']['usuarios_activos_hoy'] }}/{{ $data['salud_sistema']['total_usuarios'] }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500">Solicitudes hoy</span>
                        <span class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ $data['salud_sistema']['solicitudes_hoy'] }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500">Tiempo promedio</span>
                        <span class="text-lg font-semibold {{ $data['salud_sistema']['tiempo_promedio_dias'] <= 3 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $data['salud_sistema']['tiempo_promedio_dias'] }} días
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500">Tasa error SAT</span>
                        <span class="text-lg font-semibold {{ $data['salud_sistema']['tasa_error_sat_pct'] < 5 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $data['salud_sistema']['tasa_error_sat_pct'] }}%
                        </span>
                    </div>
                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-zinc-500">Presupuestos activos</span>
                            <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ $data['salud_sistema']['presupuestos_activos'] }}
                            </span>
                        </div>
                        @if($data['salud_sistema']['presupuestos_con_alertas'] > 0)
                            <div class="flex items-center gap-1 mt-2 text-xs text-rose-600 dark:text-rose-400">
                                <flux:icon.exclamation-triangle class="size-3" />
                                <span>{{ $data['salud_sistema']['presupuestos_con_alertas'] }} con alertas</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">Governance</flux:heading>
                    <!--<flux:button size="sm" variant="ghost" href="" icon="arrow-right" icon-trailing>
                        Ver detalle
                    </flux:button>-->
                </div>
                <div class="space-y-3">
                    <div class="flex items-start gap-2">
                        <flux:icon.document-text class="size-4 text-zinc-400 shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-xs text-zinc-500">Cambios en políticas</p>
                            <p class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ $data['governance']['cambios_politicas_mes'] }}
                                @if($data['governance']['es_mayor_promedio'])
                                    <span class="text-xs text-amber-600">↑ vs promedio</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($data['governance']['usuarios_inactivos_30dias'] > 0)
                        <div class="flex items-start gap-2">
                            <flux:icon.user-circle class="size-4 text-amber-500 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-xs text-zinc-500">Usuarios inactivos (30d)</p>
                                <p class="text-lg font-semibold text-amber-600">
                                    {{ $data['governance']['usuarios_inactivos_30dias'] }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($data['governance']['solicitudes_montos_inusuales'] > 0)
                        <div class="flex items-start gap-2">
                            <flux:icon.chart-bar class="size-4 text-rose-500 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-xs text-zinc-500">Montos inusuales</p>
                                <p class="text-lg font-semibold text-rose-600">
                                    {{ $data['governance']['solicitudes_montos_inusuales'] }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($data['governance']['accesos_fuera_horario'] > 0)
                        <div class="flex items-start gap-2">
                            <flux:icon.clock class="size-4 text-amber-500 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-xs text-zinc-500">Accesos fuera de horario</p>
                                <p class="text-lg font-semibold text-amber-600">
                                    {{ $data['governance']['accesos_fuera_horario'] }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($data['governance']['transferencias_presupuesto'] > 0)
                        <div class="flex items-start gap-2">
                            <flux:icon.arrows-right-left class="size-4 text-blue-500 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-xs text-zinc-500">Transferencias de presupuesto</p>
                                <p class="text-lg font-semibold text-blue-600">
                                    {{ $data['governance']['transferencias_presupuesto'] }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($data['governance']['usuarios_inactivos_30dias'] === 0 &&
                        $data['governance']['solicitudes_montos_inusuales'] === 0 &&
                        $data['governance']['accesos_fuera_horario'] === 0)
                        <div class="py-4 text-center">
                            <flux:icon.shield-check class="mx-auto size-6 text-emerald-500" />
                            <p class="mt-1 text-xs text-zinc-500">Sin anomalías detectadas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
