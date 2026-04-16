<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class PoliticaGastoAuditoria extends Model
{
    public $timestamps = false;

    protected $table = 'politicas_gastos_auditoria';

    protected $casts = [
        'datos_antes' => 'array',
        'datos_despues' => 'array'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
