<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\CentroCosto;
use App\Models\Empleado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SystemInitSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================
        // ROLES
        // ========================
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'gerente', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'operativo', 'guard_name' => 'web']);

        // ========================
        // USUARIOS BASE
        // ========================
        $uAdmin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name'                 => 'Admin Aurum',
                'password'             => Hash::make('password'),
                'must_change_password' => true,
                'blocked'              => false,
            ]
        );

        $uGerente = User::firstOrCreate(
            ['email' => 'gerente@demo.com'],
            [
                'name'                 => 'David De Santiago García',
                'password'             => Hash::make('password'),
                'must_change_password' => true,
                'blocked'              => false,
            ]
        );

        $uOperativo = User::firstOrCreate(
            ['email' => 'operativo@demo.com'],
            [
                'name'                 => 'Juan Pérez López',
                'password'             => Hash::make('password'),
                'must_change_password' => true,
                'blocked'              => false,
            ]
        );

        // ========================
        // ASIGNAR ROLES
        // ========================
        $uAdmin->syncRoles(['admin']);
        $uGerente->syncRoles(['gerente']);
        $uOperativo->syncRoles(['operativo']);

        // ========================
        // EMPLEADOS
        // ========================
        $area = Area::where('codigo', 'RRHH')->first();
        $centroCosto = CentroCosto::where('codigo', 'CC-001')->first();

        Empleado::firstOrCreate(
            ['user_id' => $uAdmin->id],
            [
                'nombre_completo'  => 'Admin Aurum',
                'puesto'           => 'Administrador del Sistema',
                'area_id'          => $area->id,
                'centro_costo_id'  => $centroCosto->id,
                'rfc'              => 'XAXX010101000',
                'curp'             => 'XAXX010101HXXXXX00',
                'numero_nomina'    => 'NOM-0001',
                'banco_nomina'     => 'BBVA',
                'cuenta_nomina'    => '1234567890',
                'clabe_nomina'     => '012180001234567890',
                'nss'              => '12345678901',
                'fecha_ingreso'    => '2020-01-15',
                'telefono'         => '3312345678',
                'estatus'          => true,
            ]
        );

        Empleado::firstOrCreate(
            ['user_id' => $uGerente->id],
            [
                'nombre_completo'  => 'David De Santiago García',
                'puesto'           => 'Gerente de Ventas',
                'area_id'          => $area->id,
                'centro_costo_id'  => $centroCosto->id,
                'rfc'              => 'SADD01000KSJ',
                'curp'             => 'SADD010101HXXXXX00',
                'numero_nomina'    => 'NOM-0050',
                'banco_nomina'     => 'Santander',
                'cuenta_nomina'    => '9876543210',
                'clabe_nomina'     => '014180009876543210',
                'nss'              => '98765432101',
                'fecha_ingreso'    => '2021-03-01',
                'telefono'         => '3398765432',
                'estatus'          => true,
            ]
        );

        Empleado::firstOrCreate(
            ['user_id' => $uOperativo->id],
            [
                'nombre_completo'  => 'Juan Pérez López',
                'puesto'           => 'Analista de Operaciones',
                'area_id'          => $area->id,
                'centro_costo_id'  => $centroCosto->id,
                'rfc'              => 'PELJ900101AB1',
                'curp'             => 'PELJ900101HXXXXX00',
                'numero_nomina'    => 'NOM-0100',
                'banco_nomina'     => 'BBVA',
                'cuenta_nomina'    => '1122334455',
                'clabe_nomina'     => '012180001122334455',
                'nss'              => '11223344551',
                'fecha_ingreso'    => '2025-01-15',
                'telefono'         => '3311223344',
                'estatus'          => true,
            ]
        );

        // ========================
        // ASIGNAR ROLES
        // ========================
        $uAdmin->syncRoles(['admin']);
        $uGerente->syncRoles(['gerente']);
        $uOperativo->syncRoles(['operativo']);

        // 4. OBTENER ROLES
        $admin = Role::findByName('admin');
        $gerente = Role::findByName('gerente');
        $operativo = Role::findByName('operativo');

        // 5. ASIGNAR PERMISOS

        // ADMIN → todo
        $admin->syncPermissions(Permission::all());

        // GERENTE
        $gerente->syncPermissions([
            'solicitudes.ver.propias',
            'solicitudes.crear',
            'solicitudes.editar',
            'solicitudes.enviar',

            'gastos.ver.propios',
            'gastos.crear',
            'gastos.editar',
            'gastos.subir.comprobante',

            'solicitudes.ver.todas',
            'solicitudes.aprobar',
            'solicitudes.rechazar',

            'gastos.ver.todos',

            'excepciones.ver',
            'excepciones.aprobar.nivel1',
            'excepciones.rechazar.nivel1',

            'auditoria.ver',

            'empleados.ver',
            'empleados.ver.propios',
            'empleados.ver.area',
            'empleados.crear',
            'empleados.editar',
            'empleados.eliminar',
            'proyectos.ver',

            'reportes.ver',
        ]);

        // OPERATIVO
        $operativo->syncPermissions([
            'solicitudes.ver.propias',
            'solicitudes.crear',
            'solicitudes.editar',
            'solicitudes.enviar',

            'gastos.ver.propios',
            'gastos.crear',
            'gastos.editar',
            'gastos.subir.comprobante',
        ]);
    }
}
