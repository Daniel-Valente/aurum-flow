<flux:modal name="politica-form" class="w-full max-x-lg" scroll="body">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar política' : 'Nueva política' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId ? 'Modifica los datos de la política' : 'Completa los datos para registrar una nueva política.' }}
            </flux:subheading>
        </div>
    </div>

    <div class="space-y-4">
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Rol
                </flux:label>
                <flux:select variant="listbox" wire:model.live="roleId" required>
                    <flux:select.option value=""></flux:select.option>
                    @foreach ($roles as $role)
                        <flux:select.option value="{{ $role['id'] }}">{{ $role['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:error name="roleId" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Concepto
                </flux:label>
                <flux:select variant="listbox" wire:model="concepto_id" :key="$roleId" required>
                    <flux:select.option value=""></flux:select.option>
                    @foreach ($conceptos as $concepto)
                        <flux:select.option value="{{ $concepto['id'] }}" class="flex gap-3">
                            <span class="text-xs fonto-mono text-zinc-500 dark:text-zinc-400">{{ $concepto['codigo'] }}</span> {{ $concepto['nombre'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:error name="concepto_id" />
            </flux:field>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Monto máximo ($)
                </flux:label>
                <flux:input
                    wire:model="monto_max"
                    placeholder="Ej. 0.01"
                    type="number"
                    min="0.00"
                    step=".01"
                    required
                    />

                <flux:error name="monto_max" />
            </flux:field>

            <flux:field class="w-full">
                <flux:label badge="Requerido">
                    Tipo límite
                </flux:label>
                <flux:select variant="listbox" wire:model="tipo_limite" required>
                    <flux:select.option value=""></flux:select.option>
                    <flux:select.option value="Diario">Diario</flux:select.option>
                    <flux:select.option value="Viaje">Viaje</flux:select.option>
                </flux:select>

                <flux:error name="tipo_limite" />
            </flux:field>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
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

                <flux:error name="vigencia_desde" />
            </flux:field>
        </div>

        @if ($this->isEditing())
            <div class="mt-3 flex">
                <flux:field class="w-full">
                    <flux:label badge="Requerido">
                        Motivo de cambio
                    </flux:label>

                    <flux:textarea
                        resize="none"
                        wire:model="motivo"
                        required
                    />

                    <flux:error name="motivo"/>
                </flux:field>
            </div>
        @endif

        <div class="mt-3 p-1 rounded-lg bg-zinc-100 dark:bg-zinc-700">
            <span class="text-xs font-mono text-zinc-500 dark:text-zinc-400 px-4">
                Impacto: 0 solicitudes activas, 0 gastos pendientes, 0 gastos rechazados por politica.
            </span>
        </div>

        <div class="mt-3 p-1 rounded-lg border-2 border-zinc-100 dark:border-zinc-700">
            <div class="px-4">
                <flux:field variant="inline">
                    <flux:checkbox wire:model="permite_excepcion" />
                    <flux:label class="text-xs font-mono ">Permitir excepción fuera del límite</flux:label>
                    <flux:error name="permite_excepcion" />
                </flux:field>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-3">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button
                variant="primary"
                wire:click="save"
                wire:loading.attr="disabeld"
                wire:target="save"
                >
                <span wire:loading.remove wire:target="save">
                    {{ $editingId ? 'Guardar cambios' : 'Crear política' }}
                </span>
                <span wire:loading wire:target="save">Guardando...</span>
            </flux:button>
        </div>
    </div>
</flux:modal>
