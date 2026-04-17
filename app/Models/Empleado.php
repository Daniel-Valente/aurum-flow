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
        'estatus'
    ];

    protected $casts = [
        'estatus' => 'boolean',
        'deleted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
