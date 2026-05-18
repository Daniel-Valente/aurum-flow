<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>

                <flux:sidebar.group heading="General">
                    <flux:sidebar.item icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        wire:navigate>
                        Dashboard
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @can('solicitudes.ver.propias')
                    <flux:sidebar.group heading="Operación">
                        @can('solicitudes.aprobar')
                            <flux:sidebar.item icon="document-check"
                                :href="route('autorizaciones.index')"
                                :current="request()->routeIs('autorizaciones.index')"
                                wire:navigate>
                                Autorizaciones
                            </flux:sidebar.item>
                        @endcan

                        @can('auditoria.ver')
                            <flux:sidebar.item icon="book-open"
                                :href="route('auditoria.index')"
                                :current="request()->routeIs('auditoria.index')"
                                wire:navigate>
                                Auditoría
                            </flux:sidebar.item>
                        @endcan

                        <flux:sidebar.item icon="currency-dollar"
                            :href="route('gastos.index')"
                            :current="request()->routeIs('gastos.index')"
                            wire:navigate>
                            Comprobar gastos
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="document-chart-bar"
                            :href="route('solicitudes.index')"
                            :current="request()->routeIs('solicitudes.index')"
                            wire:navigate>
                            Mis solicitudes
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endcan

                @canany(['empleados.ver.propios', 'empleados.ver.area', 'empleados.ver.todos'])
                    <flux:sidebar.group heading="Administración">
                        @can('areas.ver')
                            <flux:sidebar.item icon="globe-alt"
                                :href="route('areas')"
                                :current="request()->routeIs('areas')"
                                wire:navigate>
                                Áreas
                            </flux:sidebar.item>
                        @endcan

                        @can('conceptos.ver')
                            <flux:sidebar.item icon="clipboard-document-list"
                                :href="route('conceptos')"
                                :current="request()->routeIs('conceptos')"
                                wire:navigate>
                                Conceptos
                            </flux:sidebar.item>
                        @endcan

                        @can('empresas.ver')
                            <flux:sidebar.item icon="user-group"
                                :href="route('empresas.index')"
                                :current="request()->routeIs('empresas.index')"
                                wire:navigate>
                                Empresas
                            </flux:sidebar.item>
                        @endcan

                        <flux:sidebar.item icon="user"
                            :href="route('empleados')"
                            :current="request()->routeIs('empleados')"
                            wire:navigate>
                            Empleados
                        </flux:sidebar.item>

                        @can('politicas.ver')
                            <flux:sidebar.item icon="shield-exclamation"
                                :href="route('politicas')"
                                :current="request()->routeIs('politicas')"
                                wire:navigate>
                                Políticas
                            </flux:sidebar.item>
                        @endcan

                        @can('proyectos.ver')
                            <flux:sidebar.item icon="briefcase"
                                :href="route('proyectos')"
                                :current="request()->routeIs('proyectos')"
                                wire:navigate>
                                Proyectos
                            </flux:sidebar.item>
                        @endcan

                        @can('referencia_contable.ver')
                            <flux:sidebar.item icon="building-office"
                                :href="route('centros-costos')"
                                :current="request()->routeIs('centros-costos')"
                                wire:navigate>
                                Referencia contable
                            </flux:sidebar.item>
                        @endcan

                        @can('roles.ver')
                            <flux:sidebar.item
                                icon="shield-check"
                                :href="route('roles-permisos')"
                                :current="request()->routeIs('roles-permisos')"
                                wire:navigate
                                >
                                Roles y Permisos
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endcan

                @canany(['reportes.ver', 'presupuestos.ver'])
                    <flux:sidebar.group heading="Finanzas">
                        @can('presupuestos.ver')
                            <flux:sidebar.item icon="banknotes"
                                :href="route('presupuestos.index')"
                                :current="request()->routeIs('presupuestos.index')"
                                wire:navigate>
                                Presupuestos
                            </flux:sidebar.item>
                        @endcan

                        <flux:sidebar.item icon="chart-pie"
                            :href="route('reportes.index')"
                            :current="request()->routeIs('reportes.index')"
                            wire:navigate>
                            Reportes
                        </flux:sidebar.item>

                    </flux:sidebar.group>
                @endcan
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Ajustes') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Cerrar Sesión') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
