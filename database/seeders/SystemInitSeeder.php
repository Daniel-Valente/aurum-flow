<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class SystemInitSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $gerente = Role::firstOrCreate(['name' => 'gerente']);
        $operativo = Role::firstOrCreate(['name' => 'operativo']);

        $uAdmin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );

        $uGerente = User::firstOrCreate(
            ['email' => 'gerente@demo.com'],
            ['name' => 'Gerente', 'password' => Hash::make('password')]
        );

        $uOperativo = User::firstOrCreate(
            ['email' => 'operativo@demo.com'],
            ['name' => 'Operativo', 'password' => Hash::make('password')]
        );

        $uAdmin->assignRole($admin);
        $uGerente->assignRole($gerente);
        $uOperativo->assignRole($operativo);
    }
}
