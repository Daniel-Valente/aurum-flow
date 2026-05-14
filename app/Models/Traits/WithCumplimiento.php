<?php

namespace App\Models\Traits;

trait WithCumplimiento
{
    public function scopeWithCumplimiento($query)
    {
        $query->selectRaw("({$this->cumplimientoSql()}) as cumplimiento_calculado");

        return $query;
    }

    public function scopeFilterCumplimiento($query, ?string $cumplimiento)
    {
        if (!$cumplimiento) {
            return $query;
        }

        return $query->whereRaw(
            "({$this->cumplimientoSql()}) = ?",
            [$cumplimiento]
        );
    }

    private function cumplimientoSql(): string
    {
        return "
            CASE

                WHEN NOT EXISTS (
                    SELECT 1
                    FROM gastos g
                    WHERE g.solicitud_id = solicitudes.id
                    AND g.deleted_at IS NULL
                )
                THEN 'sin_captura'

                WHEN EXISTS (
                    SELECT 1
                    FROM gastos g
                    WHERE g.solicitud_id = solicitudes.id
                    AND g.deleted_at IS NULL
                    AND g.estatus = 'rechazado'
                )
                THEN 'rechazado'

                WHEN EXISTS (
                    SELECT 1
                    FROM gastos_excepciones ge
                    INNER JOIN gastos g ON g.id = ge.gasto_id
                    WHERE g.solicitud_id = solicitudes.id
                    AND ge.estatus = 'pendiente'
                )
                THEN 'con_excepcion'

                ELSE 'ok'

            END
        ";
    }
}
