<x-layouts::app.sidebar :title="$title ?? null">

    <flux:main>
        {{ $slot }}
    </flux:main>

    @if (!empty($forcePasswordChange))
        <livewire:force-change-password />
    @endif

</x-layouts::app.sidebar>
