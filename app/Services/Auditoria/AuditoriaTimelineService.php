<?php

namespace App\Services\Auditoria;

use App\Models\GastoAuditoria;
use App\Models\PoliticaGastoAuditoria;

class AuditoriaTimelineService
{
    public function global()
    {
        return collect()
            ->merge(GastoAuditoria::all())
            ->merge(PoliticaGastoAuditoria::all())
            ->sortByDesc('created_at')
            ->values();
    }
}
