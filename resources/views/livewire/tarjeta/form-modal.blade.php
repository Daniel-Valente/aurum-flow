<flux:modal name="tarjeta-form" class="w-full max-w-2xl" scroll="body">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar comprobación por tarjeta' : 'Nueva comprobación por tarjeta' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId ? 'Modifica la información de la comprobación por tarjeta' : 'Completa los datos para registrar una nueva comprobación por tarjeta corporativa' }}
            </flux:subheading>
        </div>

        <div class="space-y-5">
            <flux:field>
                <flux:subheading>Período</flux:subheading>
            </flux:field>

            <div class="grid auto-rows-min gap-5 md:grid-cols-2">
                <div class="flex flex-col">
                    <flux:field>
                        <flux:label badge="Requerido">Fecha inicio</flux:label>
                        <flux:date-picker selectable-header wire:model="fecha_inicio" fixed-weeks />

                        <flux:error name="fecha_inicio" />
                    </flux:field>
                </div>

                <div class="flex flex-col">
                    <flux:field>
                        <flux:label badge="Requerido">Fecha fin</flux:label>
                        <flux:date-picker selectable-header wire:model="fecha_fin" fixed-weeks />

                        <flux:error name="fecha_fin" />
                    </flux:field>
                </div>
            </div>

            <flux:field>
                <flux:label badge="Opcional">Motivo</flux:label>
                <flux:textarea resize="none" wire:model="descripcion" />

                <flux:error name="descripcion" />
            </flux:field>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-100 dark:divide-zinc-700">
                <div class="px-4 py-3">
                    <flux:field>
                        <flux:label badge="Opcional">Proyecto</flux:label>
                        <flux:select variant="listbox" wire:model="proyecto_id">
                            <flux:select.option value=""></flux:select.option>
                            @foreach ($proyectos as $proyecto)
                                <flux:select.option value="{{ $proyecto['id'] }}">{{ $proyecto['nombre'] }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:error name="proyecto_id" />
                    </flux:field>
                </div>

                <div class="px-4 py-3">
                    <flux:field variant="inline">
                        <flux:checkbox wire:model="es_extension" />

                        <div>
                            <flux:label class="text-sm font-medium">
                                Compartir gastos con solicitud de viático
                            </flux:label>

                            <flux:description class="text-xs">
                                Indica que esta comprobación por tarjeta comparte gastos o comprobantes
                                relacionados con una solicitud de viático previamente registrada.
                            </flux:description>
                        </div>

                        <flux:error name="es_extension" />
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
                    {{ $editingId ? 'Guardar cambios' : 'Crear comprobación' }}
                </span>
                <span wire:loading wire:target="save">
                    Guardando...
                </span>
            </flux:button>
        </div>
    </div>
</flux:modal>
