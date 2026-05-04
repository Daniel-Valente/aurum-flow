<flux:modal name="import-empleados" class="w-full max-w-3xl" scroll="body">
    <div class="flex flex-col gap-6">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="lg">Importar empleados</flux:heading>
                <flux:subheading>
                    Carga masiva desde Excel. Descarga la plantilla, llénala y súbela aquí.
                </flux:subheading>
            </div>
            {{-- Descarga template --}}
            <flux:button
                variant="ghost"
                icon="arrow-down-tray"
                size="sm"
                wire:click="descargarTemplate"
            >
                Descargar plantilla
            </flux:button>
        </div>

        <flux:separator />

        @if (!$validado)
            {{-- ── Paso 1: subir archivo ── --}}
            <div class="space-y-4">
                <div class="space-y-3">
                    <flux:file-upload wire:model="archivo" label="Archivo Excel">
                        <flux:file-upload.dropzone
                            heading="Arrastra tu archivo o haz clic"
                            text=".xlsx o .xls (máx. 5MB)"
                            with-progress
                            inline
                        />
                    </flux:file-upload>

                    <flux:error name="archivo" />

                    {{-- Preview del archivo seleccionado --}}
                    @if ($archivo)
                        <div class="mt-2">
                            <flux:file-item :heading="$archivo->getClientOriginalName()">
                                <x-slot name="actions">
                                    <flux:file-item.remove wire:click="$set('archivo', null)" />
                                </x-slot>
                            </flux:file-item>
                        </div>
                    @endif
                </div>

                {{-- Instrucciones rápidas --}}
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-3 space-y-1">
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-400">Instrucciones</p>
                    <ul class="text-xs text-blue-600 dark:text-blue-300 space-y-0.5 list-disc list-inside">
                        <li>Usa exactamente los nombres de rol, área y centro de costo del sistema.</li>
                        <li>Fecha de ingreso en formato <span class="font-mono">YYYY-MM-DD</span>.</li>
                        <li>Tarjeta corporativa: escribe <span class="font-mono">si</span> o <span class="font-mono">no</span>.</li>
                        <li>RFC: 13 caracteres · CURP: 18 caracteres · CLABE: 18 dígitos.</li>
                        <li>La fila 2 de la plantilla es un ejemplo, puedes borrarla.</li>
                    </ul>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button
                    variant="primary"
                    icon="magnifying-glass"
                    wire:click="validar"
                    wire:loading.attr="disabled"
                    wire:target="validar"
                >
                    <span wire:loading.remove wire:target="validar">Validar archivo</span>
                    <span wire:loading wire:target="validar">Analizando…</span>
                </flux:button>
            </div>

        @else
            {{-- ── Paso 2: preview de validación ── --}}

            {{-- Resumen KPIs --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 px-4 py-3 text-center">
                    <p class="text-xs uppercase text-zinc-400">Total filas</p>
                    <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">
                        {{ $totalOk + $totalError }}
                    </p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20 px-4 py-3 text-center">
                    <p class="text-xs uppercase text-emerald-500">Listas para importar</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $totalOk }}</p>
                </div>
                <div class="rounded-lg border border-rose-200 bg-rose-50 dark:border-rose-800 dark:bg-rose-900/20 px-4 py-3 text-center">
                    <p class="text-xs uppercase text-rose-500">Con errores</p>
                    <p class="text-2xl font-bold text-rose-600">{{ $totalError }}</p>
                </div>
            </div>

            @if ($totalError > 0)
                <div class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20 px-3 py-2.5">
                    <flux:icon.exclamation-triangle class="size-4 text-amber-500 shrink-0" />
                    <flux:text size="sm" class="text-amber-700 dark:text-amber-400">
                        Corrige los errores en tu Excel y vuelve a subir el archivo. No se puede importar con errores pendientes.
                    </flux:text>
                </div>
            @endif

            {{-- Tabla preview --}}
            <div class="max-h-80 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-zinc-500 w-10">Fila</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-zinc-500">Nombre</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-zinc-500">Email</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-zinc-500">Rol</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-zinc-500">Estado / Errores</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($preview as $fila)
                            <tr class="{{ $fila['estado'] === 'error'
                                ? 'bg-rose-50 dark:bg-rose-900/10'
                                : 'bg-white dark:bg-zinc-900' }}">
                                <td class="px-3 py-2 font-mono text-xs text-zinc-400">
                                    {{ $fila['fila'] }}
                                </td>
                                <td class="px-3 py-2 font-medium">
                                    {{ $fila['nombre_completo'] ?: '—' }}
                                </td>
                                <td class="px-3 py-2 text-zinc-500 text-xs">
                                    {{ $fila['email'] ?: '—' }}
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    {{ $fila['rol'] ?: '—' }}
                                </td>
                                <td class="px-3 py-2">
                                    @if ($fila['estado'] === 'ok')
                                        <flux:badge color="green" size="sm">
                                            <flux:icon.check class="size-3 mr-1" /> Lista
                                        </flux:badge>
                                    @else
                                        <div class="flex flex-col gap-1">
                                            @foreach ($fila['errores'] as $error)
                                                <span class="text-xs text-rose-600 dark:text-rose-400">
                                                    · {{ $error }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between gap-3">
                <flux:button
                    variant="ghost"
                    icon="arrow-left"
                    wire:click="resetImport"
                >
                    Subir otro archivo
                </flux:button>

                <flux:button
                    variant="primary"
                    color="green"
                    icon="cloud-arrow-up"
                    wire:click="confirmarImport"
                    wire:loading.attr="disabled"
                    wire:target="confirmarImport"
                    :disabled="$totalError > 0"
                >
                    <span wire:loading.remove wire:target="confirmarImport">
                        Importar {{ $totalOk }} empleado{{ $totalOk !== 1 ? 's' : '' }}
                    </span>
                    <span wire:loading wire:target="confirmarImport">Importando…</span>
                </flux:button>
            </div>

        @endif

    </div>
</flux:modal>
