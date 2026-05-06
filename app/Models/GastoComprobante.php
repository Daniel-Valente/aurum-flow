<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoComprobante extends Model
{
    protected $fillable = [
        'gasto_id',
        'archivo',
        'tipo',
        'uuid',
        'monto',
        'subido_por',
        'archivo_pdf',
        'fecha_gasto',
        'sat_status',
        'validacion_manual',
        'meta_cfdi',
        // campos de validación manual
        'validado_por',
        'comentario_validacion',
        'validado_en',
    ];

    protected $casts = [
        'monto'      => 'decimal:2',
        'meta_cfdi'  => 'array',
        'fecha_gasto'    => 'date',
        'sat_checked_at' => 'datetime',
        'validado_en'=> 'datetime',
    ];

    public function gasto()
    {
        return $this->belongsTo(Gasto::class);
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function validador()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    public function scopePendienteManual($query)
    {
        return $query->whereIn('tipo', ['pdf', 'recibo', 'factura'])
            ->where('validacion_manual', 'pendiente');
    }
}
