<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'empresa_id',
        'estatus'
    ];

    protected $casts = [
        'estatus' => 'boolean'
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
