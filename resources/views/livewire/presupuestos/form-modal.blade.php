<flux:modal name="presupuesto-form" class="w-full max-w-3xl" scroll="body">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar presupuesto' : 'Nuevo presupuesto' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId
                    ? 'Modifica la información del presupuesto.'
                    : 'Completa los datos para crear un nuevo presupuesto.' }}
            </flux:subheading>
        </div>

        <div class="space-y-5">

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Información básica</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Datos generales del presupuesto.
                    </p>
                </div>

                <div class="p-4 space-y-5">

                    @if($editingId)
                        <flux:field>
                            <flux:label>Código</flux:label>
                            <flux:input wire:model="codigo" disabled />
                        </flux:field>
                    @endif

                    <flux:field>
                        <flux:label badge="Requerido">Nombre</flux:label>
                        <flux:input wire:model="nombre" placeholder="Ej. Presupuesto Ventas Q1 2026" required />
                        <flux:error name="nombre" />
                    </flux:field>

                    <flux:field>
                        <flux:label badge="Opcional">Descripción</flux:label>
                        <flux:textarea wire:model="descripcion" placeholder="Descripción del presupuesto..." rows="2" />
                        <flux:error name="descripcion" />
                    </flux:field>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">Tipo</flux:label>
                            <flux:select variant="listbox" wire:model.live="tipo" required>
                                <flux:select.option value="empresa">Empresa</flux:select.option>
                                <flux:select.option value="area">Área</flux:select.option>
                                <flux:select.option value="empleado">Empleado</flux:select.option>
                                <flux:select.option value="proyecto">Proyecto</flux:select.option>
                            </flux:select>
                            <flux:error name="tipo" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Requerido">Período</flux:label>
                            <flux:select variant="listbox" wire:model.live="periodo" required>
                                <flux:select.option value="diario">Diario</flux:select.option>
                                <flux:select.option value="semanal">Semanal</flux:select.option>
                                <flux:select.option value="quincenal">Quincenal</flux:select.option>
                                <flux:select.option value="mensual">Mensual</flux:select.option>
                                <flux:select.option value="trimestral">Trimestral</flux:select.option>
                                <flux:select.option value="semestral">Semestral</flux:select.option>
                                <flux:select.option value="anual">Anual</flux:select.option>
                                <flux:select.option value="proyecto">Proyecto</flux:select.option>
                                <flux:select.option value="evento">Evento</flux:select.option>
                            </flux:select>
                            <flux:error name="periodo" />
                        </flux:field>
                    </div>

                    @if($tipo === 'empresa')
                        <flux:field>
                            <flux:label badge="Requerido">Empresa</flux:label>
                            <flux:select variant="listbox" wire:model="empresa_id" required>
                                @foreach($empresas as $empresa)
                                    <flux:select.option value="{{ $empresa['id'] }}">
                                        {{ $empresa['nombre'] }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="empresa_id" />
                        </flux:field>
                    @endif

                    @if($tipo === 'area')
                        <flux:field>
                            <flux:label badge="Requerido">Área</flux:label>
                            <flux:select variant="listbox" wire:model="area_id" required>
                                @foreach($areas as $area)
                                    <flux:select.option value="{{ $area['id'] }}">
                                        {{ $area['nombre'] }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="area_id" />
                        </flux:field>
                    @endif

                    @if($tipo === 'empleado')
                        <flux:field>
                            <flux:label badge="Requerido">Empleado</flux:label>
                            <flux:select variant="listbox" wire:model="empleado_id" required searchable>
                                @foreach($empleados as $empleado)
                                    <flux:select.option value="{{ $empleado['id'] }}">
                                        {{ $empleado['nombre_completo'] }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="empleado_id" />
                        </flux:field>
                    @endif

                    @if($tipo === 'proyecto')
                        <flux:field>
                            <flux:label badge="Requerido">Proyecto</flux:label>
                            <flux:select variant="listbox" wire:model="proyecto_id" required>
                                @foreach($proyectos as $proyecto)
                                    <flux:select.option value="{{ $proyecto['id'] }}">
                                        {{ $proyecto['nombre'] }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="proyecto_id" />
                        </flux:field>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Montos y vigencia</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Configuración financiera y temporal.
                    </p>
                </div>

                <div class="p-4 space-y-5">

                    <flux:field>
                        <flux:label badge="Requerido">Monto total ($)</flux:label>
                        <flux:input
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model="monto_total"
                            placeholder="Ej. 50000.00"
                            required
                        />
                        <flux:error name="monto_total" />
                    </flux:field>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">Fecha inicio</flux:label>
                            <flux:date-picker
                                selectable-header
                                wire:model="fecha_inicio"
                                fixed-weeks
                            />
                            <flux:error name="fecha_inicio" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Requerido">Fecha fin</flux:label>
                            <flux:date-picker
                                selectable-header
                                wire:model="fecha_fin"
                                fixed-weeks
                            />
                            <flux:error name="fecha_fin" />
                        </flux:field>
                    </div>

                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Alertas y renovación</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Umbrales de notificación y configuración de renovación automática.
                    </p>
                </div>

                <div class="p-4 space-y-5">
                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">Alerta (%)</flux:label>
                            <flux:input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                wire:model="alerta_porcentaje"
                                placeholder="80.00"
                                required
                            />
                            <flux:description class="text-xs">
                                Se generará alerta warning al alcanzar este porcentaje.
                            </flux:description>
                            <flux:error name="alerta_porcentaje" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Requerido">Crítico (%)</flux:label>
                            <flux:input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                wire:model="critico_porcentaje"
                                placeholder="95.00"
                                required
                            />
                            <flux:description class="text-xs">
                                Se generará alerta crítica al alcanzar este porcentaje.
                            </flux:description>
                            <flux:error name="critico_porcentaje" />
                        </flux:field>
                    </div>

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                            <p class="text-sm font-medium">Renovación automática</p>
                            <p class="text-xs text-zinc-500 mt-0.5">
                                El presupuesto se renovará automáticamente según la frecuencia configurada.
                            </p>
                        </div>

                        <div class="p-4 space-y-4">
                            <flux:field variant="inline">
                                <flux:checkbox wire:model.live="renovable" />
                                <div>
                                    <flux:label class="text-sm font-medium">
                                        Habilitar renovación automática
                                    </flux:label>
                                    <flux:description class="text-xs">
                                        El sistema creará un nuevo presupuesto cuando este venza.
                                    </flux:description>
                                </div>
                            </flux:field>

                            @if($renovable)
                                <flux:field>
                                    <flux:label badge="Requerido">Frecuencia de renovación</flux:label>
                                    <flux:select variant="listbox" wire:model="frecuencia_renovacion" required>
                                        <flux:select.option value="diario">Diario</flux:select.option>
                                        <flux:select.option value="semanal">Semanal</flux:select.option>
                                        <flux:select.option value="quincenal">Quincenal</flux:select.option>
                                        <flux:select.option value="mensual">Mensual</flux:select.option>
                                        <flux:select.option value="trimestral">Trimestral</flux:select.option>
                                        <flux:select.option value="semestral">Semestral</flux:select.option>
                                        <flux:select.option value="anual">Anual</flux:select.option>
                                    </flux:select>
                                    <flux:error name="frecuencia_renovacion" />
                                </flux:field>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <flux:field>
                <flux:label badge="Opcional">Notas</flux:label>
                <flux:textarea wire:model="notas" placeholder="Notas adicionales..." rows="3" />
                <flux:error name="notas" />
            </flux:field>
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
                    {{ $editingId ? 'Guardar cambios' : 'Crear presupuesto' }}
                </span>
                <span wire:loading wire:target="save">
                    Guardando…
                </span>
            </flux:button>
        </div>
    </div>
</flux:modal>
