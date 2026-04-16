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
        Schema::create('politicas_gastos_auditoria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('politica_id')->nullable()
                ->constrained('politicas_gastos')
                ->nullOnDelete();

            $table->foreignId('version_id')->nullable()
                ->constrained('politicas_gastos_versiones')
                ->nullOnDelete();

            $table->string('evento'); //created | updated | deleted | approved

            $table->foreignId('actor_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('origen')->default('sistema');
            // sistema | api | manual

            $table->jsonb('datos_antes')->nullable();
            $table->jsonb('datos_despues')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['politica_id']);
            $table->index(['actor_id']);
            $table->index(['evento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('politicas_gastos_auditoria');
    }
};
