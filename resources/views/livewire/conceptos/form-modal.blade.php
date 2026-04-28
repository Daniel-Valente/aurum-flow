<flux:modal name="concepto-form" class="w-full max-w-lg" scroll="body">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar concepto' : 'Nuevo concepto' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId ? 'Modifica los datos del concepto.' : 'Completa los datos para registrar un nuevo concepto.' }}
            </flux:subheading>
        </div>
    </div>

    <div class="space-y-4">
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Código
                </flux:label>
                <flux:input
                    wire:model="codigo"
                    placeholder="Ej. CON-ALIM"
                    required
                    :disabled="$editingId !== null"
                />
                <flux:error name="codigo" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Nombre
                </flux:label>
                <flux:input
                    wire:model="nombre"
                    placeholder="Ej. Alimentos"
                    required
                />
                <flux:error name="nombre" />
            </flux:field>
        </div>

        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Categoría
                </flux:label>
                <flux:input
                    wire:model="categoria"
                    placeholder="Ej. Alimentación"
                    required
                />
                <flux:error name="categoria" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Tipo
                </flux:label>
                <flux:select variant="listbox" wire:model="tipo_aplicacion">
                    <flux:select.option value=""></flux:select.option>
                    <flux:select.option value="Diario">Diario</flux:select.option>
                    <flux:select.option value="Evento">Evento</flux:select.option>
                    <flux:select.option value="Viaje">Viaje</flux:select.option>
                </flux:select>
                <flux:error name="tipo_aplicacion" />
            </flux:field>
        </div>

        <div class="mt-3 flex">
            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Descripción
                </flux:label>
                <flux:textarea resize="none" wire:model="descripcion" />
            </flux:field>
        </div>

        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Orden
                </flux:label>
                <flux:input
                    wire:model="orden"
                    placeholder="Ej. 0"
                    type="number"
                    step="1"
                    min="0"
                />
                <flux:error name="orden" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Tope referencia
                </flux:label>
                <flux:input
                    wire:model="tope_referencia"
                    placeholder="Ej. 0"
                    type="number"
                    min="0"
                />
                <flux:error name="tope_referencia" />
            </flux:field>
        </div>

        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Vigencia desde
                </flux:label>
                <flux:date-picker wire:model="vigencia_desde" />
                <flux:error name="vigencia_desde" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Vigencia hasta
                </flux:label>
                <flux:date-picker wire:model="vigencia_hasta" />
                <flux:error name="vigencia_hasta" />
            </flux:field>
        </div>

        <div class="mt-3 flex">
            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Rol Permitido
                </flux:label>
                <div class="flex gap-4 *:gap-x-2">
                    @foreach ($roles as $role)
                        <flux:checkbox value="{{ $role['name'] }}" label="{{ $role['name'] }}" wire:model.live="rolesSeleccionados" />
                    @endforeach

                </div>
            </flux:field>
        </div>

        <div class="mt-3 flex">
            <flux:fieldset>
                <flux:legend>Regla</flux:legend>
                <flux:description>Configura las condiciones del concepto.</flux:description>
                <div class="flex gap-4 *:gap-x-2">
                    <flux:checkbox wire:model.live="requiere_factura" label="Require factura (XML / PDF)" />
                    <flux:checkbox wire:model="requiere_comprobante" label="Require comprobante" />
                    <flux:checkbox wire:model.live="requiere_uuid" label="Require UUID de factura" />
                </div>
                <div class="flex gap-4 *:gap-x-2 mt-2">
                    <flux:checkbox wire:model="aplica_iva" label="Aplica IVA" />
                    <flux:checkbox wire:model.live="permite_sin_factura" label="Permite sin factura" />
                    <flux:checkbox wire:model="acumulable_dia" label="Acumulable por dia" />
                </div>

                <flux:error name="permite_sin_factura" />
                <flux:error name="requiere_uuid" />
            </flux:fieldset>
        </div>

        <div class="flex justify-end gap-3 mt-3">
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
                    {{ $editingId ? 'Guardar cambios' : 'Crear concepto' }}
                </span>
                <span wire:loading wire:target="save">Guardando...</span>
            </flux:button>
        </div>
    </div>
</flux:modal>
