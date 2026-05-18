<flux:modal name="transferencia-modal" class="w-full max-w-md">
    <div class="space-y-6">

        <div>
            <flux:heading size="lg">Transferir presupuesto</flux:heading>
            <flux:subheading>
                Transfiere monto de un presupuesto a otro. La transferencia requiere aprobación.
            </flux:subheading>
        </div>

        <div class="space-y-5">

            <flux:field>
                <flux:label badge="Requerido">Presupuesto destino</flux:label>
                <flux:select variant="listbox" wire:model="destinoId" searchable required>
                    <flux:select.option value="">Seleccionar...</flux:select.option>
                    @foreach($presupuestosDisponibles as $p)
                        <flux:select.option value="{{ $p['id'] }}">
                            {{ $p['label'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="destinoId" />
            </flux:field>

            <flux:field>
                <flux:label badge="Requerido">Monto a transferir ($)</flux:label>
                <flux:input
                    type="number"
                    step="0.01"
                    min="0.01"
                    wire:model="monto"
                    placeholder="Ej. 5000.00"
                    required
                />
                <flux:description class="text-xs">
                    El monto se descontará del presupuesto origen y se sumará al destino.
                </flux:description>
                <flux:error name="monto" />
            </flux:field>

            <flux:field>
                <flux:label badge="Requerido">Motivo de la transferencia</flux:label>
                <flux:textarea
                    wire:model="motivo"
                    placeholder="Explica el motivo de la transferencia..."
                    rows="3"
                    required
                />
                <flux:error name="motivo" />
            </flux:field>

            @error('general')
                <div class="rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 p-4">
                    <div class="flex items-start gap-3">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 shrink-0 mt-0.5" />
                        <p class="text-sm text-red-800 dark:text-red-200">{{ $message }}</p>
                    </div>
                </div>
            @enderror

            <div class="rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 p-4">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="size-5 text-blue-600 shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-medium">Importante:</p>
                        <ul class="list-disc list-inside mt-2 space-y-1 text-xs">
                            <li>La transferencia debe ser aprobada por un administrador.</li>
                            <li>El presupuesto origen debe tener saldo suficiente disponible.</li>
                            <li>Ambos presupuestos deben estar en estado activo.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        {{-- Acciones --}}
        <div class="flex justify-end gap-3">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>

            <flux:button
                variant="primary"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">Solicitar transferencia</span>
                <span wire:loading wire:target="save">Solicitando...</span>
            </flux:button>
        </div>

    </div>
</flux:modal>
