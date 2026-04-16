<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('nombre_completo');
            $table->boolean('must_change_password')->default(true);

            $table->string('puesto')->nullable();
            $table->string('area_departamento')->nullable();

            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('centro_costo_id')->nullable()->constrained('centros_costos')->nullOnDelete();

            $table->string('rfc', 13)->nullable()->index();
            $table->string('curp', 18)->nullable();

            $table->string('numero_nomina')->nullable()->unique();

            $table->string('banco_nomina')->nullable();
            $table->string('cuenta_nomina')->nullable();
            $table->string('clabe_nomina')->nullable();

            $table->string('nss')->nullable();
            $table->date('fecha_ingreso')->nullable();

            $table->string('telefono')->nullable();

            $table->boolean('estatus')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
