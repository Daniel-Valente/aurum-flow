<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory, SoftDeletes, HasRoles;

    protected $fillable = [
        'user_id',
        'nombre_completo',
        'puesto',
        'area_id',
        'centro_costo_id',
        'estatus'
    ];

    protected $casts = [
        'estatus' => 'boolean'
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
}
