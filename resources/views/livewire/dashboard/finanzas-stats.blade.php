<div class="flex h-full w-full flex-1 flex-col gap-4">

    {{-- Alertas Fiscales --}}
    @php
        $tieneAlertas = $data['alertas_fiscales']['cfdis_pendientes_sat'] > 0 ||
                        $data['alertas_fiscales']['cfdis_cancelados'] > 0 ||
                        $data['alertas_fiscales']['validacion_manual_pendiente'] > 0 ||
                        $data['alertas_fiscales']['excepciones_n2'] > 0 ||
                        $data['alertas_fiscales']['presupuestos_alerta'] > 0;
    @endphp

    @if($tieneAlertas)
        <flux:card class="border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/30">
            <div class="flex items-start gap-3">
                <flux:icon.shield-exclamation class="size-5 text-rose-500 shrink-0 mt-0.5" />
                <div class="flex-1">
                    <flux:heading size="sm" class="text-rose-800 dark:text-rose-200">
                        Alertas Fiscales y Operativas
                    </flux:heading>
                    <div class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-3">
                        @if($data['alertas_fiscales']['cfdis_pendientes_sat'] > 0)
                            <div class="flex items-center gap-2">
                                <flux:icon.document-text class="size-4 text-amber-500" />
                                <div>
                                    <p class="text-lg font-bold text-rose-600 dark:text-rose-400">
                                        {{ $data['alertas_fiscales']['cfdis_pendientes_sat'] }}
                                    </p>
                                    <p class="text-xs text-rose-700 dark:text-rose-300">
                                        CFDIs pendientes SAT
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($data['alertas_fiscales']['cfdis_cancelados'] > 0)
                            <div class="flex items-center gap-2">
                                <flux:icon.x-circle class="size-4 text-rose-500" />
                                <div>
                                    <p class="text-lg font-bold text-rose-600 dark:text-rose-400">
                                        {{ $data['alertas_fiscales']['cfdis_cancelados'] }}
                                    </p>
                                    <p class="text-xs text-rose-700 dark:text-rose-300">
                                        CFDIs cancelados
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($data['alertas_fiscales']['validacion_manual_pendiente'] > 0)
                            <div class="flex items-center gap-2">
                                <flux:icon.document-check class="size-4 text-amber-500" />
                                <div>
                                    <p class="text-lg font-bold text-amber-600 dark:text-amber-400">
                                        {{ $data['alertas_fiscales']['validacion_manual_pendiente'] }}
                                    </p>
                                    <p class="text-xs text-amber-700 dark:text-amber-300">
                                        Validación manual pendiente
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($data['alertas_fiscales']['excepciones_n2'] > 0)
                            <div class="flex items-center gap-2">
                                <flux:icon.exclamation-triangle class="size-4 text-amber-500" />
                                <div>
                                    <p class="text-lg font-bold text-amber-600 dark:text-amber-400">
                                        {{ $data['alertas_fiscales']['excepciones_n2'] }}
                                    </p>
                                    <p class="text-xs text-amber-700 dark:text-amber-300">
                                        Excepciones N2
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($data['alertas_fiscales']['presupuestos_alerta'] > 0)
                            <div class="flex items-center gap-2">
                                <flux:icon.chart-bar class="size-4 text-rose-500" />
                                <div>
                                    <p class="text-lg font-bold text-rose-600 dark:text-rose-400">
                                        {{ $data['alertas_fiscales']['presupuestos_alerta'] }}
                                    </p>
                                    <p class="text-xs text-rose-700 dark:text-rose-300">
                                        Presupuestos en alerta
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 flex gap-2">
                        <flux:button size="sm" variant="primary" color="rose" href="{{ route('validaciones.index') }}">
                            Ir a validaciones
                        </flux:button>
                        <flux:button size="sm" variant="ghost" href="{{ route('presupuestos.alertas') }}">
                            Ver presupuestos
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Grid Principal: Panorama Financiero --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">

        {{-- Panorama Financiero --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 md:col-span-2">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">Panorama Financiero (Mensual)</flux:heading>
                    @if($data['panorama_financiero']['tiene_presupuestos'])
                        <flux:badge :color="$data['panorama_financiero']['porcentaje_gastado'] > 90 ? 'red' : ($data['panorama_financiero']['porcentaje_gastado'] > 80 ? 'yellow' : 'green')" size="sm">
                            {{ $data['panorama_financiero']['porcentaje_gastado'] }}%
                        </flux:badge>
                    @endif
                </div>

                @if($data['panorama_financiero']['tiene_presupuestos'])
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <span class="text-xs text-zinc-500">Presupuesto Total</span>
                            <p class="text-xl font-bold text-zinc-800 dark:text-zinc-100 mt-1">
                                {{ Number::currency($data['panorama_financiero']['presupuesto_total'], in: 'MXN') }}
                            </p>
                        </div>
                        <div>
                            <span class="text-xs text-zinc-500">Gastado</span>
                            <p class="text-xl font-bold text-rose-600 mt-1">
                                {{ Number::currency($data['panorama_financiero']['gastado'], in: 'MXN') }}
                            </p>
                        </div>
                        <div>
                            <span class="text-xs text-zinc-500">Comprometido</span>
                            <p class="text-xl font-bold text-amber-600 mt-1">
                                {{ Number::currency($data['panorama_financiero']['comprometido'], in: 'MXN') }}
                            </p>
                        </div>
                        <div>
                            <span class="text-xs text-zinc-500">Disponible Real</span>
                            <p class="text-xl font-bold text-emerald-600 mt-1">
                                {{ Number::currency($data['panorama_financiero']['disponible_real'], in: 'MXN') }}
                            </p>
                        </div>
                    </div>

                    {{-- Barra de progreso con segmentos --}}
                    <div class="mb-4">
                        <div class="h-3 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden flex">
                            @php
                                $total = $data['panorama_financiero']['presupuesto_total'];
                                $pctGastado = $total > 0 ? ($data['panorama_financiero']['gastado'] / $total) * 100 : 0;
                                $pctComprometido = $total > 0 ? ($data['panorama_financiero']['comprometido'] / $total) * 100 : 0;
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

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <div>
                            <span class="text-xs text-zinc-500">Por Comprobar</span>
                            <p class="text-lg font-semibold text-zinc-800 dark:text-zinc-100 mt-1">
                                {{ Number::currency($data['panorama_financiero']['por_comprobar'], in: 'MXN') }}
                            </p>
                        </div>
                        <div>
                            <span class="text-xs text-zinc-500">Proyección Cierre</span>
                            <p class="text-lg font-semibold {{ $data['panorama_financiero']['proyeccion_cierre'] > $data['panorama_financiero']['presupuesto_total'] ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                                {{ Number::currency($data['panorama_financiero']['proyeccion_cierre'], in: 'MXN') }}
                            </p>
                        </div>
                    </div>

                    @if($data['panorama_financiero']['alertas_criticas'] > 0)
                        <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2 text-sm text-rose-600 dark:text-rose-400">
                                <flux:icon.exclamation-triangle class="size-4" />
                                <span>{{ $data['panorama_financiero']['alertas_criticas'] }} presupuesto(s) en estado crítico</span>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="py-8 text-center">
                        <flux:icon.chart-bar class="mx-auto size-12 text-zinc-400" />
                        <p class="mt-2 text-sm text-zinc-500">
                            No hay presupuestos configurados para este mes
                        </p>
                        <flux:button class="mt-4" href="">
                            Configurar presupuestos
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Compliance Fiscal --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Compliance Fiscal</flux:heading>

                @if($data['compliance_fiscal']['total'] > 0)
                    <div class="space-y-4">
                        {{-- Gráfica de dona simplificada con texto --}}
                        <div class="text-center">
                            <div class="text-4xl font-bold text-emerald-600">
                                {{ $data['compliance_fiscal']['pct_vigentes'] }}%
                            </div>
                            <p class="text-xs text-zinc-500 mt-1">CFDIs Vigentes</p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2">
                                    <flux:icon.check-circle class="size-4 text-emerald-500" />
                                    <span class="text-zinc-600 dark:text-zinc-400">Vigentes</span>
                                </span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ $data['compliance_fiscal']['vigentes'] }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2">
                                    <flux:icon.x-circle class="size-4 text-rose-500" />
                                    <span class="text-zinc-600 dark:text-zinc-400">Cancelados</span>
                                </span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ $data['compliance_fiscal']['cancelados'] }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2">
                                    <flux:icon.clock class="size-4 text-amber-500" />
                                    <span class="text-zinc-600 dark:text-zinc-400">Sin validar</span>
                                </span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ $data['compliance_fiscal']['sin_validar'] }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <p class="text-xs text-zinc-500 mb-2">Top RFCs</p>
                            @foreach($data['compliance_fiscal']['top_rfcs'] as $rfc)
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-zinc-600 dark:text-zinc-400 truncate flex-1">
                                        {{ $rfc['nombre'] }}
                                    </span>
                                    <span class="font-mono text-zinc-800 dark:text-zinc-100 ml-2">
                                        {{ $rfc['cfdis'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="py-6 text-center">
                        <flux:icon.document-text class="mx-auto size-8 text-zinc-400" />
                        <p class="mt-2 text-sm text-zinc-500">Sin CFDIs este mes</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Análisis por Área --}}
    <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
        <div class="p-6">
            <flux:heading size="sm" class="mb-4">Análisis por Área</flux:heading>

            @if(!empty($data['analisis_areas']))
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="text-left py-2 px-3 text-xs font-medium text-zinc-500">Área</th>
                                <th class="text-right py-2 px-3 text-xs font-medium text-zinc-500">Presupuesto</th>
                                <th class="text-right py-2 px-3 text-xs font-medium text-zinc-500">Gastado</th>
                                <th class="text-right py-2 px-3 text-xs font-medium text-zinc-500">%</th>
                                <th class="text-right py-2 px-3 text-xs font-medium text-zinc-500">Proyección</th>
                                <th class="text-center py-2 px-3 text-xs font-medium text-zinc-500">Riesgo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['analisis_areas'] as $area)
                                <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-zinc-800 dark:text-zinc-100">
                                                {{ $area['area'] }}
                                            </span>
                                            @if(!$area['tiene_presupuesto'])
                                                <flux:badge color="zinc" size="sm">Sin presupuesto</flux:badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono text-zinc-700 dark:text-zinc-300">
                                        {{ Number::currency($area['presupuesto'], in: 'MXN') }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono text-zinc-700 dark:text-zinc-300">
                                        {{ Number::currency($area['gastado'], in: 'MXN') }}
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <span class="font-semibold {{ $area['porcentaje'] > 90 ? 'text-rose-600' : ($area['porcentaje'] > 80 ? 'text-amber-600' : 'text-emerald-600') }}">
                                            {{ $area['porcentaje'] }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono text-zinc-700 dark:text-zinc-300">
                                        {{ Number::currency($area['proyeccion'], in: 'MXN') }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <flux:badge :color="match($area['riesgo']) {
                                            'critico' => 'red',
                                            'alerta' => 'yellow',
                                            default => 'green'
                                        }" size="sm">
                                            {{ ucfirst($area['riesgo']) }}
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

    {{-- Sección Inferior: Métricas y Auditoría --}}
    <div class="grid gap-4 md:grid-cols-2">

        {{-- Métricas de Operación --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Métricas de Operación</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs text-zinc-500">Solicitudes Procesadas</span>
                        <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100 mt-1">
                            {{ $data['metricas_operacion']['solicitudes_procesadas'] }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-500">Tiempo Promedio</span>
                        <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100 mt-1">
                            {{ $data['metricas_operacion']['tiempo_promedio_dias'] }} días
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-500">Tasa Aprobación</span>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">
                            {{ $data['metricas_operacion']['tasa_aprobacion_pct'] }}%
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-500">Gasto Promedio</span>
                        <p class="text-lg font-bold text-zinc-800 dark:text-zinc-100 mt-1">
                            {{ Number::currency($data['metricas_operacion']['gasto_promedio'], in: 'MXN') }}
                        </p>
                    </div>
                </div>
                @if($data['metricas_operacion']['pct_con_presupuesto'] < 100)
                    <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2 text-xs text-amber-600 dark:text-amber-400">
                            <flux:icon.exclamation-triangle class="size-3" />
                            <span>
                                Solo {{ $data['metricas_operacion']['pct_con_presupuesto'] }}% de solicitudes tienen presupuesto asignado
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Auditoría --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">Auditoría y Control</flux:heading>
                    <flux:button size="sm" variant="ghost" href="{{ route('auditoria.index') }}" icon="arrow-right" icon-trailing="chevron-down">
                        Ver todo
                    </flux:button>
                </div>
                <div class="space-y-3">
                    @if($data['auditoria']['duplicados_sospechosos'] > 0)
                        <div class="flex items-start gap-2 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                            <flux:icon.exclamation-triangle class="size-4 text-amber-500 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                    {{ $data['auditoria']['duplicados_sospechosos'] }} gastos con posible duplicación
                                </p>
                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                    Requieren revisión manual
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($data['auditoria']['empleados_excepciones_recurrentes'] > 0)
                        <div class="flex items-start gap-2 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800">
                            <flux:icon.user-group class="size-4 text-rose-500 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-rose-800 dark:text-rose-200">
                                    {{ $data['auditoria']['empleados_excepciones_recurrentes'] }} empleado(s) con 3+ excepciones consecutivas
                                </p>
                                <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                                    Revisar patrones de comportamiento
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($data['auditoria']['presupuestos_excedidos'] > 0)
                        <div class="flex items-start gap-2 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800">
                            <flux:icon.chart-bar class="size-4 text-rose-500 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-rose-800 dark:text-rose-200">
                                    {{ $data['auditoria']['presupuestos_excedidos'] }} presupuesto(s) excedido(s)
                                </p>
                                <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                                    Sobregiro detectado
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($data['auditoria']['proveedores_nuevos_semana'] > 0)
                        <div class="flex items-start gap-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                            <flux:icon.building-storefront class="size-4 text-blue-500 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                    {{ $data['auditoria']['proveedores_nuevos_semana'] }} proveedor(es) nuevo(s) esta semana
                                </p>
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                    Validar contra lista negra
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($data['auditoria']['cfdis_inusuales'] > 0)
                        <div class="flex items-start gap-2 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                            <flux:icon.document-text class="size-4 text-amber-500 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                    {{ $data['auditoria']['cfdis_inusuales'] }} CFDI(s) con monto inusual
                                </p>
                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                    >3σ del promedio
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($data['auditoria']['duplicados_sospechosos'] === 0 &&
                        $data['auditoria']['empleados_excepciones_recurrentes'] === 0 &&
                        $data['auditoria']['presupuestos_excedidos'] === 0 &&
                        $data['auditoria']['proveedores_nuevos_semana'] === 0 &&
                        $data['auditoria']['cfdis_inusuales'] === 0)
                        <div class="py-6 text-center">
                            <flux:icon.check-circle class="mx-auto size-8 text-emerald-500" />
                            <p class="mt-2 text-sm text-zinc-500">
                                Todo en orden
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Presupuestos Críticos --}}
    @if(!empty($data['presupuestos_criticos']) && count($data['presupuestos_criticos']) > 0)
        <div class="relative overflow-hidden rounded-xl border border-rose-200 dark:border-rose-800 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <div class="flex items-center gap-2 mb-4">
                    <flux:icon.exclamation-triangle class="size-5 text-rose-500" />
                    <flux:heading size="sm" class="text-rose-800 dark:text-rose-200">
                        Presupuestos en Estado Crítico
                    </flux:heading>
                </div>
                <div class="space-y-2">
                    @foreach($data['presupuestos_criticos'] as $p)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                        {{ $p['codigo'] }}
                                    </span>
                                    <flux:badge color="zinc" size="sm">{{ ucfirst($p['tipo']) }}</flux:badge>
                                    <flux:badge :color="match($p['severidad']) {
                                        'agotado', 'critico' => 'red',
                                        'alerta' => 'yellow',
                                        default => 'zinc'
                                    }" size="sm">
                                        {{ $p['porcentaje'] }}%
                                    </flux:badge>
                                </div>
                                <p class="text-xs text-zinc-500 mt-1">
                                    {{ $p['entidad'] }} · Disponible: {{ Number::currency($p['disponible'], in: 'MXN') }}
                                    @if($p['dias_restantes'])
                                        · {{ $p['dias_restantes'] }} días restantes
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>
