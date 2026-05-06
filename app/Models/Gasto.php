<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gasto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'solicitud_id',
        'concepto_id',
        'fecha_gasto',
        'monto',
        'uuid_factura',
        'estatus'
    ];

    protected $casts = [
        'fecha_gasto' => 'date',
        'monto' => 'decimal:2'
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function empleado()
    {
        return $this->hasOneThrough(
            Empleado::class,
            Solicitud::class,
            'id', // solicitud.id
            'id', // empleado.id
            'solicitud_id',
            'empleado_id'
        );
    }

    public function concepto()
    {
        return $this->belongsTo(Concepto::class);
    }

    public function comprobantes()
    {
        return $this->hasMany(GastoComprobante::class);
    }

    public function excepciones()
    {
        return $this->hasMany(GastoExcepcion::class);
    }

    public function detalle()
    {
        return $this->belongsTo(SolicitudDetalle::class, 'id');
    }
}
