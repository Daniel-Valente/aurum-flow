<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadLog extends Model
{
    protected $table = 'actividad_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'empresa_id',
        'area_id',
        'evento',
        'modulo',
        'entidad_type',
        'entidad_id',
        'entidad_descripcion',
        'datos_antes',
        'datos_despues',
        'metadatos',
        'ip_address',
        'user_agent',
        'session_id',
        'ciudad',
        'pais',
        'latitud',
        'longitud',
        'severidad',
        'es_sensible',
    ];

    protected $casts = [
        'datos_antes' => 'array',
        'datos_despues' => 'array',
        'metadatos' => 'array',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'es_sensible' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function entidad()
    {
        return $this->morphTo();
    }

    public function scopeUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopeEvento($query, string $evento)
    {
        return $query->where('evento', $evento);
    }

    public function scopeSeveridad($query, string $severidad)
    {
        return $query->where('severidad', $severidad);
    }

    public function scopeSensibles($query)
    {
        return $query->where('es_sensible', true);
    }

    public function scopeEntreFechas($query, $inicio, $fin)
    {
        return $query->whereBetween('created_at', [$inicio, $fin]);
    }

    public function scopeEmpresa($query, int $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeArea($query, int $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    public function getDescripcionEventoAttribute(): string
    {
        $eventos = [
            'created' => 'Creó',
            'updated' => 'Actualizó',
            'deleted' => 'Eliminó',
            'viewed' => 'Visualizó',
            'exported' => 'Exportó',
            'approved' => 'Aprobó',
            'rejected' => 'Rechazó',
            'sent' => 'Envió',
            'received' => 'Recibió',
            'login' => 'Inició sesión',
            'logout' => 'Cerró sesión',
            'password_changed' => 'Cambió contraseña',
            'permission_changed' => 'Modificó permisos',
        ];

        return $eventos[$this->evento] ?? ucfirst($this->evento);
    }

    public function getColorSeveridadAttribute(): string
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
        return match($this->evento) {
            'created' => 'plus-circle',
            'updated' => 'pencil',
            'deleted' => 'trash',
            'viewed' => 'eye',
            'exported' => 'arrow-down-tray',
            'approved' => 'check-circle',
            'rejected' => 'x-circle',
            'login' => 'arrow-right-on-rectangle',
            'logout' => 'arrow-left-on-rectangle',
            default => 'document',
        };
    }

    public function getCambiosAttribute(): array
    {
        if (!$this->datos_antes || !$this->datos_despues) {
            return [];
        }

        $cambios = [];
        $antes = $this->datos_antes;
        $despues = $this->datos_despues;

        foreach ($despues as $key => $valorNuevo) {
            $valorAnterior = $antes[$key] ?? null;

            if ($valorAnterior != $valorNuevo) {
                $cambios[] = [
                    'campo' => $key,
                    'antes' => $valorAnterior,
                    'despues' => $valorNuevo,
                ];
            }
        }

        return $cambios;
    }
}
