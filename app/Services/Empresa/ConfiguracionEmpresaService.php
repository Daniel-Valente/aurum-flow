<?php

namespace App\Services\Empresa;

use App\Models\ConfiguracionEmpresa;
use App\Models\Empresa;
use App\Services\Auditoria\ActividadLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConfiguracionEmpresaService
{
    private const CACHE_TTL = 3600;

    public function __construct(
        private ActividadLogService $actividadLog
    ) {}

    public function obtenerPorEmpresa(?Empresa $empresa): ConfiguracionEmpresa
    {
        if (!$empresa) {
            return $this->obtenerGlobal();
        }

        $cacheKey = "configuracion_empresa_{$empresa->id}";
        $configId = Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($empresa) {
                return ConfiguracionEmpresa::query()
                    ->where('empresa_id', $empresa->id)
                    ->value('id');
            }
        );

        if ($configId) {
            $config = ConfiguracionEmpresa::find($configId);
            if ($config) {
                return $config;
            }
        }

        return $this->obtenerGlobal();
    }

    public function obtenerGlobal(): ConfiguracionEmpresa
    {
        $cacheKey = 'configuracion_empresa_global';

        $configId = Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () {
                return ConfiguracionEmpresa::query()
                    ->whereNull('empresa_id')
                    ->value('id');
            }
        );

        if ($configId) {
            $config = ConfiguracionEmpresa::find($configId);

            if ($config) {
                return $config;
            }
        }

        return $this->crearGlobal();
    }

    public function obtenerOCrear(Empresa $empresa): ConfiguracionEmpresa
    {
        $config = ConfiguracionEmpresa::query()
            ->where('empresa_id', $empresa->id)
            ->first();

        if ($config) {
            return $config;
        }

        return $this->crear($empresa, $this->obtenerValoresDefault());
    }

    public function crear(
        Empresa $empresa,
        array $data,
        $user = null
    ): ConfiguracionEmpresa {
        return DB::transaction(function () use ($empresa, $data, $user) {

            $config = ConfiguracionEmpresa::create([
                ...$data,
                'empresa_id' => $empresa->id,
            ]);

            $this->invalidarCache($empresa->id);
            if ($user) {
                $this->actividadLog->registrar([
                    'user'                => $user,
                    'evento'              => 'created',
                    'modulo'              => 'configuracion_empresa',
                    'entidad'             => $config,
                    'entidad_descripcion' => "Configuración de {$empresa->nombre}",
                    'datos_despues'       => $config->toArray(),
                    'es_sensible'         => true,
                ]);
            }

            return $config;
        });
    }

    public function actualizar(
        Empresa $empresa,
        array $data,
        $user
    ): ConfiguracionEmpresa {
        return DB::transaction(function () use ($empresa, $data, $user) {

            $config = $this->obtenerOCrear($empresa);
            $antes = $config->toArray();
            $config->update($data);

            $this->invalidarCache($empresa->id);
            $this->actividadLog->registrar([
                'user'                => $user,
                'evento'              => 'updated',
                'modulo'              => 'configuracion_empresa',
                'entidad'             => $config,
                'entidad_descripcion' => "Configuración de {$empresa->nombre}",
                'datos_antes'         => $antes,
                'datos_despues'       => $config->fresh()->toArray(),
                'es_sensible'         => true,
            ]);

            return $config->fresh();
        });
    }

    public function resetear(Empresa $empresa, $user): void
    {
        $config = ConfiguracionEmpresa::query()
            ->where('empresa_id', $empresa->id)
            ->first();

        if (!$config) {
            return;
        }

        DB::transaction(function () use ($config, $empresa, $user) {

            $this->actividadLog->registrar([
                'user'                => $user,
                'evento'              => 'deleted',
                'modulo'              => 'configuracion_empresa',
                'entidad'             => $config,
                'entidad_descripcion' => "Configuración de {$empresa->nombre} reseteada",
                'datos_antes'         => $config->toArray(),
                'es_sensible'         => true,
            ]);

            $config->delete();

            $this->invalidarCache($empresa->id);
        });
    }

    public function obtenerValoresDefault(): array
    {
        $global = $this->obtenerGlobal();

        return [
            'dias_habiles_comprobacion'     => $global->dias_habiles_comprobacion,
            'cfdi_dias_antes_permitidos'    => $global->cfdi_dias_antes_permitidos,
            'cfdi_dias_despues_permitidos'  => $global->cfdi_dias_despues_permitidos,
            'validar_rfc_receptor'          => $global->validar_rfc_receptor,
            'propina_auto_aprueba'          => $global->propina_auto_aprueba,
            'gasto_compartido_auto_aprueba' => $global->gasto_compartido_auto_aprueba,
            'gasto_cliente_auto_aprueba'    => $global->gasto_cliente_auto_aprueba,
            'validador_tickets'             => $global->validador_tickets,
            'rfc_empresa'                   => null,
            'moneda'                        => null,
            'pais'                          => null,
        ];
    }

    public function stats(Empresa $empresa): array
    {
        $config = $this->obtenerPorEmpresa($empresa);
        $global = $this->obtenerGlobal();

        return [
            'tieneConfiguracionPropia' => $config->empresa_id === $empresa->id,
            'usaConfiguracionGlobal'   => is_null($config->empresa_id),
            'dias_habiles_comprobacion' => [
                'valor'      => $config->dias_habiles_comprobacion,
                'esGlobal'   => is_null($config->empresa_id),
                'default'    => $global->dias_habiles_comprobacion,
            ],
            'cfdi_dias_antes_permitidos' => [
                'valor'      => $config->cfdi_dias_antes_permitidos,
                'esGlobal'   => is_null($config->empresa_id),
                'default'    => $global->cfdi_dias_antes_permitidos,
            ],
            'cfdi_dias_despues_permitidos' => [
                'valor'      => $config->cfdi_dias_despues_permitidos,
                'esGlobal'   => is_null($config->empresa_id),
                'default'    => $global->cfdi_dias_despues_permitidos,
            ],
            'validar_rfc_receptor' => [
                'valor'      => $config->validar_rfc_receptor,
                'esGlobal'   => is_null($config->empresa_id),
                'default'    => $global->validar_rfc_receptor,
            ],
            'propina_auto_aprueba' => [
                'valor'      => $config->propina_auto_aprueba,
                'esGlobal'   => is_null($config->empresa_id),
                'default'    => $global->propina_auto_aprueba,
            ],
        ];
    }

    public function invalidarCache(?int $empresaId = null): void
    {
        if ($empresaId) {
            Cache::forget("configuracion_empresa_{$empresaId}");
        }

        Cache::forget('configuracion_empresa_global');
    }

    private function crearGlobal(): ConfiguracionEmpresa
    {
        return ConfiguracionEmpresa::create([
            'empresa_id'                    => null,
            'dias_habiles_comprobacion'     => 5,
            'cfdi_dias_antes_permitidos'    => 3,
            'cfdi_dias_despues_permitidos'  => 10,
            'validar_rfc_receptor'          => true,
            'propina_auto_aprueba'          => true,
            'gasto_compartido_auto_aprueba' => true,
            'gasto_cliente_auto_aprueba'    => true,
            'validador_tickets'             => 'finanzas',
            'rfc_empresa'                   => null,
            'moneda'                        => null,
            'pais'                          => null,
        ]);
    }
}
