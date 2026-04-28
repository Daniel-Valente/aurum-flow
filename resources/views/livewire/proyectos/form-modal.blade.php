<flux:modal name="proyecto-form" class="w-full max-x-lg" scroll="body">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar proyecto' : 'Nuevo proyecto' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId ? 'Modifica los datos del proyecto' : 'Completa los datos para registrar un nuevo proyecto.' }}
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
                    placeholder="Ej. PRY-0001"
                    required
                    :disabled="$editingId !== null"
                />

                <flux:error name="codigo" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Tipo
                </flux:label>
                <flux:select variant="listbox" wire:model="tipo" required>
                    <flux:select.option value=""></flux:select.option>
                    <flux:select.option value="Proyecto">Proyecto</flux:select.option>
                    <flux:select.option value="Ruta">Ruta</flux:select.option>
                    <flux:select.option value="Zona">Zona</flux:select.option>
                </flux:select>

                <flux:error name="tipo" />
            </flux:field>
        </div>

        <div class="mt-3 flex">
            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Nombre
                </flux:label>
                <flux:input
                    wire:model="nombre"
                    placeholder="Ej. Cedis Orizaba"
                    required
                />

                <flux:error name="nombre" />
            </flux:field>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Cliente
                </flux:label>
                <flux:input
                    wire:model="cliente"
                    placeholder="Ej. Grupo Salinas"
                />
            </flux:field>
            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Responsable
                </flux:label>
                <flux:select variant="listbox" wire:model.live="responsable_id" clearable>
                    <flux:select.option value=""></flux:select.option>
                    @foreach ($empleados as $empleado)
                        <flux:select.option value="{{ $empleado['id'] }}">{{ $empleado['nombre_completo'] }}</flux:select.option>
                    @endforeach
                </flux:select>
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

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Prioridad
                </flux:label>
                <flux:select variant="listbox" wire:model="prioridad">
                    <flux:select.option value=""></flux:select.option>
                    <flux:select.option value="Baja">Baja</flux:select.option>
                    <flux:select.option value="Media">Media</flux:select.option>
                    <flux:select.option value="Alta">Alta</flux:select.option>
                </flux:select>

                <flux:error name="prioridad" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Estado operativo
                </flux:label>
                <flux:select variant="listbox" wire:model="estado_operativo">
                    <flux:select.option value=""></flux:select.option>
                    <flux:select.option value="Draft">Draft</flux:select.option>
                    <flux:select.option value="Activo">Activo</flux:select.option>
                    <flux:select.option value="Cerrado">Cerrado</flux:select.option>
                </flux:select>

                <flux:error name="estado_operativo" />
            </flux:field>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Centro de Costos
                </flux:label>
                <flux:select variant="listbox" wire:model.live="centro_costo_id" clearable>
                    <flux:select.option value=""></flux:select.option>
                    @foreach ($centrosCostos as $centroCosto)
                        <flux:select.option value="{{ $centroCosto['id'] }}">{{ $centroCosto['nombre'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:error name="centro_costo_id" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Presupuesto total
                </flux:label>
                <flux:input
                    wire:model="presupuesto_total"
                    placeholder="Ej. 0.01"
                    type="number"
                    min="0.00"
                    step=".01"
                />

                <flux:error name="presupuesto_total" />
            </flux:field>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Fecha inicio
                </flux:label>
                <flux:date-picker wire:model="fecha_inicio" />
                <flux:error name="fecha_inicio"/>
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Fecha fin
                </flux:label>
                <flux:date-picker wire:model="fecha_fin" />
                <flux:error name="fecha_fin" />
            </flux:field>
        </div>

        <flux:separator />

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Ciudad
                </flux:label>
                <flux:input
                    wire:model="ciudad"
                    placeholder="Ej. Monterrey"
                />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Estado
                </flux:label>
                <flux:input
                    wire:model="estado"
                    placeholder="Nuevo León"
                />
            </flux:field>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    Región
                </flux:label>
                <flux:input
                    wire:model="region"
                    placeholder="Ej. Noreste"
                />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Opcional">
                    País
                </flux:label>
                <flux:input
                    wire:model="pais"
                    placeholder="México"
                />
            </flux:field>
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
                    {{ $editingId ? 'Guardar cambios' : 'Crear proyecto' }}
                </span>
                <span wire:loading wire:target="save">Guardando...</span>
            </flux:button>
        </div>
    </div>
</flux:modal>
