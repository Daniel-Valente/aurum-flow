<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
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
        return $this->hasMany(Solicitud::class);
    }
}
