<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroCosto extends Model
{
    use HasFactory;

    protected $table = 'centros_costos';

        protected $fillable = [
        'nombre',
        'clave',
        'estatus'
    ];

    protected $casts = [
        'estatus' => 'boolean'
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }
}
