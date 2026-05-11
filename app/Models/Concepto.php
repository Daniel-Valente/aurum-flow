<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'descripcion',

        // Naturaleza fiscal — no varía por rol, es propia del concepto
        'aplica_iva',
        'aplica_ish',
        'aplica_ieps',

        'vigencia_desde',
        'vigencia_hasta',

        'estatus',
    ];

    protected $casts = [
        'aplica_iva'       => 'boolean',
        'aplica_ish'       => 'boolean',
        'aplica_ieps'      => 'boolean',
        'estatus'          => 'boolean',
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
