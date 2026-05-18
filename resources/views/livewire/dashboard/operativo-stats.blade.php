<div class="flex h-full w-full flex-1 flex-col gap-4">
    @if($data['acciones_pendientes']['rechazadas'] > 0 ||
        $data['acciones_pendientes']['borradores'] > 0 ||
        $data['acciones_pendientes']['gastos_sin_comprobar'] > 0 ||
        $data['acciones_pendientes']['comprobantes_rechazados'] > 0)
        <flux:card class="border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30">
            <div class="flex items-start gap-3">
                <flux:icon.exclamation-triangle class="size-5 text-amber-500 shrink-0 mt-0.5" />
                <div class="flex-1">
                    <flux:heading size="sm" class="text-amber-800 dark:text-amber-200">
                        Acciones pendientes
                    </flux:heading>
                    <div class="mt-3 space-y-2">
                        @if($data['acciones_pendientes']['rechazadas'] > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-amber-700 dark:text-amber-300">
                                    {{ $data['acciones_pendientes']['rechazadas'] }} solicitud(es) rechazada(s)
                                </span>
                                <flux:button size="sm" variant="ghost" href="{{ route('solicitudes.index', ['estatus' => 'Rechazado']) }}">
                                    Corregir
                                </flux:button>
                            </div>
                        @endif

                        @if($data['acciones_pendientes']['borradores'] > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-amber-700 dark:text-amber-300">
                                    {{ $data['acciones_pendientes']['borradores'] }} solicitud(es) en borrador
                                </span>
                                <flux:button size="sm" variant="ghost" href="{{ route('solicitudes.index') }}">
                                    Completar
                                </flux:button>
                            </div>
                        @endif

                        @if($data['acciones_pendientes']['gastos_sin_comprobar'] > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-amber-700 dark:text-amber-300">
                                    {{ $data['acciones_pendientes']['gastos_sin_comprobar'] }} gasto(s) sin comprobar
                                    @if($data['acciones_pendientes']['proximos_vencer'] > 0)
                                        <span class="text-rose-600 dark:text-rose-400 font-semibold">
                                            ({{ $data['acciones_pendientes']['proximos_vencer'] }} vencen pronto)
                                        </span>
                                    @endif
                                </span>
                                <flux:button size="sm" variant="ghost" href="{{ route('solicitudes.index', ['estatus' => 'Autorizado']) }}">
                                    Comprobar
                                </flux:button>
                            </div>
                        @endif

                        @if($data['acciones_pendientes']['comprobantes_rechazados'] > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-amber-700 dark:text-amber-300">
                                    {{ $data['acciones_pendientes']['comprobantes_rechazados'] }} comprobante(s) rechazado(s)
                                </span>
                                <flux:button size="sm" variant="ghost" href="{{ route('solicitudes.index', ['estatus' => 'Autorizado']) }}">
                                    Resubir
                                </flux:button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Grid Principal: 3 columnas --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">

        {{-- Card 1: Presupuesto Mensual --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">Mi Presupuesto</flux:heading>
                    @if($data['presupuesto']['tiene_presupuesto'])
                        <flux:badge :color="match($data['presupuesto']['severidad']) {
                            'agotado', 'critico' => 'red',
                            'alerta' => 'yellow',
                            default => 'green'
                        }" size="sm">
                            {{ $data['presupuesto']['porcentaje'] }}%
                        </flux:badge>
                    @endif
                </div>

                @if($data['presupuesto']['tiene_presupuesto'])
                    <div class="space-y-3">
                        <div>
                            <div class="flex items-baseline justify-between">
                                <span class="text-xs text-zinc-500">Gastado</span>
                                <span class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ Number::currency($data['presupuesto']['gastado'], in: 'MXN') }}
                                </span>
                            </div>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xs text-zinc-500">Total</span>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ Number::currency($data['presupuesto']['total'], in: 'MXN') }}
                                </span>
                            </div>
                        </div>

                        {{-- Barra de progreso --}}
                        <div class="h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                            <div
                                class="h-full transition-all duration-500 {{ match($data['presupuesto']['severidad']) {
                                    'agotado', 'critico' => 'bg-gradient-to-r from-rose-500 to-rose-600',
                                    'alerta' => 'bg-gradient-to-r from-amber-500 to-amber-600',
                                    default => 'bg-gradient-to-r from-emerald-500 to-emerald-600'
                                } }}"
                                style="width: {{ min($data['presupuesto']['porcentaje'], 100) }}%"
                            ></div>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-500">
                                Disponible:
                                <span class="font-semibold text-zinc-700 dark:text-zinc-300">
                                    {{ Number::currency($data['presupuesto']['disponible'], in: 'MXN') }}
                                </span>
                            </span>
                            <span class="text-zinc-400">
                                {{ $data['presupuesto']['dias_restantes'] }} días restantes
                            </span>
                        </div>

                        @if($data['presupuesto']['proyeccion'] > $data['presupuesto']['total'])
                            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800">
                                <div class="flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400">
                                    <flux:icon.exclamation-triangle class="size-3" />
                                    <span>
                                        Proyección: {{ Number::currency($data['presupuesto']['proyeccion'], in: 'MXN') }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-6 text-center">
                        <flux:icon.information-circle class="mx-auto size-8 text-zinc-400" />
                        <p class="mt-2 text-sm text-zinc-500">
                            No tienes presupuesto asignado
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Card 2: Resumen Rápido --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="p-6">
                <flux:heading size="sm" class="mb-4">Resumen del Mes</flux:heading>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Solicitudes</span>
                        <span class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ $data['resumen']['solicitudes_mes'] }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Autorizadas</span>
                        <span class="text-lg font-semibold text-emerald-600">
                            {{ $data['resumen']['autorizadas'] }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">En revisión</span>
                        <span class="text-lg font-semibold text-amber-600">
                            {{ $data['resumen']['en_revision'] }}
                        </span>
                    </div>
                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-zinc-500">Comprobación</span>
                            <span class="text-sm font-semibold {{ $data['resumen']['pct_comprobacion'] >= 90 ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $data['resumen']['pct_comprobacion'] }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Tip del Día --}}
        <div class="relative overflow-hidden rounded-xl border border-blue-200 dark:border-blue-800 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900">
            <div class="p-6">
                <div class="flex items-start gap-3">
                    <flux:icon.light-bulb class="size-5 text-blue-500 shrink-0 mt-0.5" />
                    <div>
                        <flux:heading size="sm" class="text-blue-800 dark:text-blue-200 mb-2">
                            💡 Tip del Día
                        </flux:heading>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            {{ $data['tip_del_dia'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Últimas Solicitudes --}}
    <div class="relative flex-1 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="sm">Últimas Solicitudes</flux:heading>
                <flux:button size="sm" variant="ghost" href="{{ route('solicitudes.index') }}" icon="arrow-right" icon-trailing="chevron-down">
                    Ver todas
                </flux:button>
            </div>

            @if($data['ultimas_solicitudes']->isEmpty())
                <div class="py-12 text-center">
                    <flux:icon.document-text class="mx-auto size-12 text-zinc-300" />
                    <p class="mt-2 text-sm text-zinc-500">No tienes solicitudes aún</p>
                    <flux:button class="mt-4" href="{{ route('solicitudes.index') }}">
                        Crear primera solicitud
                    </flux:button>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($data['ultimas_solicitudes'] as $sol)
                        <div class="flex items-center justify-between p-3 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                        {{ $sol['folio'] }}
                                    </span>
                                    <flux:badge :color="match($sol['estatus']) {
                                        'Autorizado', 'Comprobado' => 'green',
                                        'Pendiente' => 'yellow',
                                        'Rechazado' => 'red',
                                        default => 'zinc'
                                    }" size="sm">
                                        {{ $sol['estatus'] }}
                                    </flux:badge>
                                </div>
                                <p class="text-xs text-zinc-500 mt-1">
                                    {{ $sol['concepto'] }} · {{ $sol['fecha'] }}
                                </p>
                            </div>
                            <span class="ml-4 font-mono text-sm font-semibold text-zinc-800 dark:text-zinc-100 shrink-0">
                                {{ Number::currency($sol['monto'], in: 'MXN') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
