<?php

namespace App\Services\Empleado;

use App\Models\Empleado;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmpleadoService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $tempPassword = Str::random(10);

            $user = User::create([
                'name' => $data['nombre_completo'],
                'email' => $data['email'],
                'password' => Hash::make($tempPassword),
                'must_change_password' => true
            ]);

            $user->assignRole($data['rol']);

            $empleado = Empleado::create([
                'user_id' => $user->id,
                'nombre_completo' => $data['nombre_completo'],
                'puesto' => $data['puesto'],
                'area_departamento' => $data['area_departamento'],
                'area_id' => $data['area_id'],
                'centro_costo_id' => $data['centro_costo_id'],
                'rfc' => $data['rfc'],
                'curp' => $data['curp'],
                'numero_nomina' => $data['numero_nomina'],
                'banco_nomina' => $data['banco_nomina'],
                'cuenta_nomina' => $data['cuenta_nomina'],
                'clabe_nomina' => $data['clabe_nomina'],
                'nss' => $data['nss'],
                'fecha_ingreso' => $data['fecha_ingreso'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'estatus' => true
            ]);

            return [
                'empleado' => $empleado->load('user'),
                'temp_password' => $tempPassword
            ];
        });
    }

    public function update(Empleado $empleado, array $data)
    {
        return DB::transaction(function () use ($empleado, $data) {

            $empleado->update([
                'nombre_completo' => $data['nombre_completo'],
                'puesto' => $data['puesto'],
                'area_departamento' => $data['area_departamento'],
                'area_id' => $data['area_id'],
                'centro_costo_id' => $data['centro_costo_id'],
                'rfc' => $data['rfc'],
                'curp' => $data['curp'],
                'numero_nomina' => $data['numero_nomina'],
                'banco_nomina' => $data['banco_nomina'],
                'cuenta_nomina' => $data['cuenta_nomina'],
                'clabe_nomina' => $data['clabe_nomina'],
                'nss' => $data['nss'],
                'fecha_ingreso' => $data['fecha_ingreso'] ?? null,
                'telefono' => $data['telefono'] ?? null,
            ]);

            $empleado->user?->update([
                'name' => $data['nombre_completo'],
                'email' => $data['email'],
            ]);

            if (!empty($data['rol'])) {
                $empleado->user?->syncRoles([$data['rol']]);
            }

            return $empleado->load('user');
        });
    }

    public function delete(Empleado $empleado): bool
    {
        return DB::transaction(function () use ($empleado) {

            $empleado->update([
                'estatus' => false
            ]);

            return true;
        });
    }
}
