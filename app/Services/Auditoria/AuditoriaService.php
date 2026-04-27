<?php

namespace App\Services\Auditoria;

use App\Models\GastoAuditoria;
use App\Models\PoliticaGastoAuditoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AuditoriaService
{
    public function registrar(array $data): GastoAuditoria
    {
        return GastoAuditoria::create([
            'gasto_id'      => $data['gasto_id']      ?? null,
            'excepcion_id'  => $data['excepcion_id']  ?? null,
            'solicitud_id'  => $data['solicitud_id']  ?? null,
            'evento'        => $data['evento'],
            // ✅ auth()->id() con paréntesis — sin paréntesis devuelve null siempre
            'actor_id'      => $data['actor_id']      ?? auth()->id(),
            'origen'        => $data['origen']        ?? 'sistema',
            'datos_antes'   => isset($data['antes'])   ? json_encode($data['antes'])   : null,
            'datos_despues' => isset($data['despues']) ? json_encode($data['despues']) : null,
        ]);
    }

    /**
     * Insert masivo de auditorías — usado por ValidadorGastosService
     * para evitar N queries dentro de loops.
     */
    public function registrarBatch(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $now = now();

        $insert = array_map(fn($data) => [
            'gasto_id'      => $data['gasto_id']      ?? null,
            'excepcion_id'  => $data['excepcion_id']  ?? null,
            'solicitud_id'  => $data['solicitud_id']  ?? null,
            'evento'        => $data['evento'],
            'actor_id'      => $data['actor_id']      ?? auth()->id(),
            'origen'        => $data['origen']        ?? 'sistema',
            'datos_antes'   => isset($data['antes'])   ? json_encode($data['antes'])   : null,
            'datos_despues' => isset($data['despues']) ? json_encode($data['despues']) : null,
            'created_at'    => $now,
            'updated_at'    => $now,
        ], $rows);

        DB::table('gasto_auditorias')->insert($insert);
    }
}


class AuditoriaTimelineService
{
    public function global(int $perPage = 50): LengthAwarePaginator
    {
        // UNION ALL en SQL — sin ::all() ni merge() en PHP
        // Los tipos de cast con NULL::bigint son necesarios para que PostgreSQL
        // iguale tipos en el UNION y no lance error de columnas incompatibles
        $gastos = GastoAuditoria::select([
            'id',
            'gasto_id',
            DB::raw('NULL::bigint  AS politica_id'),
            'evento',
            'actor_id',
            'origen',
            'datos_antes',
            'datos_despues',
            'created_at',
            DB::raw("'gasto' AS tipo"),
        ]);

        $politicas = PoliticaGastoAuditoria::select([
            'id',
            DB::raw('NULL::bigint  AS gasto_id'),
            'politica_id',
            'evento',
            'actor_id',
            DB::raw("'sistema'     AS origen"),
            DB::raw('NULL::jsonb   AS datos_antes'),
            'datos_despues',
            'created_at',
            DB::raw("'politica'    AS tipo"),
        ]);

        return $gastos
            ->unionAll($politicas)
            ->orderByDesc('created_at')
            ->paginate(min($perPage, 100));
    }
}
