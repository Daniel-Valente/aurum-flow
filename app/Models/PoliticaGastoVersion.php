<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class PoliticaGastoVersion extends Model
{
    protected $table = 'politicas_gastos_versiones';

    protected $casts = [
        'vigencia_desde' => 'date',
        'vigencia_hasta' => 'date',
        'permite_excepcion' => 'boolean'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
