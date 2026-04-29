<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class PoliticaGasto extends Model
{
    use SoftDeletes;

    protected $table = 'politicas_gastos';

    protected $fillable = [
        'role_id',
        'concepto_id',
        'monto_max',
        'tipo_limite',
        'permite_excepcion',
        'vigencia_desde',
        'vigencia_hasta'
    ];

    protected $casts = [
        'monto_max' => 'decimal:2',
        'permite_excepcion' => 'boolean',
        'vigencia_desde' => 'date',
        'vigencia_hasta' => 'date'
    ];

    public function concepto()
    {
        return $this->belongsTo(Concepto::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function scopeVigente($query)
    {
        return $query
            ->where(function ($q) {
                $q->whereNull('vigencia_desde')
                    ->orWhere('vigencia_desde', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('vigencia_hasta')
                    ->orWhere('vigencia_hasta', '>=', now());
            });
    }
}
