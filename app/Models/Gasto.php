<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gasto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'solicitud_id',
        'comprobacion_tarjeta_id',
        'concepto_id',
        'fecha_gasto',
        'monto',
        'uuid_factura',
        'estatus',
    ];

    protected $casts = [
        'fecha_gasto' => 'date',
        'monto'       => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function comprobacionTarjeta(): BelongsTo
    {
        return $this->belongsTo(
            ComprobacionTarjeta::class,
            'comprobacion_tarjeta_id'
        );
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(Concepto::class);
    }

    public function comprobantes()
    {
        return $this->hasMany(GastoComprobante::class);
    }

    public function excepciones()
    {
        return $this->hasMany(GastoExcepcion::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de dominio
    |--------------------------------------------------------------------------
    */

    public function esSolicitud(): bool
    {
        return !is_null($this->solicitud_id);
    }

    public function esTarjeta(): bool
    {
        return !is_null($this->comprobacion_tarjeta_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors unificados
    |--------------------------------------------------------------------------
    */

    public function getOrigenAttribute(): string
    {
        return $this->esSolicitud()
            ? 'solicitud'
            : 'tarjeta';
    }

    public function getEmpleadoAttribute(): ?Empleado
    {
        return $this->solicitud?->empleado
            ?? $this->comprobacionTarjeta?->empleado;
    }

    public function getProyectoAttribute()
    {
        return $this->solicitud?->proyecto
            ?? $this->comprobacionTarjeta?->proyecto;
    }
}
