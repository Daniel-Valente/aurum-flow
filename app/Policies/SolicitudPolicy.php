<?php

namespace App\Policies;

use App\Models\Solicitud;
use App\Models\User;

class SolicitudPolicy
{
    public function ver(User $user, Solicitud $solicitud): bool
    {
        if ($user->empleado?->id === $solicitud->empleado_id) {
            return true;
        }

        return false;
    }
}
