<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'folio',
        'empleado_id',
        'area_id',
        'proyecto_id',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'monto_total',
        'estatus'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto_total' => 'decimal:2'
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function detalles()
    {
        return $this->hasMany(SolicitudDetalle::class);
    }

    public function gastos()
    {
        return $this->hasMany(Gasto::class);
    }
}
