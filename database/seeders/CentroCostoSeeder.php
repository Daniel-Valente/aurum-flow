<?php

namespace Database\Seeders;

use App\Models\CentroCosto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class CentroCostoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        CentroCosto::firstOrCreate(
            ['codigo' => 'CC-001'],
            ['nombre' => 'Oficina Central', 'estatus' => true]
        );
    }
}
