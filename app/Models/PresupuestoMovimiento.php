<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PresupuestoMovimiento extends Model
{
    protected $table = 'presupuestos_movimientos';

    protected $fillable = [
        'presupuesto_id',
        'tipo',
        'monto',
        'saldo_gastado',
        'saldo_comprometido',
        'saldo_disponible',
        'origen_type',
        'origen_id',
        'concepto',
        'notas',
        'actor_id',
        'fecha_movimiento',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'saldo_gastado' => 'decimal:2',
        'saldo_comprometido' => 'decimal:2',
        'saldo_disponible' => 'decimal:2',
        'fecha_movimiento' => 'datetime',
    ];

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class);
    }

    public function origen(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePresupuesto($query, int $presupuestoId)
    {
        return $query->where('presupuesto_id', $presupuestoId);
    }

    public function scopeEntreFechas($query, $inicio, $fin)
    {
        return $query->whereBetween('fecha_movimiento', [$inicio, $fin]);
    }

    public function esPositivo(): bool
    {
        return in_array($this->tipo, [
            'liberacion',
            'ajuste_incremento',
            'transferencia_in',
        ]);
    }

    public function esNegativo(): bool
    {
        return in_array($this->tipo, [
            'gasto',
            'compromiso',
            'ajuste_decremento',
            'transferencia_out',
        ]);
    }

    public function getDescripcionTipoAttribute(): string
    {
        return match($this->tipo) {
            'gasto' => 'Gasto realizado',
            'compromiso' => 'Compromiso (solicitud autorizada)',
            'liberacion' => 'Liberación de compromiso',
            'ajuste_incremento' => 'Ajuste: incremento manual',
            'ajuste_decremento' => 'Ajuste: decremento manual',
            'transferencia_in' => 'Transferencia recibida',
            'transferencia_out' => 'Transferencia enviada',
            default => 'Movimiento',
        };
    }
}
