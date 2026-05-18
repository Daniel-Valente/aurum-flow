<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoTransferencia extends Model
{
    protected $table = 'presupuestos_transferencias';

    protected $fillable = [
        'presupuesto_origen_id',
        'presupuesto_destino_id',
        'monto',
        'motivo',
        'notas',
        'estatus',
        'solicitado_por',
        'solicitado_en',
        'aprobado_por',
        'aprobado_en',
        'comentario_aprobacion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'solicitado_en' => 'datetime',
        'aprobado_en' => 'datetime',
    ];

    public function presupuestoOrigen()
    {
        return $this->belongsTo(Presupuesto::class, 'presupuesto_origen_id');
    }

    public function presupuestoDestino()
    {
        return $this->belongsTo(Presupuesto::class, 'presupuesto_destino_id');
    }

    public function solicitadoPor()
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function scopePendientes($query)
    {
        return $query->where('estatus', 'pendiente');
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estatus', 'aprobada');
    }

    public function scopeEstatus($query, string $estatus)
    {
        return $query->where('estatus', $estatus);
    }

    public function aprobar(User $user, ?string $comentario = null): void
    {
        $this->update([
            'estatus' => 'aprobada',
            'aprobado_por' => $user->id,
            'aprobado_en' => now(),
            'comentario_aprobacion' => $comentario,
        ]);
    }

    public function rechazar(User $user, ?string $comentario = null): void
    {
        $this->update([
            'estatus' => 'rechazada',
            'aprobado_por' => $user->id,
            'aprobado_en' => now(),
            'comentario_aprobacion' => $comentario,
        ]);
    }

    public function cancelar(): void
    {
        $this->update(['estatus' => 'cancelada']);
    }

    public function getColorEstatusAttribute(): string
    {
        return match($this->estatus) {
            'pendiente' => 'yellow',
            'aprobada' => 'green',
            'rechazada' => 'red',
            'cancelada' => 'zinc',
            default => 'zinc',
        };
    }
}
