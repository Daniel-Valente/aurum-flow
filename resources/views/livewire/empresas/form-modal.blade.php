<flux:modal name="empresa-form" class="w-full max-w-3xl" scroll="body">
    <div class="space-y-6">

        <div>
            <flux:heading size="lg">
                {{ $editingId ? 'Editar empresa' : 'Nueva empresa' }}
            </flux:heading>

            <flux:subheading>
                {{ $editingId
                    ? 'Modifica la información de la empresa.'
                    : 'Completa los datos para registrar una nueva empresa.' }}
            </flux:subheading>
        </div>

        <div class="space-y-5">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                        Información general
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Datos básicos de identificación de la empresa.
                    </p>
                </div>

                <div class="p-4 space-y-5">

                    <flux:field>
                        <flux:label badge="Requerido">Nombre fiscal</flux:label>
                        <flux:input
                            wire:model="nombre"
                            placeholder="Ej. Empresa SA de CV"
                            required
                        />
                        <flux:error name="nombre" />
                    </flux:field>

                    <flux:field>
                        <flux:label badge="Opcional">Nombre comercial</flux:label>
                        <flux:input
                            wire:model="nombre_comercial"
                            placeholder="Ej. Mi Empresa"
                        />
                        <flux:error name="nombre_comercial" />
                    </flux:field>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">RFC</flux:label>
                            <flux:input
                                wire:model="rfc"
                                placeholder="Ej. XAXX010101000"
                                required
                            />
                            <flux:error name="rfc" />
                        </flux:field>
                        <flux:field>
                            <flux:label badge="Requerido">Correo electrónico</flux:label>
                            <flux:input
                                type="email"
                                wire:model="email"
                                placeholder="Ej. contacto@empresa.com"
                                required
                            />
                            <flux:error name="email" />
                        </flux:field>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Opcional">Teléfono</flux:label>
                            <flux:input
                                wire:model="telefono"
                                placeholder="Ej. 4771234567"
                            />
                            <flux:error name="telefono" />
                        </flux:field>
                        <flux:field>
                            <flux:label badge="Opcional">Sitio web</flux:label>
                            <flux:input
                                wire:model="sitio_web"
                                placeholder="https://empresa.com"
                            />
                            <flux:error name="sitio_web" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                        Dirección fiscal
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Información fiscal y ubicación de la empresa.
                    </p>
                </div>
                <div class="p-4 space-y-5">
                    <flux:field>
                        <flux:label badge="Requerido">Domicilio fiscal</flux:label>
                        <flux:textarea
                            wire:model="domicilio_fiscal"
                            rows="3"
                            placeholder="Ej. Calle Principal #123, Col. Centro"
                        />
                        <flux:error name="domicilio_fiscal" />
                    </flux:field>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">Ciudad</flux:label>
                            <flux:input
                                wire:model="ciudad"
                                placeholder="Ej. León"
                                required
                            />
                            <flux:error name="ciudad" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Requerido">Estado</flux:label>
                            <flux:input
                                wire:model="estado"
                                placeholder="Ej. Guanajuato"
                                required
                            />
                            <flux:error name="estado" />
                        </flux:field>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <flux:field>
                            <flux:label badge="Requerido">Código postal</flux:label>
                            <flux:input
                                wire:model="codigo_postal"
                                placeholder="Ej. 37000"
                                required
                            />
                            <flux:error name="codigo_postal" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Requerido">País</flux:label>
                            <flux:input
                                wire:model="pais"
                                placeholder="Ej. México"
                                required
                            />
                            <flux:error name="pais" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">

                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                        Configuración adicional
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Personalización visual y observaciones internas.
                    </p>
                </div>

                <div class="p-4 space-y-5">
                    <flux:field>
                        <flux:label badge="Opcional">Ruta del logo</flux:label>
                        <flux:input
                            wire:model="logo_path"
                            placeholder="/storage/logos/logo.png"
                        />
                        <flux:error name="logo_path" />
                    </flux:field>

                    <flux:field>
                        <flux:label badge="Opcional">Notas</flux:label>
                        <flux:textarea
                            wire:model="notas"
                            rows="4"
                            placeholder="Observaciones internas de la empresa..."
                        />
                        <flux:error name="notas" />
                    </flux:field>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">
                    Cancelar
                </flux:button>
            </flux:modal.close>
            <flux:button
                variant="primary"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">
                    {{ $editingId ? 'Guardar cambios' : 'Crear empresa' }}
                </span>
                <span wire:loading wire:target="save">
                    Guardando…
                </span>
            </flux:button>
        </div>
    </div>
</flux:modal>
