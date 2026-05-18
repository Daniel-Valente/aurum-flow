<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoAlerta extends Model
{
    protected $table = 'presupuestos_alertas';

    protected $fillable = [
        'presupuesto_id',
        'tipo',
        'severidad',
        'titulo',
        'mensaje',
        'porcentaje_consumido',
        'monto_disponible',
        'dias_restantes',
        'notificado',
        'notificado_en',
        'resuelto',
        'resuelto_en',
        'resolucion',
    ];

    protected $casts = [
        'porcentaje_consumido' => 'decimal:2',
        'monto_disponible' => 'decimal:2',
        'notificado' => 'boolean',
        'notificado_en' => 'datetime',
        'resuelto' => 'boolean',
        'resuelto_en' => 'datetime',
    ];

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('resuelto', false);
    }

    public function scopeNoNotificadas($query)
    {
        return $query->where('notificado', false);
    }

    public function scopeSeveridad($query, string $severidad)
    {
        return $query->where('severidad', $severidad);
    }

    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function marcarNotificado(): void
    {
        $this->update([
            'notificado' => true,
            'notificado_en' => now(),
        ]);
    }

    public function resolver(string $resolucion): void
    {
        $this->update([
            'resuelto' => true,
            'resuelto_en' => now(),
            'resolucion' => $resolucion,
        ]);
    }

    public function getColorBadgeAttribute(): string
    {
        return match($this->severidad) {
            'info' => 'blue',
            'warning' => 'yellow',
            'danger' => 'orange',
            'critical' => 'red',
            default => 'zinc',
        };
    }

    public function getIconoAttribute(): string
    {
        return match($this->tipo) {
            'alerta' => 'exclamation-triangle',
            'critico' => 'exclamation-circle',
            'agotado' => 'x-circle',
            'excedido' => 'shield-exclamation',
            'proximo_vencer' => 'clock',
            default => 'bell',
        };
    }
}
