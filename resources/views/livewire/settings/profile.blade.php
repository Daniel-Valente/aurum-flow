<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-settings.layout :heading="__('Perfil')" :subheading="__('Valida tu información y solicita corrección si es necesario')">

    </x-settings.layout>
</section>
