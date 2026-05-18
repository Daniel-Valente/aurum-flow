<flux:modal name="proyecto-form" class="w-full max-w-2xl" scroll="body">
    <div class="space-y-6">

        {{-- ── Header ───────────────────────────────────────────── --}}
        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar proyecto' : 'Nuevo proyecto' }}
            </flux:heading>
            <flux:subheading>
                {{ $editingId
                    ? 'Modifica la información del proyecto.'
                    : 'Completa los datos para registrar un nuevo proyecto.' }}
            </flux:subheading>
        </div>

        <div class="space-y-5">

            {{-- ── Identificación ───────────────────────────────── --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium">Identificación</p>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Datos básicos para identificar el proyecto dentro del sistema.
                    </p>
                </div>

                <div class="p-4 space-y-5">

                    <div class="grid auto-rows-min gap-5 md:grid-cols-2">
                        <div class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Requerido">Código</flux:label>
                                <flux:input
                                    wire:model="codigo"
                                    placeholder="Ej. PRY-2026-0001"
                                    required
                                    :disabled="true"
                                />
                                <flux:description class="text-xs">
                                    Identificador único del proyecto, este identificador se genera en automático.
                                </flux:description>
                                <flux:error name="codigo" />
                            </flux:field>
                        </div>

                        <dvi class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Requerido">Tipo</flux:label>
                                <flux:select variant="listbox" wire:model="tipo">
                                    <flux:select.option value=""></flux:select.option>
                                    <flux:select.option value="Proyecto">Proyecto</flux:select.option>
                                    <flux:select.option value="Ruta">Ruta</flux:select.option>
                                    <flux:select.option value="Zona">Zona</flux:select.option>
                                </flux:select>
                                <flux:error name="tipo" />
                            </flux:field>
                        </dvi>
                    </div>

                    <div class="grid auto-rows-min gap-5 md:grid-cols-2">
                        <flux:field>
                            <flux:label badge="Requerido">Nombre</flux:label>
                            <flux:input wire:model="nombre" placeholder="Ej. CEDIS Orizaba" required />
                            <flux:error name="nombre" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Opcional">Empresa</flux:label>
                            <flux:select variant="listbox" wire:model.live="empresa_id" clearable>
                                <flux:select.option value=""></flux:select.option>
                                @foreach ($empresas as $empresa)
                                    <flux:select.option value="{{ $empresa['id'] }}">
                                        {{ $empresa['nombre'] }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>

                    <div class="grid auto-rows-min gap-5 md:grid-cols-2">
                        <div class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Opcional">Cliente</flux:label>
                                <flux:input wire:model="cliente" placeholder="Ej. Grupo Salinas" />
                            </flux:field>
                        </div>

                        <div calss="flex flex-col">
                            <flux:field>
                                <flux:label badge="Opcional">Responsable</flux:label>
                                <flux:select variant="listbox" wire:model.live="responsable_id" clearable>
                                    <flux:select.option value=""></flux:select.option>
                                    @foreach ($empleados as $empleado)
                                        <flux:select.option value="{{ $empleado['id'] }}">
                                            {{ $empleado['nombre_completo'] }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                        </div>
                    </div>

                    <flux:field>
                        <flux:label badge="Opcional">Descripción</flux:label>
                        <flux:textarea resize="none" wire:model="descripcion" />
                    </flux:field>

                </div>
            </div>

            {{-- ── Configuración operativa ───────────────────────── --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium">Configuración operativa</p>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Define el estado y control financiero del proyecto.
                    </p>
                </div>

                <div class="p-4 space-y-5">
                    <flux:field>
                        <flux:label badge="Requerido">Estado operativo</flux:label>
                        <flux:select variant="listbox" wire:model="estado_operativo">
                            <flux:select.option value=""></flux:select.option>
                            <flux:select.option value="Draft">Draft</flux:select.option>
                            <flux:select.option value="Activo">Activo</flux:select.option>
                            <flux:select.option value="Cerrado">Cerrado</flux:select.option>
                        </flux:select>
                        <flux:error name="estado_operativo" />
                    </flux:field>

                    <div class="grid auto-rows-min gap-5 md:grid-cols-2">
                        <div class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Requerido">Referencia contable</flux:label>
                                <flux:select variant="listbox" wire:model.live="centro_costo_id">
                                    <flux:select.option value=""></flux:select.option>
                                    @foreach ($centrosCostos as $centro)
                                        <flux:select.option value="{{ $centro['id'] }}">
                                            {{ !empty($centro['nombre']) ? $centro['nombre'] : $centro['cuenta_contable'] }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="centro_costo_id" />
                            </flux:field>
                        </div>

                        <div class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Opcional">Presupuesto total</flux:label>
                                <flux:input
                                    wire:model="presupuesto_total"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Ej. 150000.00"
                                />
                                <flux:description class="text-xs">
                                    Monto estimado total del proyecto.
                                </flux:description>
                                <flux:error name="presupuesto_total" />
                            </flux:field>
                        </div>
                    </div>

                    <div class="grid auto-rows-min gap-5 md:grid-cols-2">
                        <div class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Opcional">Fecha inicio</flux:label>
                                <flux:date-picker selectable-header wire:model="fecha_inicio" fixed-weeks />
                            </flux:field>
                        </div>

                        <div class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Opcional">Fecha fin</flux:label>
                                <flux:date-picker selectable-header wire:model="fecha_fin" fixed-weeks />
                            </flux:field>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Ubicación ───────────────────────────────────── --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium">Ubicación</p>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Información geográfica del proyecto.
                    </p>
                </div>

                <div class="p-4 space-y-5">

                    <div class="grid auto-rows-min gap-5 md:grid-cols-2">
                        <div class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Opcional">Cuidad</flux:label>
                                <flux:select wire:model="ciudad" variant="combobox">
                                    <x-slot name="input">
                                        <flux:select.input
                                            wire:model="searchCiudad"
                                            placeholder="Selecciona o escribe una nueva ciudad"
                                        />
                                    </x-slot>
                                    @foreach ($ciudades as $ciudad)
                                        <flux:select.option :wire:key="$ciudad">{{ $ciudad }}</flux:select.option>
                                    @endforeach
                                    <flux:select.option.create wire:click="createCiudad" min-length="2">
                                        Crear "<span wire:text="searchCiudad"></span>"
                                    </flux:select.option.create>

                                    <flux:error name="ciudad"/>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Opcional">Estado</flux:label>
                                <flux:select wire:model="estado" variant="combobox">
                                    <x-slot name="input">
                                        <flux:select.input
                                            wire:model="searchEstado"
                                            placeholder="Selecciona o escribe un nuevo estado"
                                        />
                                    </x-slot>
                                    @foreach ($estados as $estado)
                                        <flux:select.option :wire:key="$estado">{{ $estado }}</flux:select.option>
                                    @endforeach
                                    <flux:select.option.create wire:click="createEstado" min-length="2">
                                        Crear "<span wire:text="searchEstado"></span>"
                                    </flux:select.option.create>

                                    <flux:error name="estado"/>
                                </flux:select>
                            </flux:field>
                        </div>
                    </div>

                    <div class="grid auto-rows-min gap-5 md:grid-cols-2">
                        <div class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Opcional">Región</flux:label>
                                <flux:select wire:model="region" variant="combobox">
                                    <x-slot name="input">
                                        <flux:select.input
                                            wire:model="searchRegion"
                                            placeholder="Selecciona o escribe una nueva region"
                                        />
                                    </x-slot>
                                    @foreach ($regiones as $region)
                                        <flux:select.option :wire:key="$region">{{ $region }}</flux:select.option>
                                    @endforeach
                                    <flux:select.option.create wire:click="createRegion" min-length="2">
                                        Crear "<span wire:text="searchRegion"></span>"
                                    </flux:select.option.create>

                                    <flux:error name="region"/>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="flex flex-col">
                            <flux:field>
                                <flux:label badge="Opcional">País</flux:label>
                                <flux:select wire:model="pais" variant="combobox">
                                    <x-slot name="input">
                                        <flux:select.input
                                            wire:model="searchPais"
                                            placeholder="Selecciona o escribe un nuevo país"
                                        />
                                    </x-slot>
                                    @foreach ($paises as $pais)
                                        <flux:select.option :wire:key="$pais">{{ $pais }}</flux:select.option>
                                    @endforeach
                                    <flux:select.option.create wire:click="createPais" min-length="2">
                                        Crear "<span wire:text="searchPais"></span>"
                                    </flux:select.option.create>

                                    <flux:error name="pais"/>
                                </flux:select>
                            </flux:field>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- ── Acciones ───────────────────────────────────────── --}}
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
                    {{ $editingId ? 'Guardar cambios' : 'Crear proyecto' }}
                </span>
                <span wire:loading wire:target="save">
                    Guardando…
                </span>
            </flux:button>
        </div>

    </div>
</flux:modal>
