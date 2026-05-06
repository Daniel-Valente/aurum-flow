<flux:modal name="politica-form" class="w-full max-w-2xl" scroll="body">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar política' : 'Nueva política' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId
                    ? 'Modifica los datos de la política. Se generará una nueva versión.'
                    : 'Completa los datos para registrar una nueva política de gasto.' }}
            </flux:subheading>
        </div>
    </div>

    <div class="space-y-4 mt-2">

        {{-- ── Rol y Concepto ──────────────────────────────────────────── --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Requerido">Rol</flux:label>
                <flux:select variant="listbox" wire:model.live="roleId" required>
                    <flux:select.option value=""></flux:select.option>
                    @foreach ($roles as $role)
                        <flux:select.option value="{{ $role['id'] }}">{{ $role['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="roleId" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Requerido">Concepto</flux:label>
                <flux:select variant="listbox" wire:model="concepto_id" :key="$roleId" required>
                    <flux:select.option value=""></flux:select.option>
                    @foreach ($conceptos as $concepto)
                        <flux:select.option value="{{ $concepto['id'] }}">
                            <span class="text-xs font-mono text-zinc-500 dark:text-zinc-400">{{ $concepto['codigo'] }}</span>
                            {{ $concepto['nombre'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="concepto_id" />
            </flux:field>
        </div>

        {{-- ── Monto máximo y Tipo de límite ──────────────────────────── --}}
        <div class="grid auto-rows-min gap-5 md:grid-cols-2">

            <div class="flex flex-col">
                <flux:label class="h-5 flex items-center" badge="Requerido">
                    Monto máximo ($)
                </flux:label>

                <flux:input
                    class="mt-1"
                    wire:model.live="monto_max"
                    placeholder="Ej. 1500.00"
                    type="number"
                    min="0.01"
                    step="0.01"
                    required
                />

                <flux:description class="mt-1">
                    Tope absoluto autorizado por período.
                </flux:description>

                <flux:error name="monto_max" />
            </div>

            <div class="flex flex-col">
                <flux:label class="h-5 flex items-center" badge="Requerido">
                    Tipo de límite
                </flux:label>

                <flux:select
                    class="mt-1"
                    variant="listbox"
                    wire:model="tipo_limite"
                    required
                >
                    <flux:select.option value=""></flux:select.option>
                    <flux:select.option value="Diario">Diario</flux:select.option>
                    <flux:select.option value="Viaje">Por viaje</flux:select.option>
                    <flux:select.option value="Evento">Por evento</flux:select.option>
                </flux:select>

                <!-- espacio reservado para mantener altura consistente -->
                <div class="mt-1 text-sm text-transparent">
                    placeholder
                </div>

                <flux:error name="tipo_limite" />
            </div>

        </div>

        {{-- ── Tramos documentales ─────────────────────────────────────── --}}
        {{--
            Diagrama de tramos:
            $0 ──── monto_libre ──── monto_comprobante ──── monto_factura ──── monto_max
                    sin doc               ticket                  CFDI
            Cualquier tramo puede quedar vacío (null).
        --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
            x-data="{
                get max()  { return parseFloat($wire.monto_max) || 0 },
                get lib()  { return $wire.monto_libre       != null && $wire.monto_libre       !== '' ? parseFloat($wire.monto_libre)       : null },
                get comp() { return $wire.monto_comprobante != null && $wire.monto_comprobante !== '' ? parseFloat($wire.monto_comprobante) : null },
                get fac()  { return $wire.monto_factura     != null && $wire.monto_factura     !== '' ? parseFloat($wire.monto_factura)     : null },

                get sinTramos() { return this.lib === null && this.comp === null && this.fac === null },

                pct(v) { return this.max > 0 ? Math.min(100, (v / this.max) * 100) : 0 },

                fmt(n) {
                    if (n === null || n === undefined || isNaN(n)) return null
                    return n.toLocaleString('es-MX', { style: 'currency', currency: 'MXN', minimumFractionDigits: 2 })
                },

                // ── Anchos de segmentos (comp y fac son límites INFERIORES de su tramo) ──
                // Verde:  0 → comp (inicio de ticket), o hasta max si no hay ticket
                get segNone() {
                    if (this.sinTramos) return 100
                    if (this.comp !== null) return this.pct(this.comp)
                    if (this.fac  !== null) return this.pct(this.fac)
                    return 100
                },
                // Ámbar: comp → fac (inicio de CFDI), o hasta max si no hay CFDI
                get segTicket() {
                    if (this.sinTramos || this.comp === null) return 0
                    return this.fac !== null
                        ? this.pct(this.fac) - this.pct(this.comp)
                        : 100 - this.pct(this.comp)
                },
                // Rosa: fac → 100%
                get segCfdi() {
                    if (this.sinTramos || this.fac === null) return 0
                    return 100 - this.pct(this.fac)
                },

                // Posición izquierda de cada segmento
                get leftTicket() { return this.comp !== null ? this.pct(this.comp) : 0 },
                get leftCfdi()   { return this.fac  !== null ? this.pct(this.fac)  : 0 },

                // ── Rangos legibles (cada tramo va hasta 1 centavo antes del siguiente) ──
                get rangoNone() {
                    if (this.sinTramos) return '$0.00 – ' + (this.fmt(this.max) ?? '—')
                    const desde = this.lib !== null ? this.fmt(this.lib) : '$0.00'
                    const hasta = this.comp !== null
                        ? this.fmt(this.comp - 0.01)
                        : (this.fac !== null ? this.fmt(this.fac - 0.01) : this.fmt(this.max))
                    return desde + ' – ' + hasta
                },
                get rangoTicket() {
                    if (this.comp === null) return 'No aplica'
                    const hasta = this.fac !== null ? this.fmt(this.fac - 0.01) : this.fmt(this.max)
                    return this.fmt(this.comp) + ' – ' + hasta
                },
                get rangoCfdi() {
                    if (this.fac === null) return 'No aplica'
                    return this.fmt(this.fac) + ' – ' + this.fmt(this.max)
                },
            }"
        >
            <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                    Tramos documentales por monto
                </p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Cada valor es el <span class="font-medium">inicio</span> del tramo — aplica desde ese monto
                    hasta el siguiente. El último tramo llega siempre hasta el monto máximo.
                    Si dejas todo vacío, no se requiere documento.
                </p>
            </div>

            <div class="px-4 pt-4 pb-5 space-y-4">
                <div>
                    <div class="relative h-2 rounded-full overflow-hidden bg-zinc-200 dark:bg-zinc-700">
                        <div
                            class="absolute top-0 left-0 h-full bg-emerald-400 dark:bg-emerald-500"
                            :style="`width: ${segNone}%`"
                        ></div>
                        <div
                            class="absolute top-0 h-full bg-amber-400 dark:bg-amber-500"
                            :style="`left: ${leftTicket}%; width: ${segTicket}%`"
                        ></div>
                        <div
                            class="absolute top-0 h-full bg-rose-400 dark:bg-rose-500"
                            :style="`left: ${leftCfdi}%; width: ${segCfdi}%`"
                        ></div>
                    </div>

                    <div class="flex justify-between mt-1.5 text-[10px] font-mono">
                        <span class="text-emerald-600 dark:text-emerald-400">Sin doc</span>
                        <span class="text-amber-600 dark:text-amber-500">Ticket</span>
                        <span class="text-rose-500 dark:text-rose-400">CFDI</span>
                        <span class="text-zinc-400" x-text="fmt(max) ?? 'Máx'"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-3 space-y-2">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Sin documento</span>
                        </div>
                        <flux:input
                            wire:model.live="monto_libre"
                            placeholder="Ej. 200.00"
                            type="number"
                            min="0"
                            step="0.01"
                        />
                        <p class="text-[11px] text-zinc-400">Hasta aquí no se requiere comprobante</p>
                        <p class="text-[11px] font-mono text-emerald-600 dark:text-emerald-400" x-text="rangoNone"></p>
                        <flux:error name="monto_libre" />
                    </div>

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-3 space-y-2">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Ticket / recibo</span>
                        </div>
                        <flux:input
                            wire:model.live="monto_comprobante"
                            placeholder="Ej. 500.00"
                            type="number"
                            min="0"
                            step="0.01"
                        />
                        <p class="text-[11px] text-zinc-400">Desde aquí, ticket o recibo es suficiente</p>
                        <p class="text-[11px] font-mono text-amber-600 dark:text-amber-500" x-text="rangoTicket"></p>
                        <flux:error name="monto_comprobante" />
                    </div>

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-3 space-y-2">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block w-2 h-2 rounded-full bg-rose-400 shrink-0"></span>
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300">CFDI obligatorio</span>
                        </div>
                        <flux:input
                            wire:model.live="monto_factura"
                            placeholder="Ej. 1000.00"
                            type="number"
                            min="0.01"
                            step="0.01"
                        />
                        <p class="text-[11px] text-zinc-400">Desde aquí, factura electrónica (XML + UUID)</p>
                        <p class="text-[11px] font-mono text-rose-500 dark:text-rose-400" x-text="rangoCfdi"></p>
                        <flux:error name="monto_factura" />
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Vigencia ─────────────────────────────────────────────────── --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Opcional">Vigencia desde</flux:label>
                <flux:date-picker wire:model="vigencia_desde" />
                <flux:error name="vigencia_desde" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Opcional">Vigencia hasta</flux:label>
                <flux:date-picker wire:model="vigencia_hasta" />
                <flux:error name="vigencia_hasta" />
            </flux:field>
        </div>

        {{-- ── Motivo de cambio (solo edición) ────────────────────────── --}}
        @if ($this->isEditing())
            <flux:field class="w-full">
                <flux:label badge="Requerido">Motivo de cambio</flux:label>
                <flux:textarea resize="none" wire:model="motivo" placeholder="Describe brevemente el motivo de esta modificación…" required />
                <flux:error name="motivo" />
            </flux:field>
        @endif

        {{-- ── Impacto estimado ────────────────────────────────────────── --}}
        <div class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-900">
            <span class="text-xs font-mono text-zinc-500 dark:text-zinc-400">
                Impacto: 0 solicitudes activas · 0 gastos pendientes · 0 gastos rechazados por política.
            </span>
        </div>

        {{-- ── Flags de comportamiento ─────────────────────────────────── --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-100 dark:divide-zinc-700">
            <div class="px-4 py-3">
                <flux:field variant="inline">
                    <flux:checkbox wire:model.live="valida_sat" />
                    <div>
                        <flux:label class="text-sm font-medium">Validar UUID ante el SAT</flux:label>
                        <flux:description class="text-xs">Al subir un CFDI, se consulta automáticamente la API del SAT para verificar el UUID.</flux:description>
                    </div>
                    <flux:error name="valida_sat" />
                </flux:field>
            </div>

            <div class="px-4 py-3">
                <flux:field variant="inline">
                    <flux:checkbox wire:model="acumulable_dia" />
                    <div>
                        <flux:label class="text-sm font-medium">Acumulable por día</flux:label>
                        <flux:description class="text-xs">Permite registrar este concepto varias veces en el mismo día para este rol.</flux:description>
                    </div>
                    <flux:error name="acumulable_dia" />
                </flux:field>
            </div>

            <div class="px-4 py-3">
                <flux:field variant="inline">
                    <flux:checkbox wire:model="permite_excepcion" />
                    <div>
                        <flux:label class="text-sm font-medium">Permitir excepción fuera del límite</flux:label>
                        <flux:description class="text-xs">El empleado puede superar el monto máximo con una justificación que requiere aprobación.</flux:description>
                    </div>
                    <flux:error name="permite_excepcion" />
                </flux:field>
            </div>
        </div>

        {{-- ── Acciones ─────────────────────────────────────────────────── --}}
        <div class="flex justify-end gap-3 pt-1">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button
                variant="primary"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">
                    {{ $editingId ? 'Guardar cambios' : 'Crear política' }}
                </span>
                <span wire:loading wire:target="save">Guardando…</span>
            </flux:button>
        </div>
    </div>
</flux:modal>
