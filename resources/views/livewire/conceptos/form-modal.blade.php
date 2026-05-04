<flux:modal name="concepto-form" class="w-full max-w-2xl" scroll="body">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar concepto' : 'Nuevo concepto' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId
                    ? 'Modifica los datos del concepto.'
                    : 'Completa los datos para registrar un nuevo concepto.' }}
            </flux:subheading>
        </div>
    </div>

    <div class="space-y-4 mt-2">
        <div class="grid auto-rows-min gap-5 md:grid-cols-2">
            <div class="flex flex-col">
                <flux:field>
                    <flux:label badge="Requerido">Código</flux:label>
                    <flux:input
                        wire:model="codigo"
                        placeholder="Ej. CON-ALIM"
                        class="uppercase"
                        required
                        :disabled="true"
                    />
                    <flux:description class="text-[11px]">Se genera en automático</flux:description>
                    <flux:error name="codigo" />
                </flux:field>
            </div>
            <div class="flex flex-col">
                <flux:field>
                    <flux:label badge="Requerido">Nombre</flux:label>
                    <flux:input
                        wire:model="nombre"
                        placeholder="Ej. Alimentos"
                        required
                    />
                    <flux:error name="nombre" />
                </flux:field>
            </div>
        </div>

        <div class="grid auto-rows-min gap-5 md:grid-cols-2">
            <div class="flex flex-col">
                <flux:field>
                    <flux:label badge="Opcional">Categoría</flux:label>
                    <flux:input
                        wire:model="categoria"
                        placeholder="Ej. Alimentación"
                    />
                    <flux:error name="categoria" />
                </flux:field>
            </div>
            <div class="flex flex-col">
                <flux:field>
                    <flux:label badge="Requerido">Tipo de aplicación</flux:label>
                    <flux:select variant="listbox" wire:model="tipo_aplicacion">
                        <flux:select.option value=""></flux:select.option>
                        <flux:select.option value="Diario">Diario</flux:select.option>
                        <flux:select.option value="Evento">Evento</flux:select.option>
                        <flux:select.option value="Viaje">Viaje</flux:select.option>
                    </flux:select>
                    <flux:error name="tipo_aplicacion" />
                </flux:field>
            </div>
        </div>

        <div class="w-full">
            <flux:field>
                <flux:label badge="Opcional">Descripción</flux:label>
                <flux:textarea resize="none" wire:model="descripcion" placeholder="Describe brevemente este concepto…" />
                <flux:error name="descripcion" />
            </flux:field>
        </div>

        <div class="grid auto-rows-min gap-5 md:grid-cols-2">
            <div class="flex flex-col">
                <flux:field>
                    <flux:label badge="Opcional">Orden</flux:label>
                    <flux:input
                        wire:model="orden"
                        placeholder="Ej. 1"
                        type="number"
                        step="1"
                        min="0"
                    />
                    <flux:description class="text-[11px]">Posición en listas y reportes.</flux:description>
                    <flux:error name="orden" />
                </flux:field>
            </div>

            <div class="flex flex-col">
                <flux:field>
                    <flux:label badge="Opcional">Tope de referencia ($)</flux:label>
                    <flux:input
                        wire:model="tope_referencia"
                        placeholder="Ej. 350.00"
                        type="number"
                        min="0"
                        step="0.01"
                    />
                    <flux:description class="text-[11px]">Precio promedio de mercado (informativo).</flux:description>
                    <flux:error name="tope_referencia" />
                </flux:field>
            </div>
        </div>

        <div class="grid auto-rows-min gap-5 md:grid-cols-2">
            <div class="flex flex-col">
                <flux:field>
                    <flux:label badge="Opcional">Vigencia desde</flux:label>
                    <flux:date-picker wire:model="vigencia_desde" />
                    <flux:error name="vigencia_desde" />
                </flux:field>
            </div>

            <div class="flex flex-col">
                <flux:field>
                    <flux:label badge="Opcional">Vigencia hasta</flux:label>
                    <flux:date-picker wire:model="vigencia_hasta" />
                    <flux:error name="vigencia_hasta" />
                </flux:field>
            </div>
        </div>

        <div class="w-full">
            <flux:field>
                <flux:label badge="Opcional">Roles con acceso</flux:label>
                <flux:description class="text-xs mb-2">
                    Sin selección = No disponible hacía todos los roles.
                </flux:description>
                <div class="flex flex-wrap gap-4">
                    @foreach ($roles as $role)
                        <flux:checkbox
                            value="{{ $role['name'] }}"
                            label="{{ $role['name'] }}"
                            wire:model.live="rolesSeleccionados"
                        />
                    @endforeach
                </div>
                <flux:error name="rolesSeleccionados" />
            </flux:field>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Naturaleza fiscal</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Propiedad intrínseca del concepto, independiente del rol.
                    Las reglas de documentos y montos se configuran en la política.
                </p>
            </div>
            <div class="px-4 py-3">
                <flux:field variant="inline">
                    <flux:checkbox wire:model="aplica_iva" />
                    <div>
                        <flux:label class="text-sm font-medium">Aplica IVA</flux:label>
                        <flux:description class="text-xs">
                            El gasto genera IVA acreditable (ej. hospedaje sí, viáticos de alimentación pueden ser exentos).
                        </flux:description>
                    </div>
                    <flux:error name="aplica_iva" />
                </flux:field>
            </div>
        </div>

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
                    {{ $editingId ? 'Guardar cambios' : 'Crear concepto' }}
                </span>
                <span wire:loading wire:target="save">Guardando…</span>
            </flux:button>
        </div>
    </div>
</flux:modal>
