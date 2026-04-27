<?php

namespace App\Services\Auditoria;

use App\Models\GastoAuditoria;
use App\Models\PoliticaGastoAuditoria;
use Illuminate\Support\Facades\DB;

class AuditoriaTimelineService
{
    public function global(int $perPage = 50)
    {
        // Una sola query con UNION ALL en PostgreSQL, paginada
        $gastos = GastoAuditoria::select([
                'id', 'gasto_id', 'evento', 'actor_id',
                'created_at',
                DB::raw("'gasto' as tipo"),
                DB::raw('NULL::bigint as politica_id'),
            ]);

        $politicas = PoliticaGastoAuditoria::select([
                'id',
                DB::raw('NULL::bigint as gasto_id'),
                'evento', 'actor_id',
                'created_at',
                DB::raw("'politica' as tipo"),
                'politica_id',
            ]);

        return $gastos
            ->unionAll($politicas)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
