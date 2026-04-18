<?php

namespace Database\Seeders;

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
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );

        $uGerente = User::firstOrCreate(
            ['email' => 'gerente@demo.com'],
            ['name' => 'Gerente', 'password' => Hash::make('password')]
        );

        $uOperativo = User::firstOrCreate(
            ['email' => 'operativo@demo.com'],
            ['name' => 'Operativo', 'password' => Hash::make('password')]
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
            'solicitudes.ver.todas',
            'solicitudes.aprobar',
            'solicitudes.rechazar',

            'gastos.ver.todos',

            'excepciones.ver',
            'excepciones.aprobar.nivel1',
            'excepciones.rechazar.nivel1',

            'auditoria.ver',

            'empleados.ver',
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
