<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [

            // ========================
            // SOLICITUDES
            // ========================
            'solicitudes.ver.propias',
            'solicitudes.ver.todas',
            'solicitudes.crear',
            'solicitudes.editar',
            'solicitudes.eliminar',
            'solicitudes.enviar',
            'solicitudes.aprobar',
            'solicitudes.rechazar',

            // ========================
            // GASTOS
            // ========================
            'gastos.ver.propios',
            'gastos.ver.todos',
            'gastos.crear',
            'gastos.editar',
            'gastos.eliminar',
            'gastos.validar',
            'gastos.subir.comprobante',

            // ========================
            // EXCEPCIONES
            // ========================
            'excepciones.ver',
            'excepciones.aprobar.nivel1',
            'excepciones.aprobar.nivel2',
            'excepciones.rechazar',

            // ========================
            // AUDITORIA
            // ========================
            'auditoria.ver',
            'auditoria.revisar',

            // ========================
            // EMPLEADOS
            // ========================
            'empleados.ver',
            'empleados.crear',
            'empleados.editar',
            'empleados.eliminar',

            // ========================
            // Areas
            // ========================
            'areas.ver',
            'areas.crear',
            'areas.editar',
            'areas.eliminar',

            // ========================
            // Centro Costo
            // ========================
            'centros_costos.ver',
            'centros_costos.crear',
            'centros_costos.editar',
            'centros_costos.eliminar',

            // ========================
            // PROYECTOS
            // ========================
            'proyectos.ver',
            'proyectos.crear',
            'proyectos.editar',
            'proyectos.eliminar',

            // ========================
            // CONCEPTOS
            // ========================
            'conceptos.ver',
            'conceptos.crear',
            'conceptos.editar',
            'conceptos.eliminar',

            // ========================
            // POLITICAS
            // ========================
            'politicas.ver',
            'politicas.crear',
            'politicas.editar',
            'politicas.eliminar',

            // ========================
            // REPORTES
            // ========================
            'reportes.ver',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web',
            ]);
        }
    }
}
