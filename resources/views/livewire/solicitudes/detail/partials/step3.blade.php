<div class="space-y-6">
    @include('livewire.solicitudes.detail.partials._header', [
        'badge'      => 'Comprobación de gastos',
        'badgeColor' => 'green',
    ])

    <div class="grid gap-3 grid-cols-2 sm:grid-cols-4">

        @php
            $totalGastos      = count($gastos);
            $comprobados      = collect($gastos)->where('estatus', 'comprobado')->count();
            $pendientes       = collect($gastos)->where('estatus', 'pendiente')->count();
            $conExcepcion     = collect($gastos)->whereIn('estatus', ['excepcion'])->count();
            $rechazados       = collect($gastos)->where('estatus', 'rechazado')->count();

            $montoComprobado = collect($gastos)
                ->where('estatus', 'comprobado')
                ->sum(fn($g) => $g['monto_real'] ?? 0);

            $pctComprobado = $solicitud->monto_total > 0
                ? round(($montoComprobado / $solicitud->monto_total) * 100, 1)
                : 0;
        @endphp

        <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div>
                <span class="text-xs uppercase text-zinc-400">Comprobados</span>
                <p class="text-2xl font-semibold text-emerald-600">{{ $comprobados }}/{{ $totalGastos }}</p>
                <span class="text-xs text-zinc-400">{{ $pctComprobado }}%</span>
            </div>
            <flux:icon.check-circle class="size-5 text-emerald-500" />
        </div>

        <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div>
                <span class="text-xs uppercase text-zinc-400">Pendientes</span>
                <p class="text-2xl font-semibold text-amber-500">{{ $pendientes }}</p>
            </div>
            <flux:icon.clock class="size-5 text-amber-500" />
        </div>

        <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div>
                <span class="text-xs uppercase text-zinc-400">Con excepción</span>
                <p class="text-2xl font-semibold text-rose-500">{{ $conExcepcion }}</p>
            </div>
            <flux:icon.exclamation-triangle class="size-5 text-rose-500" />
        </div>

        <div class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div>
                <span class="text-xs uppercase text-zinc-400">Rechazados</span>
                <p class="text-2xl font-semibold text-zinc-500">{{ $rechazados }}</p>
            </div>
            <flux:icon.x-circle class="size-5 text-zinc-400" />
        </div>

    </div>

    <flux:card>
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="sm">Progreso de comprobación</flux:heading>
                <flux:subheading>
                    {{ $comprobados }} de {{ $totalGastos }} gastos comprobados
                </flux:subheading>
            </div>

            @php
                $pctCompletado = $totalGastos > 0 ? round(($comprobados / $totalGastos) * 100) : 0;
            @endphp

            <div class="text-right">
                <p class="text-3xl font-semibold {{ $pctCompletado === 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $pctCompletado }}%
                </p>
                <p class="text-xs text-zinc-400 mt-1">
                    @if ($pctCompletado === 100)
                        <span class="flex items-center gap-1">
                            <flux:icon.check-circle class="size-3 text-emerald-500" />
                            Todos los gastos comprobados
                        </span>
                    @elseif ($pendientes > 0)
                        Faltan {{ $pendientes }} por comprobar
                    @elseif ($conExcepcion > 0)
                        {{ $conExcepcion }} en revisión de excepción
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-4 h-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
            <div
                class="h-full transition-all duration-500 ease-out {{ $pctCompletado === 100 ? 'bg-linear-to-r from-emerald-500 to-emerald-600' : 'bg-gradient-to-r from-amber-500 to-amber-600' }}"
                style="width: {{ $pctCompletado }}%"
            ></div>
        </div>

        <div class="mt-3 flex items-center gap-4 text-xs text-zinc-500">
            <span class="flex items-center gap-1">
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                {{ $comprobados }} comprobados
            </span>
            <span class="flex items-center gap-1">
                <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                {{ $pendientes }} pendientes
            </span>
            @if ($conExcepcion > 0)
                <span class="flex items-center gap-1">
                    <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                    {{ $conExcepcion }} con excepción
                </span>
            @endif
            @if ($rechazados > 0)
                <span class="flex items-center gap-1">
                    <div class="w-2 h-2 rounded-full bg-zinc-400"></div>
                    {{ $rechazados }} rechazados
                </span>
            @endif
        </div>
    </flux:card>

    @if (!empty($compartidosPendientes))
        <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/20 overflow-hidden">
            <div class="px-4 py-3 border-b border-blue-200 dark:border-blue-800 flex items-center gap-2">
                <flux:icon.user-group class="size-4 text-blue-500" />
                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Gastos compartidos pendientes de vinculación
                </span>
            </div>
            <div class="divide-y divide-blue-100 dark:divide-blue-900">
                @foreach ($compartidosPendientes as $cp)
                    <div class="px-4 py-3 flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                {{ $cp['pagador'] }} pagó {{ $cp['concepto'] }}
                            </span>
                            <span class="text-xs text-blue-500">
                                {{ $cp['folio_solicitud'] }} · {{ $cp['fecha'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-sm font-semibold text-blue-800 dark:text-blue-100">
                                {{ Number::currency($cp['monto_compartido'], in: 'MXN') }}
                            </span>
                            @php
                                $gastosVinculables = collect($gastos)
                                    ->where('estatus', 'pendiente')
                                    ->values();
                            @endphp
                            @if ($gastosVinculables->isNotEmpty())
                                <flux:dropdown>
                                    <flux:button size="sm" variant="primary" icon="link">
                                        Vincular
                                    </flux:button>
                                    <flux:menu>
                                        @foreach ($gastosVinculables as $gv)
                                            <flux:menu.item
                                                wire:click="vincularCompartido({{ $cp['id'] }}, {{ $gv['id'] }})">
                                                {{ $gv['concepto_nombre'] }}
                                                — {{ Number::currency($gv['monto_estimado'], in: 'MXN') }}
                                            </flux:menu.item>
                                        @endforeach
                                    </flux:menu>
                                </flux:dropdown>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <flux:card class="p-0">
        <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
            <flux:text size="sm" class="text-zinc-500">
                Gastos: <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $totalGastos }}</span>
            </flux:text>
            <flux:text size="sm" class="text-zinc-500 font-mono">
                Presupuesto: {{ Number::currency($solicitud->monto_total ?? 0, in: 'MXN') }}
            </flux:text>
        </div>

        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @foreach ($gastos as $gasto)
                @php
                    $estatusGasto = $gasto['estatus'];
                    $estaEnEdicion = $gastoActivo === $gasto['id'];

                    $estatusColor = match($estatusGasto) {
                        'comprobado' => 'green',
                        'aprobado'   => 'blue',
                        'excepcion'  => 'yellow',
                        'rechazado'  => 'red',
                        'pendiente'  => 'zinc',
                        default      => 'zinc',
                    };
                    $estatusLabel = match($estatusGasto) {
                        'comprobado' => 'Comprobado',
                        'aprobado'   => 'Aprobado',
                        'excepcion'  => 'En excepción',
                        'rechazado'  => 'Rechazado',
                        'pendiente'  => 'Pendiente',
                        default      => $estatusGasto,
                    };
                    $comprobanteColor = match($gasto['comprobante_requerido'] ?? 'ninguno') {
                        'cfdi'    => 'red',
                        'ticket'  => 'yellow',
                        'ninguno' => 'green',
                        default   => 'zinc',
                    };
                    $comprobanteLabel = match($gasto['comprobante_requerido'] ?? 'ninguno') {
                        'cfdi'    => 'CFDI',
                        'ticket'  => 'Ticket',
                        'ninguno' => 'Sin requisito',
                        default   => '—',
                    };
                @endphp

                <div class="px-4 py-4 space-y-4" wire:key="gasto-{{ $gasto['id'] }}">

                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex flex-col gap-0.5 min-w-0">
                            <span class="font-medium text-sm text-zinc-800 dark:text-zinc-100">
                                {{ $gasto['concepto_nombre'] }}
                            </span>
                            <div class="flex items-center gap-2 flex-wrap">
                                @if ($gasto['monto_estimado'])
                                    <span class="text-xs text-zinc-300">·</span>
                                    <span class="text-xs text-zinc-400 font-mono">
                                        Estimado: {{ Number::currency($gasto['monto_estimado'], in: 'MXN') }}
                                    </span>
                                @endif
                                @if ($gasto['limite_politica'])
                                    <span class="text-xs text-zinc-300">·</span>
                                    <span class="text-xs text-zinc-400 font-mono">
                                        Límite: {{ Number::currency($gasto['limite_politica'], in: 'MXN') }}
                                    </span>
                                @endif

                                @if ($gasto['tipo_limite_politica'])
                                    <span class="text-xs text-zinc-300">·</span>
                                    <span class="text-xs text-zinc-400 font-mono">
                                        Tipo: {{ $gasto['tipo_limite_politica'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($gasto['monto_real'])
                                <span class="font-mono text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ Number::currency($gasto['monto_real'], in: 'MXN') }}
                                </span>
                            @endif

                            <flux:badge :color="$comprobanteColor" size="sm">{{ $comprobanteLabel }}</flux:badge>
                            <flux:badge :color="$estatusColor" size="sm">{{ $estatusLabel }}</flux:badge>
                            @if ($estatusGasto === 'aprobado' && $gasto['tiene_excepcion'] === false && $gasto['monto_real'] > $gasto['limite_politica'])
                                <flux:badge color="blue" size="sm" icon="sparkles">
                                    Propina auto-aprobada
                                </flux:badge>
                            @endif
                            @if (in_array($estatusGasto, ['pendiente', 'aprobado'])
                                    && empty($gasto['compartido'])
                                    && !$estaEnEdicion
                                    )
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="user-plus"
                                    wire:click="abrirCompartido({{ $gasto['id'] }})"
                                    title="Este gasto lo compartí con alguien"
                                />
                            @endif
                            @if (in_array($estatusGasto, ['pendiente', 'aprobado']) && !$estaEnEdicion)
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="document-arrow-up"
                                    wire:click="abrirComprobacion({{ $gasto['id'] }})"
                                    title="Registrar monto y comprobante"
                                />
                            @elseif ($estatusGasto === 'excepcion')
                                <flux:badge color="yellow" size="sm">En revisión</flux:badge>
                            @elseif ($estatusGasto === 'comprobado')
                                <flux:badge color="green" size="sm">Comprobado</flux:badge>
                            @elseif ($estaEnEdicion)
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="x-mark"
                                    wire:click="cerrarComprobacion"
                                    title="Cancelar"
                                />
                            @endif
                        </div>
                    </div>

                    @if ($estaEnEdicion)
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 p-4 space-y-4">

                            <div class="flex items-center justify-between">
                                <flux:heading size="sm">Registrar comprobante</flux:heading>

                                @php
                                    $totalComprobado = collect($gasto['comprobantes'])->sum('monto');
                                    $montoObjetivo = $gasto['monto_estimado'];
                                    $falta = max(0, $montoObjetivo - $totalComprobado);
                                    $pctCubierto = $montoObjetivo > 0 ? round(($totalComprobado / $montoObjetivo) * 100, 1) : 0;
                                @endphp

                                <div class="text-xs text-zinc-500">
                                    Comprobado:
                                    <span class="font-semibold {{ $falta <= 0 ? 'text-green-600' : 'text-amber-600' }}">
                                        {{ Number::currency($totalComprobado, in: 'MXN') }}
                                    </span>
                                    / {{ Number::currency($montoObjetivo, in: 'MXN') }}
                                    <span class="text-[10px]">({{ $pctCubierto }}%)</span>
                                </div>
                            </div>

                            @if ($gastoConExcepcion === $gasto['id'])
                                <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 p-4 space-y-3">
                                    <div class="flex items-start gap-2">
                                        <flux:icon.exclamation-triangle class="size-4 text-amber-500 shrink-0 mt-0.5" />
                                        <div>
                                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                                El monto excede la política — se generó una excepción
                                            </p>
                                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                                                Límite: {{ Number::currency($gasto['limite_politica'], in: 'MXN') }}.
                                                Justifica el exceso para que el manager pueda resolverla.
                                            </p>
                                        </div>
                                    </div>

                                    <flux:field>
                                        <flux:label badge="Requerido">Justificación del exceso</flux:label>
                                        <flux:textarea
                                            wire:model="justificacionExcepcion"
                                            placeholder="Ej: El precio del combustible subió por el cierre de la carretera federal..."
                                            resize="none"
                                            rows="2"
                                        />
                                        <flux:error name="justificacionExcepcion" />
                                    </flux:field>

                                    <div class="flex justify-end">
                                        <flux:button
                                            variant="primary"
                                            color="amber"
                                            wire:click="guardarJustificacionExcepcion"
                                            wire:loading.attr="disabled"
                                            wire:target="guardarJustificacionExcepcion"
                                            >
                                            <span wire:loading.remove wire:target="guardarJustificacionExcepcion">
                                                Enviar justificación
                                            </span>
                                            <span wire:loading wire:target="guardarJustificacionExcepcion">Guardando…</span>
                                        </flux:button>
                                    </div>
                                </div>
                            @else
                                @if ($gasto['estatus'] === 'aprobado')
                                    <flux:description>
                                        Este gasto ya tiene monto aprobado. Solo necesitas subir el comprobante.
                                    </flux:description>
                                @endif
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @if ($gasto['estatus'] === 'pendiente')
                                        <flux:field>
                                            <flux:label badge="Requerido">Fecha del gasto</flux:label>
                                            <flux:date-picker selectable-header wire:model="fechaGastoReal" fixed-weeks />
                                            <flux:error name="fechaGastoReal" />
                                        </flux:field>
                                    @endif
                                    <flux:field>
                                        <flux:label badge="Requerido">Tipo de comprobante</flux:label>
                                        <flux:select variant="listbox" wire:model.live="tipoComprobante">
                                            <flux:select.option value="">Selecciona...</flux:select.option>
                                            @if (in_array('factura', $gasto['tipos_permitidos']))
                                                <flux:select.option value="factura">
                                                    Factura electrónica (XML + UUID)
                                                </flux:select.option>
                                            @endif
                                            @if (in_array('pdf', $gasto['tipos_permitidos']))
                                                <flux:select.option value="pdf">
                                                    Ticket / Recibo (PDF o imagen)
                                                </flux:select.option>
                                            @endif
                                            @if (in_array('sin_comprobante', $gasto['tipos_permitidos']))
                                                <flux:select.option value="sin_comprobante">
                                                    Sin comprobante
                                                </flux:select.option>
                                            @endif
                                        </flux:select>
                                        @if (!empty($gasto['limite_politica']))
                                            <flux:description>
                                                @php
                                                    $tp = $gasto['tipos_permitidos'];
                                                    $partes = [];
                                                    if (in_array('sin_comprobante', $tp)) $partes[] = 'Sin doc hasta el umbral libre';
                                                    if (in_array('pdf', $tp)) $partes[] = 'Ticket en zona intermedia';
                                                    if (in_array('factura', $tp)) $partes[] = 'CFDI en zona alta';
                                                @endphp
                                                Política: {{ implode(' · ', $partes) }}
                                            </flux:description>
                                        @endif
                                        <flux:error name="tipoComprobante" />
                                    </flux:field>
                                </div>
                                @include('livewire.solicitudes.detail.partials._archivo')
                            @endif
                            <div class="flex items-center flex-col sm:flex-row justify-end gap-3 sm:justify-between pt-2">
                                <span class="text-xs text-zinc-400">
                                    {{ count($gasto['comprobantes']) }} comprobante(s)
                                    @if ($falta > 0)
                                        · Falta: {{ Number::currency($falta, in: 'MXN') }}
                                    @endif
                                </span>
                                <div class="flex gap-3">
                                    <flux:button variant="ghost" wire:click="cerrarComprobacion">
                                        Cerrar
                                    </flux:button>
                                    <flux:button variant="primary" wire:click="guardarComprobacion">
                                        Agregar comprobante
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (!empty($gasto['comprobantes']))
                        <div class="space-y-1">
                            @foreach ($gasto['comprobantes'] as $comp)
                                @php
                                    $satColor = match($comp['sat_status'] ?? null) {
                                        'vigente'      => 'green',
                                        'cancelado'    => 'red',
                                        'no_encontrado'=> 'red',
                                        'pendiente'    => 'yellow',
                                        default        => 'zinc',
                                    };
                                    $manualColor = match($comp['validacion_manual'] ?? null) {
                                        'aprobado'  => 'green',
                                        'rechazado' => 'red',
                                        'pendiente' => 'yellow',
                                        default     => 'zinc',
                                    };
                                @endphp
                                <div class="flex items-center justify-between gap-2 rounded-md bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 px-3 py-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <flux:icon.document-text class="size-4 text-zinc-400 shrink-0" />
                                        <span class="text-xs font-mono text-zinc-600 dark:text-zinc-300 truncate">
                                            {{ $comp['tipo'] === 'factura' ? 'CFDI' : 'Ticket' }}
                                            · {{ Number::currency($comp['monto'], in: 'MXN') }}
                                        </span>
                                        @if ($comp['uuid'])
                                            <span class="text-[10px] font-mono text-zinc-400 truncate hidden sm:block">
                                                {{ strtoupper(substr($comp['uuid'], 0, 8)) }}…
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        @if ($comp['sat_status'])
                                            <flux:badge :color="$satColor" size="sm">
                                                SAT: {{ ucfirst($comp['sat_status']) }}
                                            </flux:badge>
                                        @endif
                                        @if ($comp['validacion_manual'])
                                            <flux:badge :color="$manualColor" size="sm">
                                                Manual: {{ ucfirst($comp['validacion_manual']) }}
                                            </flux:badge>
                                        @endif
                                        @if ($comp['tipo'] === 'factura' && ($comp['iva'] || $comp['ieps'] || $comp['ish']))
                                            <div class="ml-6 mt-1 flex items-center gap-3 text-[10px] font-mono text-zinc-400">
                                                @if ($comp['subtotal'])
                                                    <span>Subtotal: {{ Number::currency($comp['subtotal'], in: 'MXN') }}</span>
                                                @endif
                                                @if ($comp['iva'])
                                                    <span class="text-blue-500">IVA: {{ Number::currency($comp['iva'], in: 'MXN') }}</span>
                                                @endif
                                                @if ($comp['ieps'])
                                                    <span class="text-purple-500">IEPS: {{ Number::currency($comp['ieps'], in: 'MXN') }}</span>
                                                @endif
                                                @if ($comp['ish'])
                                                    <span class="text-orange-500">ISH: {{ Number::currency($comp['ish'], in: 'MXN') }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if (!empty($gasto['extension_tarjeta']))
                        <div class="space-y-1">
                            @php
                                $ext = $gasto['extension_tarjeta'];
                            @endphp
                            <a href="{{ route('tarjetas.show', $ext['ct_id']) }}"
                                class="flex items-center gap-1 text-[1rem] font-mono text-blue-500 hover:text-blue-700"
                                >
                                <flux:icon.credit-card class="size-3" />
                                {{ Number::currency($ext['monto'], in: 'MXN') }} → {{ $ext['ct_folio'] }}
                                @if ($ext['comprobado'])
                                    <flux:icon.check-circle class="size-4 text-emerald-500" />
                                @else
                                    <flux:badge color="{{ match($ext['estatus_ct']) {
                                        'abierta'     => 'cyan',
                                        'en_revision' => 'yellow',
                                        'conciliada'  => 'green',
                                        'rechazada'   => 'red',
                                        default       => 'zinc',
                                    } }}" size="sm">{{ $ext['estatus_ct'] }}</flux:badge>
                                @endif
                            </a>
                        </div>
                    @endif
                    @if (!empty($gasto['compartido']))
                        @php
                            $compartido = $gasto['compartido'];
                            $compartidoColor = match($compartido['estatus']) {
                                'vinculado'        => 'green',
                                'aprobado_cliente' => 'blue',
                                'pendiente'        => 'yellow',
                                default            => 'zinc',
                            };
                        @endphp
                        <flux:badge :color="$compartidoColor" size="sm">
                            Compartido: {{ Number::currency($compartido['monto_compartido'], in: 'MXN') }}
                            con {{ $compartido['receptor'] }}
                        </flux:badge>
                    @endif
                    @if ($marcandoCompartido && $gastoCompartidoActivo === $gasto['id'])
                        <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/30 p-4 space-y-4">
                            <flux:heading size="sm">¿Con quién compartiste este gasto?</flux:heading>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label badge="Requerido">Tipo</flux:label>
                                    <flux:select variant="listbox" wire:model.live="tipoCompartido">
                                        <flux:select.option value="empleado">Compañero de trabajo</flux:select.option>
                                        <flux:select.option value="cliente">Invitación a cliente</flux:select.option>
                                    </flux:select>
                                </flux:field>
                                <flux:field>
                                    <flux:label badge="Requerido">
                                        Monto que cargó el otro ($)
                                    </flux:label>
                                    <flux:input
                                        wire:model.live="montoCompartido"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        placeholder="Ej. 300.00 - lo que pagó la otra persona"
                                    />
                                    <flux:description>
                                        @php
                                            $gastoActualData = collect($gastos)->firstWhere('id', $gasto['id']);
                                            $miParte = $gastoActualData
                                                ? ($gastoActualData['monto_estimado'] - (float) $montoCompartido)
                                                : 0;
                                        @endphp
                                        Lo que queda para ti:
                                        <span class="font-semibold {{ $miParte < 0 ? 'text-rose-500' : 'text-emerald-600' }}">
                                            {{ Number::currency(max(0, $miParte), in: 'MXN') }}
                                        </span>
                                        @if ($miParte < 0)
                                            <span class="text-rose-500">— excede el total del gasto</span>
                                        @endif
                                    </flux:description>
                                    <flux:error name="montoCompartido" />
                                </flux:field>
                            </div>
                            @if ($tipoCompartido === 'empleado')
                                <flux:field>
                                    <flux:label badge="Requerido">Empleado receptor</flux:label>
                                    <flux:select variant="listbox" wire:model="empleadoReceptorId">
                                        <flux:select.option value="">Selecciona...</flux:select.option>
                                        @foreach ($empleadosDisponibles as $emp)
                                            <flux:select.option value="{{ $emp['id'] }}">
                                                {{ $emp['nombre_completo'] }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:description>
                                        Al receptor le aparecerá una notificación para vincular su comprobación.
                                    </flux:description>
                                    <flux:error name="empleadoReceptorId" />
                                </flux:field>
                            @else
                                <flux:field>
                                    <flux:label badge="Requerido">Descripción del cliente / invitado</flux:label>
                                    <flux:input
                                        wire:model="clienteDescripcion"
                                        placeholder="Ej. Juan Pérez — Empresa XYZ"
                                    />
                                    <flux:error name="clienteDescripcion" />
                                </flux:field>
                            @endif
                            <div class="flex justify-end gap-3">
                                <flux:button variant="ghost" wire:click="$set('marcandoCompartido', false)">
                                    Cancelar
                                </flux:button>
                                <flux:button variant="primary" wire:click="guardarCompartido"
                                    wire:loading.attr="disabled" wire:target="guardarCompartido">
                                    <span wire:loading.remove wire:target="guardarCompartido">Registrar gasto compartido</span>
                                    <span wire:loading wire:target="guardarCompartido">Guardando…</span>
                                </flux:button>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </flux:card>

</div>
