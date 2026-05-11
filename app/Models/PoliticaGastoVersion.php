<?php

namespace App\Models;

use App\Models\Traits\EvaluaComprobacion;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class PoliticaGastoVersion extends Model
{
    use EvaluaComprobacion;

    protected $table = 'politicas_gastos_versiones';

    protected $fillable = [
        // Referencia a la política padre
        'politica_id',

        // Snapshot completo del estado de la política en este momento
        'role_id',
        'concepto_id',

        'monto_max',
        'tipo_limite',

        // Tramos documentales (snapshot)
        'monto_libre',
        'monto_comprobante',
        'monto_factura',

        'valida_sat',
        'acumulable_dia',
        'permite_excepcion',

        // Se puede permitir propina y establecer un monto máximo de porcentaje
        'permite_propina',
        'propina_max_porcentaje',

        'vigencia_desde',
        'vigencia_hasta',

        // Metadatos de la versión
        'motivo',
        'creado_por',
        'aprobado_por',
        'approved_at',

        // Borrador | Aprobada | Inactiva
        'estatus',
    ];

    protected $casts = [
        'monto_max'              => 'decimal:2',
        'monto_libre'            => 'decimal:2',
        'monto_comprobante'      => 'decimal:2',
        'monto_factura'          => 'decimal:2',
        'propina_max_porcentaje' => 'decimal:2',
        'valida_sat'             => 'boolean',
        'acumulable_dia'         => 'boolean',
        'permite_excepcion'      => 'boolean',
        'permite_propina'        => 'boolean',
        'vigencia_desde'         => 'date',
        'vigencia_hasta'         => 'date',
        'approved_at'            => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------

    public function politica()
    {
        return $this->belongsTo(PoliticaGasto::class, 'politica_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function concepto()
    {
        return $this->belongsTo(Concepto::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeAprobada($query)
    {
        return $query->where('estatus', 'Aprobada');
    }

    public function scopeVigente($query)
    {
        return $query
            ->where('estatus', 'Aprobada')
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
