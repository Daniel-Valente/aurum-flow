<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudDetalle extends Model
{
    protected $fillable = [
        'solicitud_id',
        'concepto_id',
        'monto_estimado',
        'justificacion_exceso',
    ];

    protected $casts = [
        'monto_estimado' => 'decimal:2',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function concepto()
    {
        return $this->belongsTo(Concepto::class);
    }
}
