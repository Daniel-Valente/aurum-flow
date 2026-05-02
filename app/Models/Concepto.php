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

        // Diario | Evento | Viaje — define el "ritmo" del concepto
        'tipo_aplicacion',

        'orden',

        // Naturaleza fiscal — no varía por rol, es propia del concepto
        'aplica_iva',

        // Precio promedio de mercado (referencia informativa para el validador)
        'tope_referencia',

        'vigencia_desde',
        'vigencia_hasta',

        'estatus',
    ];

    protected $casts = [
        'aplica_iva'       => 'boolean',
        'estatus'          => 'boolean',
        'tope_referencia'  => 'decimal:2',
        'vigencia_desde'   => 'date',
        'vigencia_hasta'   => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------

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

    /**
     * Roles que tienen este concepto habilitado.
     * Tabla pivot: concepto_rol (concepto_id, rol_id)
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'concepto_rol',
            'concepto_id',
            'rol_id'
        );
    }

    /**
     * Políticas de gasto activas que aplican a este concepto.
     */
    public function politicas()
    {
        return $this->hasMany(PoliticaGasto::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

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
