<flux:modal name="empleado-form" class="w-full max-w-2xl" scroll="body">
    <div class="space-y-6">

        {{-- ── Header ───────────────────────────────────────────────────── --}}
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar empleado' : 'Nuevo empleado' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId
                    ? 'Modifica la información del empleado.'
                    : 'Completa los datos para registrar un nuevo empleado.' }}
            </flux:subheading>
        </div>

        {{-- ── Formulario ──────────────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- ── Datos personales ───────────────────────────────────── --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Datos personales</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Información básica de identificación y contacto.
                    </p>
                </div>

                <div class="p-4 space-y-5">

                    <flux:field>
                        <flux:label badge="Requerido">Nombre completo</flux:label>
                        <flux:input wire:model="nombre_completo" placeholder="Ej. Juan Pérez" required />
                        <flux:error name="nombre_completo" />
                    </flux:field>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">Correo electrónico</flux:label>
                            <flux:input type="email" wire:model="email" placeholder="Ej. jperez@empresa.com" required />
                            <flux:error name="email" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Opcional">Teléfono</flux:label>
                            <flux:input wire:model="telefono" placeholder="(55) 55-5555-5555" />
                            <flux:error name="telefono" />
                        </flux:field>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">RFC</flux:label>
                            <flux:input wire:model="rfc" placeholder="Ej. XAXX010101000" required />
                            <flux:error name="rfc" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Requerido">CURP</flux:label>
                            <flux:input wire:model="curp" placeholder="Ej. XAXX010101HXXXXX00" required />
                            <flux:error name="curp" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label badge="Requerido">NSS</flux:label>
                        <flux:input wire:model="nss" placeholder="Ej. 12345678901" required />
                        <flux:error name="nss" />
                    </flux:field>

                </div>
            </div>

            {{-- ── Datos laborales ───────────────────────────────────── --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Datos laborales</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Información organizacional y asignaciones internas.
                    </p>
                </div>

                <div class="p-4 space-y-5">

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">Puesto</flux:label>
                            <flux:input wire:model="puesto" placeholder="Ej. Gerente de Ventas" required />
                            <flux:error name="puesto" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Requerido">Área / Departamento</flux:label>
                            <flux:select variant="listbox" wire:model="area_id" :disabled="$esGerente">
                                @foreach ($areas as $area)
                                    <flux:select.option value="{{ $area['id'] }}">{{ $area['nombre'] }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="area_id" />
                        </flux:field>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">Rol</flux:label>
                            <flux:select variant="listbox" wire:model="rol_id" required>
                                @foreach ($roles as $rol)
                                    <flux:select.option value="{{ $rol['id'] }}">{{ $rol['name'] }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="rol_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Requerido">Centro de costo</flux:label>
                            <flux:select variant="listbox" wire:model="centro_costo_id" :disabled="$esGerente" required>
                                @foreach ($centrosCostos as $centro)
                                    <flux:select.option value="{{ $centro['id'] }}">{{ $centro['nombre'] }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="centro_costo_id" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label badge="Opcional">Fecha de ingreso</flux:label>
                        <flux:date-picker wire:model="fecha_ingreso" />
                    </flux:field>

                </div>
            </div>

            {{-- ── Información financiera ───────────────────────────── --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Información financiera</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Datos relacionados con nómina y medios de pago.
                    </p>
                </div>

                <div class="p-4 space-y-5">

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">Número de nómina</flux:label>
                            <flux:input wire:model="numero_nomina" placeholder="Ej. NOM-0001" required />
                            <flux:error name="numero_nomina" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Requerido">Banco</flux:label>
                            <flux:input wire:model="banco_nomina" placeholder="Ej. BBVA" required />
                            <flux:error name="banco_nomina" />
                        </flux:field>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">Cuenta</flux:label>
                            <flux:input wire:model="cuenta_nomina" placeholder="Ej. 1234567890" required />
                            <flux:error name="cuenta_nomina" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Requerido">CLABE</flux:label>
                            <flux:input wire:model="clabe_nomina" placeholder="Ej. 012180001234567890" required />
                            <flux:error name="clabe_nomina" />
                        </flux:field>
                    </div>

                    {{-- ── Tarjeta corporativa ───────────────────────── --}}
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                            <p class="text-sm font-medium">Tarjeta corporativa</p>
                            <p class="text-xs text-zinc-500 mt-0.5">
                                Configuración de tarjeta empresarial asignada al empleado.
                            </p>
                        </div>

                        <div class="p-4 space-y-4">

                            <flux:field variant="inline">
                                <flux:checkbox wire:model.live="tarjeta_credito_corporativa_asignada" />
                                <div>
                                    <flux:label class="text-sm font-medium">
                                        Tarjeta asignada
                                    </flux:label>
                                    <flux:description class="text-xs">
                                        El empleado puede utilizar una tarjeta corporativa.
                                    </flux:description>
                                </div>
                            </flux:field>

                            <flux:field>
                                <flux:label badge="Opcional">Límite de crédito ($)</flux:label>
                                <flux:input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    wire:model="limite_credito_tarjeta"
                                    placeholder="Ej. 15000.00"
                                    :disabled="!$tarjeta_credito_corporativa_asignada"
                                />
                                <flux:description class="text-xs">
                                    Monto máximo autorizado para uso de la tarjeta.
                                </flux:description>
                                <flux:error name="limite_credito_tarjeta" />
                            </flux:field>

                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- ── Acciones ─────────────────────────────────────────────── --}}
        <div class="flex justify-end gap-3 pt-2">
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
                    {{ $editingId ? 'Guardar cambios' : 'Crear empleado' }}
                </span>
                <span wire:loading wire:target="save">
                    Guardando…
                </span>
            </flux:button>
        </div>

    </div>
</flux:modal>
