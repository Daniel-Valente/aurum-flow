@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Aurum Flow" {{ $attributes }}>
        <x-slot name="logo" class="flex size-8 p-0.5 aspect-square items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="w-full h-full fill-current text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Aurum Flow" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 p-0.5 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="w-full h-full fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
