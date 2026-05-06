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

        // Permisos — separados por dominio
        $permisos = [
            // Solicitudes
            'solicitudes.ver.propias',
            'solicitudes.ver.area',
            'solicitudes.ver.todas',
            'solicitudes.crear',
            'solicitudes.editar',
            'solicitudes.eliminar',
            'solicitudes.enviar',
            'solicitudes.aprobar',
            'solicitudes.rechazar',

            // Gastos
            'gastos.ver.propios',
            'gastos.ver.area',
            'gastos.ver.todos',
            'gastos.crear',
            'gastos.editar',
            'gastos.eliminar',
            'gastos.subir.comprobante',
            'gastos.validar',

            // Comprobantes
            'comprobantes.validar',

            // Excepciones
            'excepciones.ver',
            'excepciones.aprobar.nivel1',
            'excepciones.aprobar.nivel2',
            'excepciones.rechazar.nivel1',
            'excepciones.rechazar.nivel2',

            // Auditoría
            'auditoria.ver',
            'auditoria.revisar',

            // Empleados
            'empleados.ver.propios',
            'empleados.ver.area',
            'empleados.ver.todos',
            'empleados.crear',
            'empleados.editar',
            'empleados.eliminar',

            // Áreas
            'areas.ver',
            'areas.crear',
            'areas.editar',
            'areas.eliminar',

            // Centros de costo
            'centros_costos.ver',
            'centros_costos.crear',
            'centros_costos.editar',
            'centros_costos.eliminar',

            // Proyectos
            'proyectos.ver',
            'proyectos.crear',
            'proyectos.editar',
            'proyectos.eliminar',

            // Conceptos
            'conceptos.ver',
            'conceptos.crear',
            'conceptos.editar',
            'conceptos.eliminar',

            // Políticas
            'politicas.ver',
            'politicas.crear',
            'politicas.editar',
            'politicas.eliminar',

            // Reportes
            'reportes.ver',
            'reportes.exportar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web',
            ]);
        }
    }
}
