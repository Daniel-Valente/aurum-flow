<?php

namespace App\Models;

use Cache;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionEmpresa extends Model
{
    protected $table = 'configuracion_empresa';

    protected $fillable = [
        'empresa_id',
        'dias_habiles_comprobacion',
        'cfdi_dias_antes_permitidos',
        'cfdi_dias_despues_permitidos',
        'rfc_empresa',
        'validar_rfc_receptor',
        'propina_auto_aprueba',
        'gasto_compartido_auto_aprueba',
        'gasto_cliente_auto_aprueba',
        'validador_tickets',
        'moneda',
        'pais',
    ];

    protected $casts = [
        'dias_habiles_comprobacion'     => 'integer',
        'cfdi_dias_antes_permitidos'    => 'integer',
        'cfdi_dias_despues_permitidos'  => 'integer',
        'validar_rfc_receptor'          => 'boolean',
        'propina_auto_aprueba'          => 'boolean',
        'gasto_compartido_auto_aprueba' => 'boolean',
        'gasto_cliente_auto_aprueba'    => 'boolean',
    ];

    private const CACHE_TTL = 3600;

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    private static function getCacheKey(?int $empresaId = null): string
    {
        return $empresaId
            ? "configuracion_empresa_{$empresaId}"
            : 'configuracion_empresa_global';
    }

    public static function obtenerGlobal(): self
    {
        $configId = Cache::remember(
            self::getCacheKey(),
            self::CACHE_TTL,
            function () {
                return self::query()
                    ->whereNull('empresa_id')
                    ->value('id');
            }
        );

        if ($configId) {
            $config = self::find($configId);
            if ($config) {
                return $config;
            }
        }

        return self::crearGlobal();
    }

    public static function obtenerPorEmpresa(?Empresa $empresa): self
    {
        if (!$empresa) {
            return self::obtenerGlobal();
        }

        $configId = Cache::remember(
            self::getCacheKey($empresa->id),
            self::CACHE_TTL,
            function () use ($empresa) {
                return self::query()
                    ->where('empresa_id', $empresa->id)
                    ->value('id');
            }
        );

        if ($configId) {
            $config = self::find($configId);

            if ($config) {
                return $config;
            }
        }

        return self::obtenerGlobal();
    }

    public static function obtenerPorEmpresaId(?int $empresaId): self
    {
        if (!$empresaId) {
            return self::obtenerGlobal();
        }

        $configId = Cache::remember(
            self::getCacheKey($empresaId),
            self::CACHE_TTL,
            function () use ($empresaId) {
                return self::query()
                    ->where('empresa_id', $empresaId)
                    ->value('id');
            }
        );

        if ($configId) {
            $config = self::find($configId);

            if ($config) {
                return $config;
            }
        }

        return self::obtenerGlobal();
    }

    private static function crearGlobal(): self
    {
        return self::create([
            'empresa_id'                    => null,
            'dias_habiles_comprobacion'     => 5,
            'cfdi_dias_antes_permitidos'    => 3,
            'cfdi_dias_despues_permitidos'  => 10,
            'validar_rfc_receptor'          => true,
            'propina_auto_aprueba'          => true,
            'gasto_compartido_auto_aprueba' => true,
            'gasto_cliente_auto_aprueba'    => true,
            'validador_tickets'             => 'finanzas',
            'moneda'                        => 'MXN',
            'pais'                          => 'MX',
        ]);
    }

    public static function invalidarCache(?int $empresaId = null): void
    {
        Cache::forget(self::getCacheKey($empresaId));
    }

    public function invalidarCachePropia(): void
    {
        self::invalidarCache($this->empresa_id);
    }

    protected static function booted(): void
    {
        static::saved(function ($model) {
            $model->invalidarCachePropia();

            if ($model->empresa_id === null) {
                self::invalidarCache();
            }
        });

        static::deleted(function ($model) {
            $model->invalidarCachePropia();

            if ($model->empresa_id === null) {
                self::invalidarCache();
            }
        });
    }
}
