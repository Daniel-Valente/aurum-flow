<?php

namespace App\Models;

use Cache;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionEmpresa extends Model
{
    protected $table = 'configuracion_empresa';

    protected $fillable = [
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
        'pais'
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

    private const CACHE_KEY = 'configuracion_empresa';
    private const CACHE_TTL = 3600;

    public static function actual(): self
    {
        $id = Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            function () {
                return self::query()->value('id')
                    ?? self::create([
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
                    ])->id;
            }
        );

        return self::findOrFail($id);
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
