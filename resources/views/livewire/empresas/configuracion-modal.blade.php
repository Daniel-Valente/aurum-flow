<flux:modal name="configuracion-form" class="w-full max-w-4xl" scroll="body">
    <div class="space-y-6">

        <div>
            <flux:heading size="lg">
                Configuración: {{ $empresa?->nombre ?? 'Empresa' }}
            </flux:heading>

            <flux:subheading>
                Define las reglas de validación y comportamiento para {{ $empresa?->nombre_comercial ?? $empresa?->nombre ?? 'esta empresa' }}.
                @if ($esConfiguracionPropia)
                    <span class="text-green-600 dark:text-green-400 font-semibold">
                        (Configuración personalizada)
                    </span>
                @else
                    <span class="text-amber-600 dark:text-amber-400 font-semibold">
                        (Usando configuración global)
                    </span>
                @endif
            </flux:subheading>
        </div>

        @if ($errors->has('general'))
            <flux:card variant="danger" class="border border-red-200 dark:border-red-900">
                <div class="flex gap-3">
                    <flux:icon name="exclamation-triangle" class="mt-0.5 size-5 shrink-0 text-red-600 dark:text-red-400" />
                    <div>
                        <flux:heading size="sm" class="text-red-900 dark:text-red-100">Error</flux:heading>
                        <flux:text size="sm" class="text-red-800 dark:text-red-200">
                            {{ $errors->first('general') }}
                        </flux:text>
                    </div>
                </div>
            </flux:card>
        @endif

        <div class="space-y-5">
            <!-- Sección CFDI -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                        Validación de CFDI
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Rango de fechas permitidas entre CFDI y gasto registrado.
                    </p>
                </div>

                <div class="p-4 space-y-5">

                    <flux:field>
                        <flux:label>RFC de la empresa (para validación)</flux:label>
                        <flux:input
                            wire:model="rfc_empresa"
                            placeholder="Ej. XAXX010101000"
                            maxlength="13"
                        />
                        @if ($configActual && !$esConfiguracionPropia && $valoresGlobales['rfc_empresa'] ?? null)
                            <flux:description size="sm">
                                Global: {{ $valoresGlobales['rfc_empresa'] }}
                            </flux:description>
                        @endif
                        <flux:error name="rfc_empresa" />
                    </flux:field>

                    <flux:checkbox
                        wire:model="validar_rfc_receptor"
                        label="Validar RFC receptor contra RFC de empresa"
                        description="Si está activado, el RFC del receptor en el CFDI debe coincidir con el RFC de la empresa."
                    />

                    <div class="grid md:grid-cols-3 gap-5">
                        <flux:field>
                            <flux:label>Días hábiles para comprobación</flux:label>
                            <flux:input
                                type="number"
                                wire:model="dias_habiles_comprobacion"
                                min="1"
                                max="30"
                            />
                            @if (!$esConfiguracionPropia && isset($valoresGlobales['dias_habiles_comprobacion']))
                                <flux:description size="sm">
                                    Global: {{ $valoresGlobales['dias_habiles_comprobacion'] }}
                                </flux:description>
                            @endif
                            <flux:error name="dias_habiles_comprobacion" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Días antes permitidos</flux:label>
                            <flux:input
                                type="number"
                                wire:model="cfdi_dias_antes_permitidos"
                                min="0"
                                max="60"
                            />
                            @if (!$esConfiguracionPropia && isset($valoresGlobales['cfdi_dias_antes_permitidos']))
                                <flux:description size="sm">
                                    Global: {{ $valoresGlobales['cfdi_dias_antes_permitidos'] }}
                                </flux:description>
                            @endif
                            <flux:error name="cfdi_dias_antes_permitidos" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Días después permitidos</flux:label>
                            <flux:input
                                type="number"
                                wire:model="cfdi_dias_despues_permitidos"
                                min="0"
                                max="60"
                            />
                            @if (!$esConfiguracionPropia && isset($valoresGlobales['cfdi_dias_despues_permitidos']))
                                <flux:description size="sm">
                                    Global: {{ $valoresGlobales['cfdi_dias_despues_permitidos'] }}
                                </flux:description>
                            @endif
                            <flux:error name="cfdi_dias_despues_permitidos" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <!-- Sección Auto-aprobaciones -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                        Auto-aprobaciones
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Determina qué tipos de gastos se aprueban automáticamente.
                    </p>
                </div>

                <div class="p-4 space-y-4">
                    <flux:checkbox
                        wire:model="propina_auto_aprueba"
                        label="Auto-aprobar propinas"
                        description="Aprobar automáticamente gastos clasificados como propina que cumplan con la política."
                    />

                    <flux:checkbox
                        wire:model="gasto_compartido_auto_aprueba"
                        label="Auto-aprobar gastos compartidos"
                        description="Aprobar automáticamente gastos que se comparten entre múltiples personas."
                    />

                    <flux:checkbox
                        wire:model="gasto_cliente_auto_aprueba"
                        label="Auto-aprobar gastos de cliente"
                        description="Aprobar automáticamente gastos asociados a clientes."
                    />

                    @if (!$esConfiguracionPropia)
                        <flux:card variant="info" class="mt-4">
                            <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                                💡 Estos valores heredan de la configuración global. Personaliza la configuración de esta empresa para cambiarlos.
                            </flux:text>
                        </flux:card>
                    @endif
                </div>
            </div>

            <!-- Sección Validadores y Localización -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                        Validadores y Localización
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Define quién valida los tickets y la configuración regional.
                    </p>
                </div>

                <div class="p-4 space-y-5">

                    <flux:field>
                        <flux:label>Rol validador de tickets</flux:label>
                        <flux:select variant="listbox" wire:model="validador_tickets">
                            @foreach ($roles as $role)
                                <flux:select.option value="{{ $role['name'] }}">{{ $role['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @if (!$esConfiguracionPropia && isset($valoresGlobales['validador_tickets']))
                            <flux:description size="sm">
                                Global: {{ $valoresGlobales['validador_tickets'] }}
                            </flux:description>
                        @endif
                        <flux:error name="validador_tickets" />
                    </flux:field>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Opcional">Moneda</flux:label>
                            <flux:input
                                wire:model="moneda"
                                placeholder="Ej. MXN"
                                maxlength="3"
                                class="uppercase"
                            />
                            <flux:description size="sm">
                                Deja vacío para usar el de la empresa ({{ $empresa?->moneda ?? 'MXN' }})
                            </flux:description>
                            <flux:error name="moneda" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Opcional">País</flux:label>
                            <flux:input
                                wire:model="pais"
                                placeholder="Ej. MX"
                                maxlength="2"
                                class="uppercase"
                            />
                            <flux:description size="sm">
                                Deja vacío para usar el de la empresa ({{ $empresa?->pais ?? 'MX' }})
                            </flux:description>
                            <flux:error name="pais" />
                        </flux:field>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex justify-between gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
            <div>
                @if ($esConfiguracionPropia)
                    <flux:button
                        variant="danger"
                        wire:click="resetear"
                        wire:confirm="¿Deseas resetear la configuración a los valores globales?"
                    >
                        Resetear a global
                    </flux:button>
                @endif
            </div>

            <div class="flex gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">
                        Cancelar
                    </flux:button>
                </flux:modal.close>
                <flux:button
                    variant="primary"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">
                        Guardar configuración
                    </span>
                    <span wire:loading wire:target="save">
                        Guardando…
                    </span>
                </flux:button>
            </div>
        </div>
    </div>
</flux:modal>
