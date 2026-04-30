<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class PoliticaGastoVersion extends Model
{
    protected $table = 'politicas_gastos_versiones';

    protected $fillable = [
        'politica_id',
        'role_id',
        'concepto_id',
        'monto_max',
        'tipo_limite',
        'permite_excepcion',
        'vigencia_desde',
        'vigencia_hasta',
        'motivo',
        'creado_por',
        'aprobado_por',
        'approved_at',
        'estatus',
    ];

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
