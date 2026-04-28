<flux:modal name="proyecto-detail" flyout variant="floating" class="md:w-lg">
    @if ($proyecto)
    <div class="space-y-6">
        <div class="flex items-start gap-4">
            <div class="flex size-14 items-center justify-center rounded-full">
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar :name="$proyecto->nombre" :initials="strtoupper(substr($proyecto->nombre, 0, 1))" />
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate">
                                {{ $proyecto->nombre }}
                            </flux:heading>
                            <flux:text class="font-mono text-sm">
                                {{ $proyecto->codigo }}
                            </flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</flux:modal>
