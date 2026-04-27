<?php

namespace App\Services\Concepto;

use App\Models\Concepto;

class ConceptoService
{
    public function paginate(
        string  $search         = '',
        string  $tipoAplicacion = '',
        string  $estatus        = '',
        string  $categoria      = '',
        string  $vigencia       = '',   // ← 'vigentes' | 'no_vigentes' | '' (todos)
        ?string $rol            = null,
        int     $perPage        = 15,
    ) {
        return Concepto::query()
            ->with('roles')
            ->when($search, fn($q) =>
                $q->where('nombre', 'like', "%{$search}%")
                ->orWhere('codigo', 'like', "%{$search}%")
            )
            ->when($tipoAplicacion, fn($q) =>
                $q->where('tipo_aplicacion', $tipoAplicacion)
            )
            ->when($estatus !== '', fn($q) =>
                $q->where('estatus', $estatus)
            )
            ->when($categoria, fn($q) =>
                $q->where('categoria', $categoria)
            )
            ->when($vigencia === 'vigentes', fn($q) =>
                $q->where(fn($q2) =>
                    $q2->whereNull('vigencia_desde')
                    ->orWhere('vigencia_desde', '<=', now())
                )
                ->where(fn($q2) =>
                    $q2->whereNull('vigencia_hasta')
                    ->orWhere('vigencia_hasta', '>=', now())
                )
            )
            ->when($vigencia === 'no_vigentes', fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('vigencia_desde', '>', now())
                    ->orWhere('vigencia_hasta', '<', now())
                )
            )
            ->when($rol, fn($q) =>
                $q->whereHas('roles', fn($r) => $r->where('name', $rol))
            )
            ->orderBy('orden')
            ->paginate($perPage);
    }

    public function list(?int $rolId = null): array
    {
        return Concepto::query()
            ->with('roles')
            ->where('estatus', true)
            ->where(fn($q) =>
                $q->whereNull('vigencia_desde')
                  ->orWhere('vigencia_desde', '<=', now())
            )
            ->where(fn($q) =>
                $q->whereNull('vigencia_hasta')
                  ->orWhere('vigencia_hasta', '>=', now())
            )
            ->when($rolId, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->whereHas('roles', fn($r) =>
                            $r->where('roles.id', $rolId)
                        )
                       ->orWhereDoesntHave('roles')
                )
            )
            ->orderBy('nombre')
            ->get()
            ->toArray();
    }

    public function create(array $data): Concepto
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $concepto = Concepto::create($data);

        if (!empty($roles)) {
            $ids = \Spatie\Permission\Models\Role::whereIn('name', $roles)
            ->pluck('id');
            $concepto->roles()->sync($ids);
        }

        return $concepto->load('roles');
    }

    public function update(Concepto $concepto, array $data): Concepto
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $concepto->update($data);

        $ids = \Spatie\Permission\Models\Role::whereIn('name', $roles)
            ->pluck('id');
        $concepto->roles()->sync($ids);

        return $concepto->load('roles');
    }

    public function delete(Concepto $concepto): bool
    {
        $concepto->delete();
        return true;
    }

    public function toggle(Concepto $concepto): Concepto
    {
        $concepto->update(['estatus' => !$concepto->estatus]);
        return $concepto->fresh();
    }

    public function categorias(): array
    {
        return Concepto::query()
            ->whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria')
            ->toArray();
    }
}
