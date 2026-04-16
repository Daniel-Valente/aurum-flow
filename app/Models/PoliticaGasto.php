<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class PoliticaGasto extends Model
{
    protected $fillable = [
        'role_id',
        'concepto_id',
        'monto_max',
        'tipo_limite'
    ];

    public function concepto()
    {
        return $this->belongsTo(Concepto::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
