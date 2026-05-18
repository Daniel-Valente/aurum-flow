<flux:modal name="empresa-detail" flyout variant="floating" class="md:w-4xl">
    @if ($empresa)
        <div class="flex flex-col gap-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex size-14 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="building-office-2" class="size-7 text-zinc-600 dark:text-zinc-300" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <flux:heading size="lg" class="leading-tight">
                            {{ $empresa->nombre }}
                        </flux:heading>

                        <div class="flex items-center gap-2 text-xs text-zinc-400">
                            <span class="font-mono">{{ $empresa->codigo }}</span>
                            <span>•</span>
                            <span class="font-mono">{{ $empresa->rfc }}</span>
                        </div>
                    </div>
                </div>

                @if ($empresa->activo)
                    <flux:badge color="green" size="sm">
                        Activa
                    </flux:badge>
                @else
                    <flux:badge color="red" size="sm">
                        Inactiva
                    </flux:badge>
                @endif
            </div>

            <div>
                <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                    Información general
                </flux:subheading>

                <div class="grid grid-cols-2 gap-3">

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Nombre comercial</span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->nombre_comercial ?? '—' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Email</span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->email ?? '—' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Teléfono</span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->telefono ?? '—' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Sitio web</span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200 break-all">
                            {{ $empresa->sitio_web ?? '—' }}
                        </span>
                    </div>

                </div>
            </div>

            <flux:separator />

            <div>
                <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                    Dirección fiscal
                </flux:subheading>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2 flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Domicilio</span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->domicilio_fiscal ?? '—' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Ciudad</span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->ciudad ?? '—' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Estado</span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->estado ?? '—' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Código postal</span>
                        <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->codigo_postal ?? '—' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">País</span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->pais ?? '—' }}
                        </span>
                    </div>

                </div>
            </div>

            <div>
                <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                    Configuración regional
                </flux:subheading>

                <div class="grid grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Moneda</span>
                        <span class="text-sm font-mono text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->moneda }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        <span class="text-[10px] uppercase text-zinc-400">Timezone</span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->timezone }}
                        </span>
                    </div>

                    <div class="flex flex-col justify-center rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-2.5">
                        @if ($empresa->activo)
                            <flux:badge color="green" size="sm">
                                Empresa activa
                            </flux:badge>
                        @else
                            <flux:badge color="red" size="sm">
                                Empresa inactiva
                            </flux:badge>
                        @endif
                    </div>

                </div>
            </div>

            @if ($configuracion)
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <flux:subheading class="text-xs uppercase tracking-widest text-zinc-400">
                            Configuración de validación
                        </flux:subheading>

                        @if ($configuracion['tieneConfiguracionPropia'])
                            <flux:badge color="green" size="sm">
                                Personalizada
                            </flux:badge>
                        @else
                            <flux:badge color="amber" size="sm">
                                Global
                            </flux:badge>
                        @endif
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] uppercase text-zinc-400">
                                    Días hábiles
                                </span>

                                @unless($configuracion['dias_habiles_comprobacion']['esGlobal'])
                                    <flux:badge color="blue" size="sm">
                                        Custom
                                    </flux:badge>
                                @endunless
                            </div>

                            <div class="mt-2 text-2xl font-bold text-zinc-800 dark:text-zinc-100">
                                {{ $configuracion['dias_habiles_comprobacion']['valor'] }}
                            </div>
                        </div>

                        <div class="rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] uppercase text-zinc-400">
                                    Días antes
                                </span>
                                @unless($configuracion['cfdi_dias_antes_permitidos']['esGlobal'])
                                    <flux:badge color="blue" size="sm">
                                        Custom
                                    </flux:badge>
                                @endunless
                            </div>
                            <div class="mt-2 text-2xl font-bold text-zinc-800 dark:text-zinc-100">
                                {{ $configuracion['cfdi_dias_antes_permitidos']['valor'] }}
                            </div>
                        </div>

                        <div class="rounded-lg bg-zinc-50 dark:bg-zinc-900 px-3 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] uppercase text-zinc-400">
                                    Días después
                                </span>
                                @unless($configuracion['cfdi_dias_despues_permitidos']['esGlobal'])
                                    <flux:badge color="blue" size="sm">
                                        Custom
                                    </flux:badge>
                                @endunless
                            </div>

                            <div class="mt-2 text-2xl font-bold text-zinc-800 dark:text-zinc-100">
                                {{ $configuracion['cfdi_dias_despues_permitidos']['valor'] }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-xs uppercase text-zinc-400">
                                Auto-aprobaciones
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="flex items-center gap-2">
                                @if ($configuracion['propina_auto_aprueba']['valor'])
                                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                                    <span class="text-sm">Propinas</span>
                                @else
                                    <flux:icon name="x-circle" class="size-5 text-red-500" />
                                    <span class="text-sm text-red-500">Sin propinas</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($configuracion['propina_auto_aprueba']['valor'])
                                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                                    <span class="text-sm">Compartidos</span>
                                @else
                                    <flux:icon name="x-circle" class="size-5 text-red-500" />
                                    <span class="text-sm text-red-500">Sin compartidos</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($configuracion['validar_rfc_receptor']['valor'])
                                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                                    <span class="text-sm">RFC validado</span>
                                @else
                                    <flux:icon name="x-circle" class="size-5 text-red-500" />
                                    <span class="text-sm text-red-500">Sin validación RFC</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($empresa->notas)
                <div>
                    <flux:subheading class="mb-3 text-xs uppercase tracking-widest text-zinc-400">
                        Notas
                    </flux:subheading>

                    <div class="rounded-lg bg-zinc-50 dark:bg-zinc-900 px-4 py-3">
                        <p class="text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-200">
                            {{ $empresa->notas }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    @endif
</flux:modal>
