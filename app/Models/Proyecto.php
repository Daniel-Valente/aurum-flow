<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'cliente',
        'tipo',
        'descripcion',
        'region',
        'prioridad',
        'estado_operativo',
        'centro_costo_id',
        'responsable_id',
        'presupuesto_total',
        'fecha_inicio',
        'fecha_fin',
        'pais',
        'ciudad',
        'estatus'
    ];

    protected $casts = [
        'estatus' => 'boolean',
        'presupuesto_total' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
    }

    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class);
    }

    public function responsable()
    {
        return $this->belongsTo(Empleado::class, 'responsable_id');
    }
}
