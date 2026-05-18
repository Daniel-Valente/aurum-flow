<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresupuestoSolicitud extends Model
{
    protected $table = 'presupuestos_solicitudes';

    protected $fillable = [
        'presupuesto_id',
        'solicitud_id',
        'monto_comprometido',
        'monto_consumido',
        'estatus',
    ];

    protected $casts = [
        'monto_comprometido' => 'decimal:2',
        'monto_consumido'    => 'decimal:2',
    ];

    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function estaComprometido(): bool
    {
        return $this->estatus === 'comprometido';
    }

    public function estaConsumido(): bool
    {
        return $this->estatus === 'consumido';
    }

    public function pendientePorConsumir(): float
    {
        return (float) $this->monto_comprometido
            - (float) $this->monto_consumido;
    }
}
