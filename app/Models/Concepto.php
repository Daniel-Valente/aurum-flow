<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estatus'
    ];

    protected $casts = [
        'estatus' => 'boolean'
    ];

    public function solicitudes()
    {
        return $this->hasMany(SolicitudDetalle::class);
    }

    public function gastos()
    {
        return $this->hasMany(Gasto::class);
    }
}
