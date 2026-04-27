<?php

namespace App\Services\Gasto;

use App\Models\PoliticaGasto;
use App\Models\PoliticaGastoAuditoria;
use App\Models\PoliticaGastoVersion;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class PoliticaGastoService
{
    public function create(array $data, $user): PoliticaGasto
    {
        $exists = PoliticaGasto::where('role_id',     $data['role_id'])
            ->where('concepto_id',  $data['concepto_id'])
            ->where('tipo_limite',  $data['tipo_limite'])
            ->vigente()
            ->exists();

        if ($exists) {
            throw new \Exception('Ya existe una política vigente para este rol y concepto');
        }

        return DB::transaction(function () use ($data, $user) {
            // ✅ Campos explícitos — sin ...$data para evitar mass assignment no controlado
            $politica = PoliticaGasto::create([
                'role_id'         => $data['role_id'],
                'concepto_id'     => $data['concepto_id'],
                'tipo_limite'     => $data['tipo_limite'],
                'monto_max'       => $data['monto_max'],
                'permite_excepcion' => $data['permite_excepcion'] ?? false,
                'vigencia_desde'  => $data['vigencia_desde'] ?? null,
                'vigencia_hasta'  => $data['vigencia_hasta'] ?? null,
            ]);

            $version = PoliticaGastoVersion::create([
                'politica_id'     => $politica->id,
                'role_id'         => $data['role_id'],
                'concepto_id'     => $data['concepto_id'],
                'tipo_limite'     => $data['tipo_limite'],
                'monto_max'       => $data['monto_max'],
                'permite_excepcion' => $data['permite_excepcion'] ?? false,
                'vigencia_desde'  => $data['vigencia_desde'] ?? null,
                'vigencia_hasta'  => $data['vigencia_hasta'] ?? null,
                'creado_por'      => $user->id,
                'estatus'         => 'Aprobada',
                'motivo'          => 'Creación inicial',
            ]);

            PoliticaGastoAuditoria::create([
                'politica_id'   => $politica->id,
                'version_id'    => $version->id,
                'evento'        => 'created',
                'actor_id'      => $user->id,
                'datos_despues' => $politica->toArray(),
            ]);

            return $politica;
        });
    }

    public function update(PoliticaGasto $politica, array $data, $user): PoliticaGasto
    {
        return DB::transaction(function () use ($politica, $data, $user) {
            $antes = $politica->toArray();

            // ✅ Campos explícitos en update
            $politica->update([
                'role_id'           => $data['role_id'],
                'concepto_id'       => $data['concepto_id'],
                'tipo_limite'       => $data['tipo_limite'],
                'monto_max'         => $data['monto_max'],
                'permite_excepcion' => $data['permite_excepcion'] ?? $politica->permite_excepcion,
                'vigencia_desde'    => $data['vigencia_desde'] ?? null,
                'vigencia_hasta'    => $data['vigencia_hasta'] ?? null,
            ]);

            $version = PoliticaGastoVersion::create([
                'politica_id'       => $politica->id,
                'role_id'           => $data['role_id'],
                'concepto_id'       => $data['concepto_id'],
                'tipo_limite'       => $data['tipo_limite'],
                'monto_max'         => $data['monto_max'],
                'permite_excepcion' => $data['permite_excepcion'] ?? $politica->permite_excepcion,
                'vigencia_desde'    => $data['vigencia_desde'] ?? null,
                'vigencia_hasta'    => $data['vigencia_hasta'] ?? null,
                'creado_por'        => $user->id,
                'estatus'           => 'Aprobada',
                'motivo'            => $data['motivo'] ?? 'Actualización',
            ]);

            PoliticaGastoAuditoria::create([
                'politica_id'   => $politica->id,
                'version_id'    => $version->id,
                'evento'        => 'updated',
                'actor_id'      => $user->id,
                'datos_antes'   => $antes,
                'datos_despues' => $politica->fresh()->toArray(),
            ]);

            return $politica;
        });
    }

    public function delete(PoliticaGasto $politica, $user): bool
    {
        return DB::transaction(function () use ($politica, $user) {
            $politica->delete();

            PoliticaGastoAuditoria::create([
                'politica_id' => $politica->id,
                'evento'      => 'deleted',
                'actor_id'    => $user->id,
            ]);

            return true;
        });
    }

    /**
     * Devuelve la versión de política vigente más reciente para un rol/concepto/fecha.
     * Usado por ValidadorGastosService para validaciones individuales.
     */
    public function getPoliticaAplicable(int $roleId, int $conceptoId, $fecha): ?PoliticaGastoVersion
    {
        return PoliticaGastoVersion::where('role_id',    $roleId)
            ->where('concepto_id', $conceptoId)
            ->where('estatus',     'Aprobada')
            ->where(fn($q) =>
                $q->whereNull('vigencia_desde')
                  ->orWhere('vigencia_desde', '<=', $fecha)
            )
            ->where(fn($q) =>
                $q->whereNull('vigencia_hasta')
                  ->orWhere('vigencia_hasta', '>=', $fecha)
            )
            ->latest()
            ->first();
    }

    /**
     * Carga en una sola query las políticas para múltiples conceptos.
     * Usado por ValidadorGastosService::validarSolicitud para evitar N+1.
     */
    public function getPoliticasBulk(int $roleId, array $conceptoIds, $fecha): \Illuminate\Support\Collection
    {
        return PoliticaGastoVersion::where('role_id', $roleId)
            ->whereIn('concepto_id', $conceptoIds)
            ->where('estatus', 'Aprobada')
            ->where(fn($q) =>
                $q->whereNull('vigencia_desde')
                  ->orWhere('vigencia_desde', '<=', $fecha)
            )
            ->where(fn($q) =>
                $q->whereNull('vigencia_hasta')
                  ->orWhere('vigencia_hasta', '>=', $fecha)
            )
            ->latest()
            ->get()
            ->keyBy('concepto_id'); // O(1) lookup por concepto en el loop
    }
}
