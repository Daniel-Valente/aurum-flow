<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">Políticas y Roles</flux:heading>
            <flux:subheading>Matríz de limites por rol y concepto con vigencia e historial</flux:subheading>
        </div>
        @can('politicas.crear')
        <flux:button variant="primary" icon="plus" wire:click="openCreate">
            Nueva Política
        </flux:button>
        @endcan
    </div>

    <flux:card>
        <flux:separator text="Capacidades por rol"/>
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="pl-4">ROL</flux:table.column>
                <flux:table.column>CAPACIDADES EFECTIVAS</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                <flux:table.row>
                    <flux:table.cell class="pl-4">
                        Admin
                    </flux:table.cell>
                    <flux:table.cell>
                        Gestión global de políticas, excepciones nivel 2, auditoria total.
                    </flux:table.cell>
                    <flux:table.cell></flux:table.cell>
                </flux:table.row>
                <flux:table.row>
                    <flux:table.cell class="pl-4">
                        Gerente
                    </flux:table.cell>
                    <flux:table.cell>
                        Aprobación nivel 1 por área/departamento y gestión operativa de solicitudes.
                    </flux:table.cell>
                    <flux:table.cell></flux:table.cell>
                </flux:table.row>
                <flux:table.row>
                    <flux:table.cell class="pl-4">
                        Operativo
                    </flux:table.cell>
                    <flux:table.cell>
                        Captura de solicitudes/gastos bajo políticas vigentes de su rol.
                    </flux:table.cell>
                    <flux:table.cell></flux:table.cell>
                </flux:table.row>
            </flux:table.rows>
        </flux:table>
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
                <flux:table.column class="flex justify-end">ACciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($politicas as $politica)
                    <flux:table.row :key="$politica->id">
                        <flux:table.cell size="xs" class="pl-4">
                            {{ $politica->roles?->name ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell variant="strong">
                            <div class="flex flex-col gap-3">
                                <span>{{ $politica->conceptos?->nombre }}</span>
                                <span size="xs" class="font-mono text-zinc-500 dark:text-zinc-400 px-4">
                                    {{ $politica->conceptos?->codigo }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            ${{ $politica->monto_max }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-3">
                                <span>{{ $politica->tipo_limite }}</span>
                                <span size="xs" class="font-mono text-zinc-500 dark:text-zinc-400 px-4">
                                    Excepción: {{ $politica->permite_excepcion ? 'SI' : 'NO' }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell></flux:table.cell>

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
</div>
