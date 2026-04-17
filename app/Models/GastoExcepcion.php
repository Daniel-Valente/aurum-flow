<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoExcepcion extends Model
{
    use HasFactory;

    protected $fillable = [
        'gasto_id',
        'nivel',
        'aprobado_por',
        'estatus',
        'comentarios'
    ];

    protected $casts = [
        'nivel' => 'integer'
    ];

    public function gasto()
    {
        return $this->belongsTo(Gasto::class);
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}
