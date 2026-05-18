<div class="space-y-6">
    <div>
        <flux:heading size="xl">Panel de Control</flux:heading>
        <flux:subheading>Monitoreo operativo y financiero en tiempo real.</flux:subheading>
    </div>

    <flux:tab.group>

    <flux:tabs class="w-full">
        @can('ver-stats-operativo')
            <flux:tab name="tab_operativo" icon="briefcase">Operaciones</flux:tab>
        @endcan

        @can('ver-stats-manager')
            <flux:tab name="tab_manager" icon="user-group">Mi Área</flux:tab>
        @endcan

        @can('ver-stats-finanzas')
            <flux:tab name="tab_finanzas" icon="chart-pie">Finanzas & Fiscal</flux:tab>
        @endcan

        @can('ver-stats-admin')
            <flux:tab name="tab_admin" icon="shield-check">Administración Global</flux:tab>
        @endcan
    </flux:tabs>

    @can('ver-stats-operativo')
        <flux:tab.panel name="tab_operativo">
            <div class="mt-4">
                @livewire('dashboard.operativo-stats')
            </div>
        </flux:tab.panel>
    @endcan

    @can('ver-stats-manager')
        <flux:tab.panel name="tab_manager">
            <div class="mt-4">
                @livewire('dashboard.manager-stats')
            </div>
        </flux:tab.panel>
    @endcan

    @can('ver-stats-finanzas')
        <flux:tab.panel name="tab_finanzas">
            <div class="mt-4">
                @livewire('dashboard.finanzas-stats')
            </div>
        </flux:tab.panel>
    @endcan

    @can('ver-stats-admin')
        <flux:tab.panel name="tab_admin">
            <div class="mt-4">
                @livewire('dashboard.admin-stats')
            </div>
        </flux:tab.panel>
    @endcan
    </flux:tab.group>
</div>
