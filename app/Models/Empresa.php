<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes;

    protected $table = 'empresas';

    protected $fillable = [
        'codigo',
        'nombre',
        'nombre_comercial',
        'rfc',
        'domicilio_fiscal',
        'ciudad',
        'estado',
        'codigo_postal',
        'pais',
        'telefono',
        'email',
        'sitio_web',
        'moneda',
        'timezone',
        'logo_path',
        'activo',
        'notas',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ===== RELACIONES =====
    public function configuracion()
    {
        return $this->hasOne(ConfiguracionEmpresa::class);
    }

    public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    // ===== MÉTODOS HELPER =====
    public function obtenerConfiguracion(): ConfiguracionEmpresa
    {
        return $this->configuracion ?? ConfiguracionEmpresa::obtenerGlobal();
    }

    public function getRfc(): string
    {
        return strtoupper($this->rfc);
    }

    public function getMoneda(): string
    {
        return $this->configuracion?->moneda ?? $this->moneda ?? 'MXN';
    }

    public function getPais(): string
    {
        return $this->configuracion?->pais ?? $this->pais ?? 'MX';
    }
}
