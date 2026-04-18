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
        Schema::create('gastos_auditoria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gasto_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('excepcion_id')->nullable()->constrained('gastos_excepciones')->nullOnDelete();

            $table->string('evento');
            // creado | validado | excepcion_creada | aprobado | rechazado

            $table->foreignId('actor_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('origen')->default('sistema');
            // sistema | api | manual

            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['gasto_id']);
            $table->index(['excepcion_id']);
            $table->index(['actor_id']);
            $table->index(['evento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_auditoria');
    }
};
