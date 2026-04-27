<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class Concepto extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'descripcion',
        'tipo_aplicacion',
        'orden',

        'requiere_factura',
        'requiere_comprobante',
        'requiere_uuid',
        'permite_sin_factura',
        'aplica_iva',
        'acumulable_dia',

        'tope_referencia',

        'vigencia_desde',
        'vigencia_hasta',

        'estatus'
    ];

    protected $casts = [
        'requiere_factura' => 'boolean',
        'requiere_comprobante' => 'boolean',
        'requiere_uuid' => 'boolean',
        'permite_sin_factura' => 'boolean',
        'aplica_iva' => 'boolean',
        'acumulable_dia' => 'boolean',
        'estatus' => 'boolean',

        'tope_referencia' => 'decimal:2',
        'vigencia_desde' => 'date',
        'vigencia_hasta' => 'date'
    ];

    public function solicitudes()
    {
        return $this->hasManyThrough(
            Solicitud::class,
            SolicitudDetalle::class,
            'concepto_id',
            'id',
            'id',
            'solicitud_id'
        );
    }

    public function solicitudDetalles()
    {
        return $this->hasMany(SolicitudDetalle::class);
    }

    public function gastos()
    {
        return $this->hasMany(Gasto::class);
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'concepto_rol',
            'concepto_id',
            'rol_id'
        );
    }
}
