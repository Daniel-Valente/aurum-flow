<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Presupuesto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'empresa_id',
        'area_id',
        'empleado_id',
        'proyecto_id',
        'monto_total',
        'monto_gastado',
        'monto_comprometido',
        'periodo',
        'fecha_inicio',
        'fecha_fin',
        'renovable',
        'frecuencia_renovacion',
        'alerta_porcentaje',
        'critico_porcentaje',
        'activo',
        'estatus',
        'creado_por',
        'aprobado_por',
        'aprobado_en',
        'notas',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_gastado' => 'decimal:2',
        'monto_comprometido' => 'decimal:2',
        'alerta_porcentaje' => 'decimal:2',
        'critico_porcentaje' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'renovable' => 'boolean',
        'activo' => 'boolean',
        'aprobado_en' => 'datetime',
    ];

    protected $appends = ['monto_disponible', 'porcentaje_consumido', 'dias_restantes'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function movimientos()
    {
        return $this->hasMany(PresupuestoMovimiento::class)->orderByDesc('fecha_movimiento');
    }

    public function alertas()
    {
        return $this->hasMany(PresupuestoAlerta::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function getMontoDisponibleAttribute(): float
    {
        return max(0, $this->monto_total - $this->monto_gastado - $this->monto_comprometido);
    }

    public function getPorcentajeConsumidoAttribute(): float
    {
        if ($this->monto_total <= 0) return 0;

        $consumido = $this->monto_gastado + $this->monto_comprometido;
        return round(($consumido / $this->monto_total) * 100, 2);
    }

    public function getDiasRestantesAttribute(): ?int
    {
        if (!$this->fecha_fin) return null;

        $hoy = Carbon::today();
        $fin = Carbon::parse($this->fecha_fin);

        if ($fin->lt($hoy)) return 0;

        return $hoy->diffInDays($fin);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true)->where('estatus', 'activo');
    }

    public function scopeVigentes($query)
    {
        return $query->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->where('estatus', 'activo');
    }

    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeEmpresa($query, int $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeArea($query, int $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    public function scopeEmpleado($query, int $empleadoId)
    {
        return $query->where('empleado_id', $empleadoId);
    }

    public function scopeProyecto($query, int $proyectoId)
    {
        return $query->where('proyecto_id', $proyectoId);
    }

    // Métodos de negocio
    public function puedeComprometer(float $monto): bool
    {
        return $this->monto_disponible >= $monto;
    }

    public function estaEnAlerta(): bool
    {
        return $this->porcentaje_consumido >= $this->alerta_porcentaje;
    }

    public function estaEnCritico(): bool
    {
        return $this->porcentaje_consumido >= $this->critico_porcentaje;
    }

    public function estaAgotado(): bool
    {
        return $this->monto_disponible <= 0;
    }

    public function estaVencido(): bool
    {
        return $this->fecha_fin && Carbon::parse($this->fecha_fin)->lt(Carbon::today());
    }

    public function getSeveridad(): string
    {
        if ($this->estaAgotado()) return 'agotado';
        if ($this->estaEnCritico()) return 'critico';
        if ($this->estaEnAlerta()) return 'alerta';
        return 'normal';
    }
}
