<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nombre_completo',
        'puesto',
        'area_id',
        'centro_costo_id',
        'rfc',
        'curp',
        'numero_nomina',
        'banco_nomina',
        'cuenta_nomina',
        'clabe_nomina',
        'nss',
        'fecha_ingreso',
        'telefono',
        'estatus',
        'tarjeta_credito_corporativa_asignada',
        'limite_credito_tarjeta'
    ];

    protected $casts = [
        'estatus'      => 'boolean',
        'fecha_ingreso' => 'date',
        'deleted_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRoleAttribute()
    {
        return $this->user?->roles->first();
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('estatus', true);
    }

    public function scopeInactivos($query)
    {
        return $query->where('estatus', false);
    }
}
