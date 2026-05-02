<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class FolioHelper
{
    public static function generar(string $prefix, int $padding = 4)
    {
        $year = now()->year;

        return DB::transaction(function () use ($prefix, $year, $padding) {

            // 🔥 PostgreSQL atomic upsert
            $row = DB::selectOne("
                INSERT INTO folio_counters (prefix, year, current)
                VALUES (?, ?, 1)
                ON CONFLICT (prefix, year)
                DO UPDATE SET current = folio_counters.current + 1
                RETURNING current
            ", [$prefix, $year]);

            $consecutivo = str_pad($row->current, $padding, '0', STR_PAD_LEFT);

            return "{$prefix}-{$year}-{$consecutivo}";
        });
    }
}
