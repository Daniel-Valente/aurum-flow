<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class FlujoAprobacion extends Model
{
    protected $table = 'flujos_aprobacion';

    protected $fillable = [
        'tipo_solicitud',
        'role_id',
        'orden',
        'minimo_aprobaciones',
        'estatus'
    ];

    protected $casts = [
        'estatus' => 'boolean'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('estatus', true);
    }

    public function scopeTipo($query, string $tipo = 'viaticos')
    {
        return $query->where('tipo_solicitud', $tipo);
    }
}
