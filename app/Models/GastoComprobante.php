<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoComprobante extends Model
{
    protected $fillable = [
        'gasto_id',

        'archivo',
        'archivo_pdf',
        'tipo',
        'uuid',

        'validacion_manual',
        'validado_por',
        'comentario_validacion',
        'validado_en',

        'monto',
        'fecha_gasto',

        'cfdi_compartido',
        'comprobante_origen_id',
        'monto_total_cfdi',

        'subtotal',
        'descuento',
        'iva',
        'ieps',
        'ish',

        'tasa_iva',
        'tasa_ieps',
        'tasa_ish',

        'sat_status',
        'sat_checked_at',
        'sat_attempts',
        'meta_cfdi',
        'sat_last_error',

        'subido_por',
        'fecha_subida'
    ];

    protected $casts = [
        'monto'            => 'decimal:2',
        'monto_total_cfdi' => 'decimal:2',
        'subtotal'         => 'decimal:2',
        'descuento'        => 'decimal:2',
        'iva'              => 'decimal:2',
        'ieps'             => 'decimal:2',
        'ish'              => 'decimal:2',
        'meta_cfdi'        => 'array',
        'fecha_subida'     => 'date',
        'fecha_gasto'      => 'date',
        'sat_checked_at'   => 'datetime',
        'validado_en'      => 'datetime',
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
