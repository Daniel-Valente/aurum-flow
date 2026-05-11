<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Políticas</flux:heading>
            <flux:subheading>Matríz de limites por rol y concepto con vigencia e historial</flux:subheading>
        </div>
        @can('politicas.crear')
        <flux:button variant="primary" icon="plus" wire:click="openCreate">
            Nueva Política
        </flux:button>
        @endcan
    </div>

    <flux:card>
    <div class="space-y-4">

        <div>
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">
                ¿Cómo funcionan las políticas?
            </h3>

            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                Las políticas definen las reglas operativas y fiscales aplicables
                a las solicitudes y comprobaciones de gastos según el concepto,
                el rol del colaborador y su vigencia.
            </p>

            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                Durante la captura, el sistema valida automáticamente límites,
                tipos de comprobación permitidos, montos, porcentajes y restricciones
                configuradas para cada política activa.
            </p>

            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                Cuando una solicitud excede las condiciones establecidas,
                puede generarse una excepción sujeta a aprobación escalonada:
                primero por un responsable operativo o gerente (Nivel 1)
                y posteriormente por administración o finanzas (Nivel 2).
            </p>

            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                Las políticas solamente aplican dentro de su periodo de vigencia,
                permitiendo mantener reglas distintas según cambios operativos,
                fiscales o administrativos.
            </p>
        </div>

        <flux:separator text="Flujo operativo" />

        <flux:table>
            <flux:table.columns>
                <flux:table.column class="pl-4">ROL / NIVEL</flux:table.column>
                <flux:table.column>RESPONSABILIDAD OPERATIVA</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                <flux:table.row>
                    <flux:table.cell class="pl-4">
                        Operativo
                    </flux:table.cell>

                    <flux:table.cell>
                        Registra solicitudes y comprobaciones conforme
                        a las políticas activas asignadas a su rol.
                    </flux:table.cell>
                </flux:table.row>

                <flux:table.row>
                    <flux:table.cell class="pl-4">
                        Gerente / Responsable
                    </flux:table.cell>

                    <flux:table.cell>
                        Revisa y aprueba excepciones operativas
                        o desviaciones iniciales (Nivel 1).
                    </flux:table.cell>
                </flux:table.row>

                <flux:table.row>
                    <flux:table.cell class="pl-4">
                        Administración / Finanzas
                    </flux:table.cell>

                    <flux:table.cell>
                        Valida excepciones finales, control fiscal,
                        auditoría y autorizaciones administrativas (Nivel 2).
                    </flux:table.cell>
                </flux:table.row>

            </flux:table.rows>
        </flux:table>

    </div>
</flux:card>

    <flux:card class="py-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <flux:field>
                    <flux:label>Rol</flux:label>
                    <flux:select variant="listbox" wire:model.live="rolId">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach ($roles as $role)
                            <flux:select.option value="{{ $role['id'] }}">
                                {{ $role['name'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="flex-1">
                <flux:field>
                    <flux:label>Concepto</flux:label>
                    <flux:select variant="listbox" wire:model.live="conceptoId">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach ($conceptos as $concepto)
                            <flux:select.option value="{{ $concepto['id'] }}">
                                {{ $concepto['nombre'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="flex-1">
                <flux:field>
                    <flux:label>Estatus</flux:label>
                    <flux:select variant="listbox" wire:model.live="estatus">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="1">Activo</flux:select.option>
                        <flux:select.option value="0">Inactivo</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="flex-1">
                <flux:field>
                    <flux:label>Vigencia</flux:label>
                    <flux:select variant="listbox" wire:model.live="vigencia">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="Vigente">Vigente</flux:select.option>
                        <flux:select.option value="Futura">Futura</flux:select.option>
                        <flux:select.option value="Expirada">Expirada</flux:select.option>
                        <flux:select.option value="Sin vigencia">Sin vigencia</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>
        </div>
    </flux:card>

    <flux:card class="py-3">
        <div class="flex flex-col gap-1 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
            <flux:text size="sm" class="text-zinc-500">
                Total encontrados:
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $politicas->total() }}
                </span>
            </flux:text>

            <flux:text size="sm" class="text-zinc-500">
                Páginas {{ $politicas->currentPage() }} de {{ $politicas->lastPage() }}
            </flux:text>
        </div>

        <flux:table :paginate="$politicas">
            <flux:table.columns>
                <flux:table.column class="pl-4">Rol</flux:table.column>
                <flux:table.column>Concepto</flux:table.column>
                <flux:table.column>Límite</flux:table.column>
                <flux:table.column>Frecuencia</flux:table.column>
                <flux:table.column>Vigencia</flux:table.column>
                <flux:table.column>Estatus</flux:table.column>
                <flux:table.column class="flex justify-end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($politicas as $politica)
                    <flux:table.row :key="$politica->id">
                        <flux:table.cell size="xs" class="pl-4">
                            {{ $politica->rol_nombre ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell variant="strong">
                            <div class="flex flex-col gap-3">
                                <span>{{ $politica->concepto_nombre }}</span>
                                <span size="xs" class="font-mono text-zinc-500 dark:text-zinc-400 px-4">
                                    {{ $politica->concepto_codigo }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ Number::currency($politica->monto_max ?? 0.00, in: 'MXN') }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-3">
                                <span>{{ $politica->tipo_limite }}</span>
                                <span size="xs" class="font-mono text-zinc-500 dark:text-zinc-400 px-4">
                                    Excepción: {{ $politica->permite_excepcion ? 'SI' : 'NO' }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @php
                                $estado = $politica->estado_vigencia;

                                $color = match($estado) {
                                    'Vigente' => 'green',
                                    'Futura' => 'blue',
                                    'Expirada' => 'red',
                                    'Sin vigencia' => 'gray',
                                };
                            @endphp

                            <flux:badge color="{{ $color }}">
                                {{ $estado }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($politica->estatus)
                                <flux:badge color="green" size="sm" inset="top bottom">Activo</flux:badge>
                            @else
                                <flux:badge color="red" size="sm" inset="top bottom">Inactivo</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    insert="top bottom"
                                    wire:click="openDetail({{ $politica->id }})"
                                    title="Ver"
                                />

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="clock"
                                    insert="top bottom"
                                    wire:click=""
                                    title="Historial"
                                />

                                @can('politicas.editar')
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil"
                                    insert="top bottom"
                                    wire:click="openEdit({{ $politica->id }})"
                                    title="Editar"
                                />
                                @endcan

                                @if ($politica->estatus)
                                    @can('politicas.eliminar')
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            insert="top bottom"
                                            wire:click="openDelete({{ $politica->id }})"
                                            title="Deshabilitar"
                                        />
                                    @endcan
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="inbox" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                <flux:text class="text-zinc-400">No se encontraron politicas</flux:text>
                                @if ($search || $estatus !== '' || $vigencia !== '' || $rolId || $conceptoId)
                                    <flux:button size="sm" variant="ghost" wire:click="clearFilters">
                                        Limpiar filtros
                                    </flux:button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    @livewire('politicas.form-modal')
    @livewire('politicas.detail-modal')

    <flux:modal name="politica-delete" class="w-full max-w-sm">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon
                        name="exclamation-triangle"
                        class="size-5 text-red-600 dark:text-red-400"
                    />
                </div>
                <div>
                    <flux:heading size="lg">Deshabilitar política</flux:heading>
                    <flux:subheading class="mt-1">
                        ¿Estás seguro deshabilitar la política <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deletingNombre }}</span>?
                    </flux:subheading>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button
                    variant="danger"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                >
                    <span wire:loading.remove wire:target="delete">Deshabilitar</span>
                    <span wire:loading wire:target="delete">Deshabilitando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
