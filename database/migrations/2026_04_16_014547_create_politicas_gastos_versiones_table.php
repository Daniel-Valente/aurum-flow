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
        Schema::create('politicas_gastos_versiones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('politica_id')->constrained('politicas_gastos')->cascadeOnDelete();

            $table->foreignId('role_id')->constrained();
            $table->foreignId('concepto_id')->constrained();

            $table->decimal('monto_max', 12, 2);

            $table->string('tipo_limite')->default('Diario');
            // Diario | Viaje | Evento

            $table->boolean('permite_excepcion')->default(false);

            $table->date('vigencia_desde')->nullable();
            $table->date('vigencia_hasta')->nullable();

            $table->string('motivo')->nullable();

            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->string('estatus')->default('Aprobada');
            // Borrador | Aprobada | Inactiva

            $table->timestamps();

            $table->index(['role_id', 'concepto_id', 'estatus']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('politicas_gastos_versiones');
    }
};
