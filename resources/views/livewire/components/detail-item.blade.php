@props(['icon', 'label'])

<div class="flex items-start gap-3 rounded-lg px-3 py-2.5">
    <flux:icon :name="$icon" class="mt-0.5 size-4 shrink-0 text-zinc-400 dark:text-zinc-500" />
    <div class="min-w-0 flex-1">
        <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $label }}</p>
        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-200">
            {{ $slot }}
        </p>
    </div>
</div>
