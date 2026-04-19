<?php

namespace App\Services\PoliticaGasto;

use App\Models\PoliticaGasto;
use App\Models\PoliticaGastoAuditoria;
use App\Models\PoliticaGastoVersion;
use Illuminate\Support\Facades\DB;

class PoliticaGastoService
{
    public function create(array $data, $user)
    {
        $exists = PoliticaGasto::where('role_id', $data['role_id'])
            ->where('concepto_id', $data['concepto_id'])
            ->where('tipo_limite', $data['tipo_limite'])
            ->vigente()
            ->exists();

        if ($exists) {
            throw new \Exception('Ya existe una política vigente para este rol y concepto');
        }

        $politica = PoliticaGasto::create($data);
        $version = PoliticaGastoVersion::create([
            'politica_id' => $politica->id,
            ...$data,
            'creado_por' => $user->id,
            'estatus' => 'Aprobada',
            'motivo' => 'Creación inicial'
        ]);

        PoliticaGastoAuditoria::create([
            'politica_id' => $politica->id,
            'version_id' => $version->id,
            'evento' => 'created',
            'actor_id' => $user->id,
            'datos_despues' => $politica->toArray(),
        ]);

        return $politica;
    }

    public function update(PoliticaGasto $politica, array $data, $user)
    {
        return DB::transaction(function () use ($politica, $data, $user) {

            $antes = $politica->toArray();

            // 🔥 actualizar base
            $politica->update($data);

            // 🔥 nueva versión
            $version = PoliticaGastoVersion::create([
                'politica_id' => $politica->id,
                ...$data,
                'creado_por' => $user->id,
                'estatus' => 'Aprobada',
                'motivo' => 'Actualización'
            ]);

            // 🔥 auditoría
            PoliticaGastoAuditoria::create([
                'politica_id' => $politica->id,
                'version_id' => $version->id,
                'evento' => 'updated',
                'actor_id' => $user->id,
                'datos_antes' => $antes,
                'datos_despues' => $politica->toArray(),
            ]);

            return $politica;
        });
    }

    public function delete(PoliticaGasto $politica, $user)
    {
        return DB::transaction(function () use ($politica, $user) {

            $politica->delete();

            PoliticaGastoAuditoria::create([
                'politica_id' => $politica->id,
                'evento' => 'deleted',
                'actor_id' => $user->id,
            ]);

            return true;
        });
    }

    public function getPoliticaAplicable($roleId, $conceptoId, $fecha)
    {
        return PoliticaGastoVersion::where('role_id', $roleId)
            ->where('concepto_id', $conceptoId)
            ->where('estatus', 'Aprobada')
            ->where(function ($q) use ($fecha) {
                $q->whereNull('vigencia_desde')
                ->orWhere('vigencia_desde', '<=', $fecha);
            })
            ->where(function ($q) use ($fecha) {
                $q->whereNull('vigencia_hasta')
                ->orWhere('vigencia_hasta', '>=', $fecha);
            })
            ->latest()
            ->first();
    }
}
