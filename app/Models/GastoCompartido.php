<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoCompartido extends Model
{
    protected $table = 'gastos_compartidos';

    protected $fillable = [
        'gasto_pagador_id',
        'tipo',
        'empleado_receptor_id',
        'cliente_descripcion',
        'monto_compartido',
        'gasto_receptor_id',
        'estatus'
    ];

    protected $casts = [
        'monto_compartido' => 'decimal:2',
    ];

    public function gastoPagador()
    {
        return $this->belongsTo(Gasto::class, 'gasto_pagador_id');
    }

    public function gastoReceptor()
    {
        return $this->belongsTo(Gasto::class, 'gasto_receptor_id');
    }

    public function empleadoReceptor()
    {
        return $this->belongsTo(Empleado::class, 'empleado_receptor_id');
    }
}
