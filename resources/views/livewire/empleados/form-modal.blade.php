<flux:modal name="empleado-form" class="w-full max-w-lg" scroll="body">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar empleado' : 'Nuevo empleado' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId ? 'Modifica los datos del empleado.' : 'Completa los datos para registar un nuevo empleado.' }}
            </flux:subheading>
        </div>

        <div class="space-y-4">
            <flux:fieldset>
                <flux:legend>Datos personales</flux:legend>
                <flux:field class="w-full">
                    <flux:label badge="Requerido">
                        Nombre completo
                    </flux:label>
                    <flux:input
                        wire:model="nombre_completo"
                        placeholder="Ej. Juan Perez"
                        required
                        />
                    <flux:error name="nombre_completo" />
                </flux:field>

                <div class="flex flex-row gap-4 mt-5">
                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            Correo electrónico
                        </flux:label>
                        <flux:input
                            type="email"
                            required
                            wire:model="email"
                            placeholder="Ej. jperez@demonio.com"
                            />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field class="w-full">
                        <flux:label badge="Opcional">
                            Teléfono
                        </flux:label>
                        <flux:input
                            wire:model="telefono"
                            placeholder="(55) 55-5555-5555"
                            mask="(99) 99-9999-9999"
                            type="phone"
                            />
                        <flux:error name="telefono" />
                    </flux:field>
                </div>

                <div class="flex flex-row gap-4 mt-5">
                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            RFC
                        </flux:label>
                        <flux:input
                            wire:model="rfc"
                            placeholder="Ej. XAXX010101000"
                            required
                            />
                        <flux:error name="rfc" />
                    </flux:field>

                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            CURP
                        </flux:label>
                        <flux:input
                            wire:model="curp"
                            placeholder="Ej. XAXX010101HXXXXX00"
                            required
                            />
                        <flux:error name="curp" />
                    </flux:field>
                </div>

                <flux:field class="mt-5 w-full">
                    <flux:label badge="Requerido">
                        NSS (Número de Seguro Social)
                    </flux:label>
                    <flux:input
                        wire:model="nss"
                        placeholder="Ej. 12345678901"
                        required
                        />
                    <flux:error name="nss" />
                </flux:field>
            </flux:fieldset>

            <flux:fieldset class="mt-5">
                <flux:legend>Datos laborales</flux:legend>

                <div class="flex flex-row gap-4">
                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            Puesto
                        </flux:label>
                        <flux:input
                            wire:model="puesto"
                            placeholder="Ej. Gerente de Ventas"
                            required
                        />
                        <flux:error name="puesto" />
                    </flux:field>

                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            Área / Departamento
                        </flux:label>
                        <flux:select variant="listbox" wire:model="area_id">
                            @foreach ($areas as $area)
                                <flux:select.option value="{{ $area['id'] }}">{{ $area['nombre'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="area_id" />
                    </flux:field>
                </div>

                <div class="flex flex-row gap-4 mt-5">
                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            Rol
                        </flux:label>
                        <flux:select variant="listbox" wire:model="rol_id" required>
                            @foreach ($roles as $rol)
                                <flux:select.option value="{{ $rol['id'] }}">{{ $rol['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="rol_id" />
                    </flux:field>

                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            Centro de costo
                        </flux:label>
                        <flux:select variant="listbox" wire:model="centro_costo_id" required>
                            @foreach ($centrosCostos as $centroCosto)
                                <flux:select.option value="{{ $centroCosto['id'] }}">{{ $centroCosto['nombre'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="centro_costo_id" />
                    </flux:field>
                </div>

                <flux:field class="mt-5 w-full">
                    <flux:label badge="Opcional">
                        Fecha de ingreso
                    </flux:label>
                    <flux:date-picker wire:model="fecha_ingreso" />
                </flux:field>

            </flux:fieldset>

            <flux:fieldset class="mt-5">
                <flux:legend>Información financiera</flux:legend>

                <div class="flex flex-row gap-4">
                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            Número de nómina
                        </flux:label>
                        <flux:input
                            wire:model="numero_nomina"
                            placeholder="Ej. NOM-0001"
                            required
                        />
                        <flux:error name="numero_nomina" />
                    </flux:field>

                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            Banco de nómina
                        </flux:label>
                        <flux:input
                            wire:model="banco_nomina"
                            placeholder="Ej. BBVA"
                            required
                        />
                        <flux:error name="banco_nomina" />
                    </flux:field>
                </div>

                <div class="flex flex-row gap-4 mt-5">
                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            Cuenta de nómina
                        </flux:label>
                        <flux:input
                            wire:model="cuenta_nomina"
                            placeholder="Ej. 1234567890"
                            required
                        />
                        <flux:error name="cuenta_nomina" />
                    </flux:field>

                    <flux:field class="w-full">
                        <flux:label badge="Requerido">
                            CLABE
                        </flux:label>
                        <flux:input
                            wire:model="clabe_nomina"
                            placeholder="Ej. 012180001234567890"
                            required
                        />
                        <flux:error name="clabe_nomina" />
                    </flux:field>
                </div>
            </flux:fieldset>
        </div>

        <div class="flex justify-end gap-3">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button
                variant="primary"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save">
                <span wire:loading.remove wire:target="save">
                    {{ $editingId ? 'Guardar cambios' : 'Crear empleado' }}
                </span>
                <span wire:loading wire:target="save">Guardando...</span>
            </flux:button>
        </div>
    </div>
</flux:modal>
