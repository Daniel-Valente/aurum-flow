<?php

namespace App\Services\Auditoria;

use App\Models\GastoAuditoria;

class AuditoriaService
{
    public function registrar(array $data)
    {
        return GastoAuditoria::create([
            'gasto_id' => $data['gasto_id'] ?? null,
            'excepcion_id' => $data['excepcion_id'] ?? null,
            'evento' => $data['evento'],
            'actor_id' => auth()->id,
            'origen' => $data['origen'] ?? 'sistema',
            'datos_antes' => $data['antes'] ?? null,
            'datos_despues' => $data['despues'] ?? null,
        ]);
    }
}
