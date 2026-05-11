<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComprobacionTarjeta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comprobaciones_tarjeta';

    protected $fillable = [
        'folio',
        'empleado_id',
        'proyecto_id',
        'solicitud_id',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'monto_total',
        'es_extension',
        'estatus',
        'motivo_rechazo',
        'conciliado_por',
        'conciliado_en',
    ];

    protected $casts = [
        'fecha_inicio'  => 'date',
        'fecha_fin'     => 'date',
        'monto_total'   => 'decimal:2',
        'es_extension'  => 'boolean',
        'conciliado_en' => 'datetime',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'comprobacion_tarjeta_id');
    }

    public function conciliador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conciliado_por');
    }

    public function scopeAbierta($query)
    {
        return $query->where('estatus', 'abierta');
    }

    public function scopeEnRevision($query)
    {
        return $query->where('estatus', 'en_revision');
    }

    public function scopeConciliada($query)
    {
        return $query->where('estatus', 'conciliada');
    }

    public function scopeExtensiones($query)
    {
        return $query->where('es_extension', true)->whereNotNull('solicitud_id');
    }

    public function scopeDelEmpleado($query, int $empleadoId)
    {
        return $query->where('empleado_id', $empleadoId);
    }

    public function getDiasAttribute(): int
    {
        if (!$this->fecha_inicio || !$this->fecha_fin) {
            return 0;
        }

        return $this->fecha_inicio->diffInDays($this->fecha_fin) + 1;
    }

    public function getEstatusLabelAttribute(): string
    {
        return match($this->estatus) {
            'abierta'     => 'Abierta',
            'en_revision' => 'En revisión',
            'conciliada'  => 'Conciliada',
            'rechazada'   => 'Rechazada',
            default       => ucfirst($this->estatus)

        };
    }

    public function getEstatusColorAttribute(): string
    {
        return match ($this->estatus) {
            'abierta'     => 'cyan',
            'en_revision' => 'yellow',
            'conciliada'  => 'green',
            'rechazada'   => 'red',
            default       => 'zinc'
        };
    }

    public function getAceptaGastosAttribute(): bool
    {
        return $this->estatus === 'abierta';
    }

    public function getEsTerminalAttribute(): bool
    {
        return in_array($this->estatus, ['conciliada', 'rechazada'], true);
    }
}
