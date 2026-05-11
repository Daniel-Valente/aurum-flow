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
        Schema::create('conceptos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('categoria')->nullable();
            $table->string('descripcion')->nullable();

            // Naturaleza fiscal — propiedad del tipo de gasto, no del rol
            $table->boolean('aplica_iva')->default(true);
            $table->boolean('aplica_ish')->default(false);
            $table->boolean('aplica_ieps')->default(false);

            $table->date('vigencia_desde')->nullable();
            $table->date('vigencia_hasta')->nullable();

            $table->boolean('estatus')->default(true)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conceptos');
    }
};
