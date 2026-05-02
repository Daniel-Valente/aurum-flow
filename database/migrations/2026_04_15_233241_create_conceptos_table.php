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

            // Diario | Evento | Viaje — ritmo de aplicación del concepto
            $table->string('tipo_aplicacion', 20)->default('Diario')->index();

            $table->integer('orden')->default(0);

            // Naturaleza fiscal — propiedad del tipo de gasto, no del rol
            // El hospedaje genera IVA acreditable; viáticos de alimentación pueden ser exentos
            $table->boolean('aplica_iva')->default(true);

            // Precio promedio de mercado (informativo para el validador y reportes)
            $table->decimal('tope_referencia', 10, 2)->nullable();

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
