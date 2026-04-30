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
        Schema::create('politicas_gastos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')->constrained();
            $table->foreignId('concepto_id')->constrained();

            $table->decimal('monto_max', 12, 2);

            $table->string('tipo_limite')->default('Diario');
            //Diario | Viaje

            $table->boolean('permite_excepcion')->default(false);

            $table->date('vigencia_desde')->nullable();
            $table->date('vigencia_hasta')->nullable();

            $table->boolean('estatus')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['role_id', 'concepto_id']);

            $table->unique([
                'role_id',
                'concepto_id',
                'tipo_limite',
                'vigencia_desde'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('politicas_gastos');
    }
};
