<?php

namespace App\Models;

use App\Models\Traits\WithCumplimiento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Solicitud extends Model
{
    use HasFactory, SoftDeletes, WithCumplimiento;

    protected $table = 'solicitudes';

    protected $fillable = [
        'folio',
        'empleado_id',
        'area_id',
        'proyecto_id',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'fecha_solicitud',
        'monto_total',
        'motivo_rechazo',
        'motivo_cancelacion',
        'motivo_rechazo',
        'estatus'
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
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

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function scopePropias($query, $user)
    {
        return $query->where('empleado_id', $user->empleado->id);
    }
}
