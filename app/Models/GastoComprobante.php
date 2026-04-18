<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoComprobante extends Model
{
    protected $fillable = [
        'gasto_id',
        'archivo',
        'tipo',
        'uuid',
        'monto',
        'subido_por'
    ];

    public function gasto()
    {
        return $this->belongsTo(Gasto::class);
    }

    public function comprobantes()
    {
        return $this->hasMany(GastoComprobante::class);
    }
}
