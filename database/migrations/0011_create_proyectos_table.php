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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo')->unique();
            $table->string('nombre');

            $table->string('cliente')->nullable();
            $table->string('tipo')->default('Proyecto');

            $table->text('descripcion')->nullable();
            $table->string('region')->nullable();

            $table->string('estado_operativo')->default('Draft');

            $table->foreignId('centro_costo_id')->nullable()->constrained('centros_costos');
            $table->foreignId('responsable_id')->nullable()->constrained('empleados')->nullOnDelete();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();

            $table->decimal('presupuesto_total', 15, 2)->nullable();
            $table->decimal('presupuesto_gastado', 15, 2)->default(0);

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->string('pais')->nullable();
            $table->string('estado')->nullable();
            $table->string('ciudad')->nullable();

            $table->boolean('estatus')->default(true);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
