<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\CentroCosto;
use App\Models\Empleado;
use App\Models\FlujoAprobacion;
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
        $rAdmin     = Role::firstOrCreate(['name' => 'admin',     'guard_name' => 'web']);
        $rManager   = Role::firstOrCreate(['name' => 'manager',   'guard_name' => 'web']);
        $rOperativo = Role::firstOrCreate(['name' => 'operativo', 'guard_name' => 'web']);
        $rFinanzas  = Role::firstOrCreate(['name' => 'finanzas',  'guard_name' => 'web']);

        // =====================================================================
        // 3. PERMISOS POR ROL
        // =====================================================================

        $rAdmin->syncPermissions(Permission::all());

        $rManager->syncPermissions([
            'solicitudes.ver.propias', 'solicitudes.ver.area',
            'solicitudes.crear', 'solicitudes.editar',
            'solicitudes.enviar', 'solicitudes.eliminar',
            'solicitudes.aprobar', 'solicitudes.rechazar',

            'gastos.ver.propios', 'gastos.ver.area',
            'gastos.crear', 'gastos.editar', 'gastos.subir.comprobante',

            'comprobantes.validar',

            'excepciones.ver',
            'excepciones.aprobar.nivel1', 'excepciones.rechazar.nivel1',

            'empleados.ver.propios', 'empleados.ver.area',
            // ✅ Sin crear/editar/eliminar empleados

            'proyectos.ver',
            'politicas.ver',
            'conceptos.ver',
            'reportes.ver',
            'auditoria.ver',
        ]);

        $rFinanzas->syncPermissions([
            'solicitudes.ver.todas',
            'solicitudes.ver.propias',
            'solicitudes.crear', 'solicitudes.editar',
            'solicitudes.enviar', 'solicitudes.eliminar',
            'solicitudes.aprobar', 'solicitudes.rechazar',

            'gastos.ver.propios',
            'gastos.ver.todos', 'gastos.validar',
            'gastos.crear', 'gastos.editar', 'gastos.subir.comprobante',

            'comprobantes.validar',

            'excepciones.ver',
            'excepciones.aprobar.nivel2', 'excepciones.rechazar.nivel2',

            'reportes.ver', 'reportes.exportar',
            'auditoria.ver',

            'politicas.ver',
        ]);

        $rOperativo->syncPermissions([
            'solicitudes.ver.propias',
            'solicitudes.crear', 'solicitudes.editar',
            'solicitudes.enviar', 'solicitudes.eliminar',

            'gastos.ver.propios',
            'gastos.crear', 'gastos.editar', 'gastos.subir.comprobante',
        ]);

        // =====================================================================
        // 4. USUARIOS BASE
        // =====================================================================

        $uAdmin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name'                 => 'Admin Aurum',
                'password'             => Hash::make('password'),
                'must_change_password' => true,
                'blocked'              => false,
            ]
        );

        $uManager = User::firstOrCreate(
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

        $uFinanzas = User::firstOrCreate(
            ['email' => 'finanzas@demo.com'],
            [
                'name'                 => 'María González Ruiz',
                'password'             => Hash::make('password'),
                'must_change_password' => true,
                'blocked'              => false,
            ]
        );

        $uAdmin->syncRoles(['admin']);
        $uManager->syncRoles(['manager']);
        $uOperativo->syncRoles(['operativo']);
        $uFinanzas->syncRoles(['finanzas']);

        // =====================================================================
        // 5. EMPLEADOS
        // =====================================================================
        $area        = Area::where('codigo', 'RRHH')->first();
        $centroCosto = CentroCosto::where('codigo', 'CC-001')->first();

        $empleados = [
            [$uAdmin,    'Admin Aurum',              'Administrador del Sistema', 'NOM-0001', 'XAXX010101000',  'XAXX010101HXXXXX00'],
            [$uManager,  'David De Santiago García', 'Gerente de Ventas',        'NOM-0050', 'SADD01000KSJ',   'SADD010101HXXXXX00'],
            [$uOperativo,'Juan Pérez López',         'Analista de Operaciones',  'NOM-0100', 'PELJ900101AB1',   'PELJ900101HXXXXX00'],
            [$uFinanzas, 'María González Ruiz',      'Analista de Finanzas',     'NOM-0150', 'GOMR900201AB2',   'GOMR900201MXXXXX00'],
        ];

        foreach ($empleados as [$user, $nombre, $puesto, $nomina, $rfc, $curp]) {
            Empleado::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nombre_completo'  => $nombre,
                    'puesto'           => $puesto,
                    'area_id'          => $area?->id,
                    'centro_costo_id'  => $centroCosto?->id,
                    'rfc'              => $rfc,
                    'curp'             => $curp,
                    'numero_nomina'    => $nomina,
                    'banco_nomina'     => 'BBVA',
                    'cuenta_nomina'    => '1234567890',
                    'clabe_nomina'     => '012180001234567890',
                    'nss'              => '12345678901',
                    'fecha_ingreso'    => now()->subYear(),
                    'estatus'          => true,
                ]
            );
        }

        // =====================================================================
        // 6. FLUJO DE APROBACIÓN — 2 de 3 (admin, manager, finanzas)
        // =====================================================================
        // minimo_aprobaciones vive en la primera fila del tipo (se aplica a todas)
        $flujos = [
            ['role' => 'admin',    'orden' => 1, 'minimo' => 2],
            ['role' => 'manager',  'orden' => 2, 'minimo' => 2],
            ['role' => 'finanzas', 'orden' => 3, 'minimo' => 2],
        ];

        foreach ($flujos as $f) {
            $role = Role::findByName($f['role']);
            FlujoAprobacion::firstOrCreate(
                ['tipo_solicitud' => 'viaticos', 'role_id' => $role->id],
                [
                    'orden'               => $f['orden'],
                    'minimo_aprobaciones' => $f['minimo'],
                    'estatus'             => true,
                ]
            );
        }
    }
}
