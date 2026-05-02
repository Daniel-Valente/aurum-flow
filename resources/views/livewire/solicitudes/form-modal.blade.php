<flux:modal name="solicitud-form" class="w-full max-w-2xl" scroll="body">
    <div class="space-y-6">

        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar solicitud' : 'Nuevo solicitud' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId
                    ? 'Modifica la información de la solicitud.'
                    : 'Completa los datos para registrar una nueva solicitud.' }}
            </flux:subheading>
        </div>

        <div class="space-y-5">
            <flux:field>
                <flux:label badge="Requerido">Proyecto</flux:label>
                <flux:select variant="listbox" wire:model="proyecto_id">
                    <flux:select.option value=""></flux:select.option>
                    @foreach ($proyectos as $proyecto)
                        <flux:select.option value="{{ $proyecto['id'] }}">{{ $proyecto['nombre'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="´proyecto_id" />
            </flux:field>

            <flux:field>
                <flux:label badge="Requerido">Motivo</flux:label>
                <flux:textarea resize="none" wire:model="motivo" />

                <flux:error name="motivo" />
            </flux:field>

            <div class="grid auto-rows-min gap-5 md:grid-cols-2">
                <div class="flex flex-col">
                    <flux:field>
                        <flux:label badge="Requerido">Fecha inicio</flux:label>
                        <flux:date-picker wire:model="fecha_inicio" />

                        <flux:error name="fecha_inicio" />
                    </flux:field>
                </div>

                <div class="flex flex-col">
                    <flux:field>
                        <flux:label badge="Requerido">Fecha fin</flux:label>
                        <flux:date-picker wire:model="fecha_fin" />

                        <flux:error name="fecha_fin" />
                    </flux:field>
                </div>
            </div>
        </div>

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
                    {{ $editingId ? 'Guardar cambios' : 'Crear solicitud' }}
                </span>
                <span wire:loading wire:target="save">
                    Guardando…
                </span>
            </flux:button>
        </div>

    </div>
</flux:modal>
