<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoAuditoria extends Model
{
    public $timestamps = false;

    protected $table = 'gastos_auditoria';

    protected $fillable = [
        'gasto_id',
        'excepcion_id',
        'evento',
        'actor_id',
        'origen',
        'datos_antes',
        'datos_despues'
    ];

    protected $casts = [
        'datos_antes' => 'array',
        'datos_despues' => 'array'
    ];
}
