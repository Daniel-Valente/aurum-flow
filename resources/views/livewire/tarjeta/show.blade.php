<div class="space-y-6">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">{{ $comprobacion->folio }}</flux:heading>
                @php
                    $estatusColor = match($comprobacion->estatus) {
                        'abierta'     => 'cyan',
                        'en_revision' => 'yellow',
                        'conciliada'  => 'green',
                        'rechazada'   => 'red',
                        default       => 'zinc',
                    };
                    $estatusLabel = match($comprobacion->estatus) {
                        'abierta'     => 'Abierta',
                        'en_revision' => 'En revisión',
                        'conciliada'  => 'Conciliada',
                        'rechazada'   => 'Rechazada',
                        default       => $comprobacion->estatus,
                    };
                @endphp
                <flux:badge :color="$estatusColor">{{ $estatusLabel }}</flux:badge>
            </div>
            <p class="text-sm text-zinc-400 mt-1">
                Tarjeta corporativa
                · {{ $comprobacion->empleado->nombre_completo }}
            </p>
        </div>

        <div class="flex gap-2">
            @if ($comprobacion->estatus === 'abierta')
                <flux:modal.trigger name="enviar-a-revisar">
                    <flux:button
                        variant="primary"
                        icon="paper-airplane"
                        >
                        Enviar a revisión
                    </flux:button>
                </flux:modal.trigger>
            @endif

            @can('gastos.tarjeta.conciliar')
                @if ($comprobacion->estatus === 'en_revision')
                    <flux:button variant="primary" color="green" icon="check-circle"
                        wire:click="abrirConciliacion('conciliada')">
                        Conciliar
                    </flux:button>
                    <flux:button variant="danger" icon="x-circle"
                        wire:click="abrirConciliacion('rechazada')">
                        Rechazar
                    </flux:button>
                @endif
            @endcan
        </div>
    </div>

    <flux:card>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Proyecto</span>
                <span class="text-sm text-zinc-700 dark:text-zinc-200">
                    {{ $comprobacion->proyecto->nombre ?? '—' }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Inicio</span>
                <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                    {{ $comprobacion->fecha_inicio?->format('d/m/Y') }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Fin</span>
                <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                    {{ $comprobacion->fecha_fin?->format('d/m/Y') }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-3 py-2.5">
                <span class="text-[10px] uppercase tracking-wider text-zinc-400">Total cargado</span>
                <span class="text-sm font-mono font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ Number::currency($comprobacion->monto_total, in: 'MXN') }}
                </span>
            </div>
        </div>

        @if ($comprobacion->descripcion)
            <p class="mt-3 text-sm text-zinc-500">{{ $comprobacion->descripcion }}</p>
        @endif

        @if ($comprobacion->estatus === 'rechazada' && $comprobacion->motivo_rechazo)
            <div class="mt-3 flex items-start gap-2 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 px-3 py-2.5">
                <flux:icon.x-circle class="size-4 text-rose-500 shrink-0 mt-0.5" />
                <div>
                    <p class="text-xs font-medium text-rose-700 dark:text-rose-300">Motivo de rechazo</p>
                    <p class="text-sm text-rose-600 dark:text-rose-400">{{ $comprobacion->motivo_rechazo }}</p>
                </div>
            </div>
        @endif
    </flux:card>

    @if ($comprobacion->estatus === 'abierta')
        <flux:card>
            <flux:heading size="sm" class="mb-4">Agregar gastos al periodo</flux:heading>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label badge="Requerido">Concepto</flux:label>
                        <flux:select variant="listbox" wire:model="concepto_id">
                            <flux:select.option value="">Selecciona...</flux:select.option>
                            @foreach ($conceptos as $concepto)
                                <flux:select.option value="{{ $concepto['id'] }}">
                                    <span class="text-xs font-mono text-zinc-400">{{ $concepto['codigo'] }}</span>
                                    {{ $concepto['nombre'] }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="concepto_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label badge="Requerido">Fecha del gasto</flux:label>
                        <flux:date-picker selectable-header wire:model="fechaGasto" fixed-weeks />
                        <flux:error name="fechaGasto" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label badge="Requerido">
                        Facturas XML — tarjeta corporativa requiere CFDI
                    </flux:label>
                    <flux:file-upload
                        wire:model="archivosCfdi"
                        multiple
                        wire:change="procesarXmls"
                        >
                        <flux:file-upload.dropzone
                            heading="Arrastra uno o varios XML aquí"
                            text="Solo archivos .xml — el monto se extrae automáticamente"
                            with-progress
                            inline
                        />
                    </flux:file-upload>
                    <flux:error name="archivosCfdi" />
                </flux:field>

                @if (!empty($archivosCfdi))
                    <div class="space-y-2">
                        <p class="text-xs font-medium text-zinc-500">
                            {{ count($archivosCfdi) }} CFDI(s):
                        </p>
                        @foreach ($archivosCfdi as $idx => $cfdi)
                            @php
                                $tieneError = !empty($cfdi['error']);
                            @endphp
                            <div class="rounded-lg border {{ $tieneError ? 'border-rose-300 bg-rose-50 dark:border-rose-700 dark:bg-rose-950/30' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800' }} p-3 space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <flux:icon.document-text class="size-4 {{ $tieneError ? 'text-rose-400' : 'text-emerald-500' }} shrink-0" />
                                        <div class="min-w-0">
                                            @if ($tieneError)
                                                <p class="text-xs font-medium text-rose-600">{{ /*$cfdi['error']*/ }}</p>
                                            @else
                                                <p class="text-xs font-medium text-zinc-800 dark:text-zinc-100">{{ $cfdi['emisor'] }}</p>
                                                <p class="text-[10px] font-mono text-zinc-400">
                                                    {{ strtoupper(substr($cfdi['uuid'], 0, 8)) }}…
                                                    · {{ $cfdi['fecha'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if (!$tieneError)
                                            <span class="font-mono text-sm font-semibold">
                                                {{ Number::currency($cfdi['monto'], in: 'MXN') }}
                                            </span>
                                        @endif
                                        <flux:button size="xs" variant="ghost" icon="x-mark"
                                            wire:click="removeCfdi({{ $idx }})" />
                                    </div>
                                </div>

                                @if (!$tieneError)
                                    <div class="pl-6">
                                        @if (!empty($pdfsCfdi[$idx]))
                                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                                <flux:icon.paper-clip class="size-3" />
                                                <span class="truncate">
                                                    {{ $pdfsCfdi[$idx]->getClientOriginalName() }}
                                                </span>
                                                <flux:button size="xs" variant="ghost" icon="x-mark"
                                                    wire:click="$set('pdfsCfdi.{{ $idx }}', null)" />
                                            </div>
                                        @else
                                            <flux:file-upload with-progress inline wire:model="pdfsCfdi.{{ $idx }}">
                                                <flux:button size="xs" variant="ghost" icon="paper-clip">
                                                    Adjuntar PDF (recomendado)
                                                </flux:button>
                                            </flux:file-upload>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @php
                            $totalCfdi = collect($archivosCfdi)
                                ->filter(fn($c) => empty($c['error']))
                                ->sum(fn($c) => (float) $c['monto']);
                        @endphp
                        @if ($totalCfdi > 0)
                            <div class="flex justify-end">
                                <span class="text-xs font-mono text-zinc-500">
                                    Total a agregar:
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                                        {{ Number::currency($totalCfdi, in: 'MXN') }}
                                    </span>
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="flex justify-end">
                    <flux:button
                        variant="primary"
                        wire:click="guardarGastos"
                        wire:loading.attr="disabled"
                        wire:target="guardarGastos"
                    >
                        <span wire:loading.remove wire:target="guardarGastos">Agregar al periodo</span>
                        <span wire:loading wire:target="guardarGastos">Guardando…</span>
                    </flux:button>
                </div>
            </div>
        </flux:card>
    @endif

    <flux:card class="p-0">
        <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
            <flux:text size="sm" class="text-zinc-500">
                Gastos: <span class="font-semibold">{{ count($gastos) }}</span>
            </flux:text>
            <flux:text size="sm" class="font-mono text-zinc-500">
                Total: {{ Number::currency($comprobacion->monto_total, in: 'MXN') }}
            </flux:text>
        </div>

        @if (empty($gastos))
            <div class="py-12 text-center">
                <flux:icon name="inbox" class="size-8 text-zinc-300 dark:text-zinc-600 mx-auto" />
                <p class="mt-2 text-sm text-zinc-400">Sin gastos registrados</p>
            </div>
        @else
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($gastos as $gasto)
                    <div class="px-4 py-3 space-y-2" wire:key="gasto-{{ $gasto['id'] }}">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div class="flex flex-col gap-0.5">
                                <span class="font-medium text-sm">{{ $gasto['concepto_nombre'] }}</span>
                                <span class="text-xs text-zinc-400 font-mono">{{ $gasto['fecha_gasto'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-semibold text-sm">
                                    {{ Number::currency($gasto['monto_real'], in: 'MXN') }}
                                </span>
                                @if ($comprobacion->estatus === 'abierta')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="eliminarGasto({{ $gasto['id'] }})"
                                        wire:confirm="¿Eliminar este gasto y sus archivos?"
                                        class="text-rose-500 hover:text-rose-700"
                                    />
                                @endif
                            </div>
                        </div>

                        @foreach ($gasto['comprobantes'] as $comp)
                            @php
                                $satColor = match($comp['sat_status'] ?? null) {
                                    'vigente'       => 'green',
                                    'cancelado'     => 'red',
                                    'no_encontrado' => 'red',
                                    'pendiente'     => 'yellow',
                                    default         => 'zinc',
                                };
                            @endphp
                            <div class="flex items-start sm:items-center sm:justify-between flex-col sm:flex-row gap-2 rounded-md bg-zinc-50 dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 px-3 py-2 ml-4">
                                <div class="flex gap-2 justify-between sm:justify-start">
                                    <flux:icon.document-text class="size-4 text-zinc-400 shrink-0" />

                                    <div class="flex flex-col min-w-0 flex-1">
                                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-200 truncate">
                                            Ferreteria Indar
                                        </span>
                                        @if ($comp['uuid'])
                                            <span class="text-[10px] font-mono text-zinc-400">
                                                {{ strtoupper(substr($comp['uuid'], 0, 8)) }}…
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 shrink-0 justify-end">
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            wire:click="descargar({{ $comp['id'] }}, false)"
                                            icon="document-currency-dollar"
                                            title="XML adjunto"
                                            />
                                    @if ($comp['archivo_pdf'])
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            wire:click="descargar({{ $comp['id'] }}, true)"
                                            icon="paper-clip"
                                            title="PDF adjunto"
                                            />
                                    @endif
                                    <flux:badge :color="$satColor" size="sm">
                                        SAT: {{ ucfirst($comp['sat_status'] ?? 'pendiente') }}
                                    </flux:badge>
                                    <span class="font-mono text-xs font-semibold">
                                        {{ Number::currency($comp['monto'], in: 'MXN') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>

    @can('gastos.tarjeta.conciliar')
        <flux:modal name="conciliacion" wire:model="mostrandoConciliacion">
            <div class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">
                        {{ $accionConciliacion === 'conciliada' ? 'Conciliar periodo' : 'Rechazar periodo' }}
                    </flux:heading>
                    <flux:subheading>
                        {{ $accionConciliacion === 'conciliada'
                            ? 'Confirma que todos los cargos son correctos y corresponden a gastos de la empresa.'
                            : 'Indica el motivo del rechazo para que el empleado pueda corregir.' }}
                    </flux:subheading>
                </div>

                @if ($accionConciliacion === 'rechazada')
                    <flux:field>
                        <flux:label badge="Requerido">Motivo de rechazo</flux:label>
                        <flux:textarea
                            wire:model="motivoRechazo"
                            placeholder="Describe el motivo del rechazo..."
                            resize="none"
                            rows="3"
                        />
                        <flux:error name="motivoRechazo" />
                    </flux:field>
                @else
                    <div class="rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 px-4 py-3">
                        <p class="text-sm text-emerald-700 dark:text-emerald-300">
                            Total a conciliar:
                            <span class="font-semibold font-mono">
                                {{ Number::currency($comprobacion->monto_total, in: 'MXN') }}
                            </span>
                            — {{ count($gastos) }} gasto(s)
                        </p>
                    </div>
                @endif

                <div class="flex justify-between gap-3">
                    <flux:button variant="ghost" wire:click="$set('mostrandoConciliacion', false)">
                        Cancelar
                    </flux:button>
                    <flux:button
                        :variant="$accionConciliacion === 'conciliada' ? 'primary' : 'danger'"
                        :color="$accionConciliacion === 'conciliada' ? 'green' : null"
                        wire:click="conciliar"
                        wire:loading.attr="disabled"
                        wire:target="conciliar"
                    >
                        <span wire:loading.remove wire:target="conciliar">
                            {{ $accionConciliacion === 'conciliada' ? 'Confirmar conciliación' : 'Rechazar periodo' }}
                        </span>
                        <span wire:loading wire:target="conciliar">Procesando…</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endcan

    <flux:modal name="enviar-a-revisar" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">¿Enviar este periodo a revisión?</flux:heading>
                <flux:text class="mt-2">
                    Ya no podrás agregar ni eliminar gastos.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button
                    type="submit"
                    wire:click="enviarARevision"
                    variant="primary"
                    color="green"
                    >
                    Enviar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
