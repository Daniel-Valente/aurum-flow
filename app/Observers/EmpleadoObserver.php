<?php

namespace App\Observers;

use App\Models\Empleado;

class EmpleadoObserver
{
    public function saved(Empleado $empleado): void
    {
        $user = $empleado->user;

        $permisosTarjeta = [
            'gastos.tarjeta.crear',
            'gastos.tarjeta.ver.propios',
        ];

        if ($empleado->tarjeta_credito_corporativa_asignada) {

            $user->givePermissionTo($permisosTarjeta);

        } else {

            $user->revokePermissionTo($permisosTarjeta);
        }
    }
}
