<?php

namespace App\Services\Empleado;

use App\Models\Empleado;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EmpleadoService
{
    private const ALLOWED_SORT_COLUMNS = [
        'nombre_completo', 'numero_nomina', 'puesto', 'created_at', 'estatus',
    ];
    private const ALLOWED_SORT_DIRS = ['asc', 'desc'];

    public function paginate(
        string  $search        = '',
        string  $estatus       = '',
        ?int    $areaId        = null,
        ?int    $centroCostoId = null,
        ?string $rol           = null,
        string  $sortBy        = 'created_at',
        string  $sortDir       = 'desc',
        int     $perPage       = 15,
    ): LengthAwarePaginator {
        // Whitelist — ORDER BY no acepta parámetros preparados en SQL
        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        $user     = auth()->user();
        $empleado = $user->empleado;

        return Empleado::query()
            // JOINs directos — elimina orWhereHas (subquery correlacionada)
            ->join('users',           'users.id',           '=', 'empleados.user_id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles',           'roles.id',           '=', 'model_has_roles.role_id')
            ->leftJoin('areas',       'areas.id',           '=', 'empleados.area_id')
            ->leftJoin('centros_costos','centros_costos.id',  '=', 'empleados.centro_costo_id')
            ->leftJoin('empresas','empresas.id',  '=', 'empleados.empresa_id')
            ->select(
                'empleados.*',
                'users.email',
                'roles.name as rol_nombre',
                'areas.nombre as area_nombre',
                'empresas.nombre as empresa_nombre',
                'centros_costos.nombre as centro_costo_nombre',
            )
            // Restricción por centro de costo para gerente
            ->when($user->hasRole('manager'), fn($q) => $this->aplicarScopeManager($q, $user))
            // Búsqueda — ILIKE usa índice GIN trgm en PostgreSQL
            ->when($search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('empleados.nombre_completo', 'ilike', "%{$search}%")
                       ->orWhere('empleados.numero_nomina',  'ilike', "%{$search}%")
                       ->orWhere('users.email',              'ilike', "%{$search}%")
                )
            )
            ->when($estatus !== '', fn($q) => $q->where('empleados.estatus', $estatus))
            // Filtro por centro de costo solo para admin
            ->when($centroCostoId && $user->hasRole('admin'), fn($q) =>
                $q->where('empleados.centro_costo_id', $centroCostoId)
            )
            ->when($areaId, fn($q) => $q->where('empleados.area_id', $areaId))
            // Filtro por rol — ya en JOIN, sin subquery extra
            ->when($rol, fn($q) => $q->where('roles.name', $rol))
            ->orderBy("empleados.{$sortBy}", $sortDir)
            ->paginate($perPage);
    }

    public function list(): array
    {
        return Empleado::where('estatus', true)
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo'])
            ->toArray();
    }

    public function roles(): array
    {
        return Role::orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function create(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $tempPassword = Str::random(16); // 16 chars más seguro que 10

            $user = User::create([
                'name'                 => $data['nombre_completo'],
                'email'                => $data['email'],
                'password'             => Hash::make($tempPassword),
                'must_change_password' => true,
            ]);

            $user->assignRole($data['rol']);

            $empleado = Empleado::create([
                'user_id'         => $user->id,
                'nombre_completo' => $data['nombre_completo'],
                'puesto'          => $data['puesto'],
                'area_id'         => $data['area_id'],
                'centro_costo_id' => $data['centro_costo_id'],
                'empresa_id'      => $data['empresa_id'],
                'rfc'             => strtoupper(trim($data['rfc'])),
                'curp'            => strtoupper(trim($data['curp'])),
                'numero_nomina'   => $data['numero_nomina'],
                'banco_nomina'    => $data['banco_nomina'],
                'cuenta_nomina'   => $data['cuenta_nomina'],
                'clabe_nomina'    => $data['clabe_nomina'],
                'nss'             => $data['nss'],
                'fecha_ingreso'   => $data['fecha_ingreso'] ?? null,
                'telefono'        => $data['telefono'] ?? null,
                'tarjeta_credito_corporativa_asignada' => $data['tarjeta_credito_corporativa_asignada'] ?? false,
                'limite_credito_tarjeta' => $data['limite_credito_tarjeta'] ?? null,
                'estatus'         => true,
            ]);

            return [
                'empleado'      => $empleado->load('user'),
                'temp_password' => $tempPassword,
            ];
        });
    }

    public function update(Empleado $empleado, array $data): Empleado
    {
        return DB::transaction(function () use ($empleado, $data) {
            $empleado->update([
                'nombre_completo' => $data['nombre_completo'],
                'puesto'          => $data['puesto'],
                'area_id'         => $data['area_id'],
                'centro_costo_id' => $data['centro_costo_id'],
                'empresa_id'      => $data['empresa_id'],
                'rfc'             => strtoupper(trim($data['rfc'])),
                'curp'            => strtoupper(trim($data['curp'])),
                'numero_nomina'   => $data['numero_nomina'],
                'banco_nomina'    => $data['banco_nomina'],
                'cuenta_nomina'   => $data['cuenta_nomina'],
                'clabe_nomina'    => $data['clabe_nomina'],
                'nss'             => $data['nss'],
                'fecha_ingreso'   => $data['fecha_ingreso'] ?? null,
                'telefono'        => $data['telefono'] ?? null,
                'tarjeta_credito_corporativa_asignada' => $data['tarjeta_credito_corporativa_asignada'] ?? false,
                'limite_credito_tarjeta' => $data['limite_credito_tarjeta'] ?? null,
            ]);

            $empleado->user?->update([
                'name'  => $data['nombre_completo'],
                'email' => $data['email'],
            ]);

            if (!empty($data['rol'])) {
                $empleado->user?->syncRoles([$data['rol']]);
            }

            return $empleado->load('user');
        });
    }

    public function delete(Empleado $empleado): bool
    {
        return DB::transaction(function () use ($empleado) {
            $empleado->update(['estatus' => false]);

            $empleado->user?->update(['blocked' => true]);

            // Invalida todas las sesiones activas del empleado
            DB::table('sessions')
                ->where('user_id', $empleado->user_id)
                ->delete();

            return true;
        });
    }

    private function aplicarScopeManager($query, $user): void
    {
        $empleado = $user->empleado;

        if (!$empleado) {
            $query->whereRaw('1 = 0');
            return;
        }

        // ¿Hay otros managers en el mismo área?
        $otrosManagers = Empleado::whereHas('user.roles', fn($q) =>
            $q->where('name', 'manager')
        )
        ->where('area_id', $empleado->area_id)
        ->where('id', '!=', $empleado->id)
        ->exists();

        if ($otrosManagers) {
            // Varios managers en el área → scope = área + centro de costo propio
            $query->where('empleados.area_id', $empleado->area_id)
                ->where('empleados.centro_costo_id', $empleado->centro_costo_id);
        } else {
            // Único manager del área → ve toda el área sin filtro de CC
            $query->where('empleados.area_id', $empleado->area_id);
        }
    }
}
