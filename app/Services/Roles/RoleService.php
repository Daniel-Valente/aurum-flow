<?php

namespace App\Services\Roles;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleService
{
    private const ROLES_SISTEMA = ['admin', 'manager', 'finanzas', 'operativo'];

    private const DOMINIOS = [
        'ver-stats'           => 'Dashboard',
        'solicitudes'         => 'Solicitudes',
        'gastos'              => 'Gastos',
        'comprobantes'        => 'Comprobantes',
        'excepciones'         => 'Excepciones',
        'auditoria'           => 'Auditoría',
        'empleados'           => 'Empleados',
        'areas'               => 'Áreas',
        'empresas'            => 'Empresas',
        'presupuestos'        => 'Presupuestos',
        'referencia_contable' => 'Referencia Contable',
        'proyectos'           => 'Proyectos',
        'conceptos'           => 'Conceptos',
        'politicas'           => 'Políticas',
        'reportes'            => 'Reportes',
        'roles'               => 'Roles'
    ];

    private const ETIQUETAS = [
        'ver-stats-operativo'         => 'Ver stats operativo',
        'ver-stats-manager'           => 'Ver stats manager',
        'ver-stats-finanzas'          => 'Ver stats finanzas',
        'ver-stats-admin'             => 'Ver stats admin',
        'solicitudes.ver.propias'     => 'Ver propias',
        'solicitudes.ver.area'        => 'Ver del área',
        'solicitudes.ver.todas'       => 'Ver todas',
        'solicitudes.crear'           => 'Crear',
        'solicitudes.editar'          => 'Editar',
        'solicitudes.eliminar'        => 'Eliminar',
        'solicitudes.enviar'          => 'Enviar',
        'solicitudes.aprobar'         => 'Aprobar',
        'solicitudes.rechazar'        => 'Rechazar',
        'gastos.ver.propios'          => 'Ver propios',
        'gastos.ver.area'             => 'Ver del área',
        'gastos.ver.todos'            => 'Ver todos',
        'gastos.crear'                => 'Crear',
        'gastos.editar'               => 'Editar',
        'gastos.eliminar'             => 'Eliminar',
        'gastos.subir.comprobante'    => 'Subir comprobante',
        'gastos.validar'              => 'Validar',
        'gastos.tarjeta.conciliar'    => 'Conciliar tarjeta',
        'gastos.tarjeta.crear'        => 'Crear tarjeta',
        'gastos.tarjeta.ver.propios'  => 'Ver tarjeta propios',
        'comprobantes.validar'        => 'Validar comprobantes',
        'excepciones.ver'             => 'Ver excepciones',
        'excepciones.aprobar.nivel1'  => 'Aprobar nivel 1',
        'excepciones.aprobar.nivel2'  => 'Aprobar nivel 2',
        'excepciones.rechazar.nivel1' => 'Rechazar nivel 1',
        'excepciones.rechazar.nivel2' => 'Rechazar nivel 2',
        'auditoria.ver'               => 'Ver auditoría',
        'auditoria.revisar'           => 'Revisar auditoría',
        'empleados.ver.propios'       => 'Ver propios',
        'empleados.ver.area'          => 'Ver del área',
        'empleados.ver.todos'         => 'Ver todos',
        'empleados.crear'             => 'Crear',
        'empleados.editar'            => 'Editar',
        'empleados.eliminar'          => 'Eliminar',
        'areas.ver'                   => 'Ver áreas',
        'areas.crear'                 => 'Crear',
        'areas.editar'                => 'Editar',
        'areas.eliminar'              => 'Eliminar',
        'empresas.ver'                => 'Ver empresas',
        'empresas.crear'              => 'Crear',
        'empresas.editar'             => 'Editar',
        'empresas.configurar'         => 'Configurar',
        'empresas.eliminar'           => 'Eliminar',
        'presupuestos.ver'            => 'Ver presupuestos',
        'presupuestos.crear'          => 'Crear',
        'presupuestos.editar'         => 'Editar',
        'presupuestos.eliminar'       => 'Eliminar',
        'presupuestos.aprobar'        => 'Aprobar',
        'presupuestos.cancelar'       => 'Cancelar',
        'presupuestos.ajustar'        => 'Ajustar',
        'presupuestos.transferir'     => 'Transferir',
        'referencia_contable.ver'     => 'Ver referencias contables',
        'referencia_contable.crear'   => 'Crear',
        'referencia_contable.editar'  => 'Editar',
        'referencia_contable.eliminar'=> 'Eliminar',
        'proyectos.ver'               => 'Ver proyectos',
        'proyectos.crear'             => 'Crear',
        'proyectos.editar'            => 'Editar',
        'proyectos.eliminar'          => 'Eliminar',
        'conceptos.ver'               => 'Ver conceptos',
        'conceptos.crear'             => 'Crear',
        'conceptos.editar'            => 'Editar',
        'conceptos.eliminar'          => 'Eliminar',
        'politicas.ver'               => 'Ver políticas',
        'politicas.crear'             => 'Crear',
        'politicas.editar'            => 'Editar',
        'politicas.eliminar'          => 'Eliminar',
        'reportes.ver'                => 'Ver reportes',
        'reportes.exportar'           => 'Exportar',
        'roles.ver'                   => 'Ver roles',
        'roles.crear'                 => 'Crear',
        'roles.editar'                => 'Editar',
        'roles.eliminar'              => 'Eliminar',
        'roles.permisos'              => 'Permisos'
    ];

    public function todos(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::with('permissions')
            ->withCount('permissions', 'users')
            ->orderBy('name')
            ->get();
    }

    public function permisosAgrupados(): array
    {
        $permisos = Permission::orderBy('name')->get();
        $grupos   = [];

        foreach ($permisos as $permiso) {
            $dominio = $this->resolverDominio($permiso->name);
            $grupos[$dominio][] = [
                'name'  => $permiso->name,
                'label' => self::ETIQUETAS[$permiso->name] ?? $permiso->name,
            ];
        }

        $ordenado = [];
        foreach (self::DOMINIOS as $etiqueta) {
            if (isset($grupos[$etiqueta])) {
                $ordenado[$etiqueta] = $grupos[$etiqueta];
            }
        }

        foreach ($grupos as $dominio => $items) {
            if (!isset($ordenado[$dominio])) {
                $ordenado[$dominio] = $items;
            }
        }

        return $ordenado;
    }

    public function crear(array $data, $user): Role
    {
        $this->validarNombreUnico($data['name']);

        return DB::transaction(function () use ($data) {
            return Role::create([
                'name'       => strtolower(trim($data['name'])),
                'guard_name' => 'web',
            ]);
        });
    }

    public function actualizar(Role $role, array $data, $user): Role
    {
        if ($this->esSistema($role) && isset($data['name'])) {
            throw new \Exception("El rol '{$role->name}' es de sistema y no se puede renombrar.");
        }

        if (isset($data['name']) && strtolower(trim($data['name'])) !== $role->name) {
            $this->validarNombreUnico($data['name'], $role->id);
        }

        return DB::transaction(function () use ($role, $data) {
            if (isset($data['name'])) {
                $role->update(['name' => strtolower(trim($data['name']))]);
            }

            return $role->fresh();
        });
    }

    public function sincronizarPermisos(Role $role, array $permisos, $user): Role
    {
        DB::transaction(function () use ($role, $permisos) {
            $role->syncPermissions($permisos);
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        });

        return $role->fresh(['permissions']);
    }

    public function eliminar(Role $role, $user): bool
    {
        if ($this->esSistema($role)) {
            throw new \Exception("El rol '{$role->name}' es de sistema y no se puede eliminar.");
        }

        if ($role->users()->count() > 0) {
            throw new \Exception("No se puede eliminar el rol '{$role->name}' porque tiene usuarios asignados.");
        }

        return DB::transaction(function () use ($role) {
            $role->syncPermissions([]);
            $role->delete();
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return true;
        });
    }

    public function esSistema(Role $role): bool
    {
        return in_array($role->name, self::ROLES_SISTEMA, true);
    }

    public function etiquetaPermiso(string $name): string
    {
        return self::ETIQUETAS[$name] ?? $name;
    }

    public function dominios(): array
    {
        return self::DOMINIOS;
    }

    private function resolverDominio(string $permiso): string
    {
        foreach (self::DOMINIOS as $prefijo => $etiqueta) {
            if (str_starts_with($permiso, $prefijo)) {
                return $etiqueta;
            }
        }

        $segmento = explode('.', $permiso)[0];

        return ucfirst($segmento);
    }

    private function validarNombreUnico(string $name, ?int $exceptId = null): void
    {
        $existe = Role::where('name', strtolower(trim($name)))
            ->where('guard_name', 'web')
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->exists();

        if ($existe) {
            throw new \Exception("Ya existe un rol con el nombre '{$name}'.");
        }
    }
}
