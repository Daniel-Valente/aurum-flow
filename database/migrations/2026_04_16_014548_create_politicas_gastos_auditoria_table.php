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

            $table->foreignId('politica_id')
                ->nullable()
                ->constrained('politicas_gastos')
                ->nullOnDelete();

            $table->foreignId('version_id')
                ->nullable()
                ->constrained('politicas_gastos_versiones')
                ->nullOnDelete();

            // created | updated | deleted | status_changed | approved
            $table->string('evento', 30);

            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // sistema | api | manual
            $table->string('origen', 20)->default('manual');

            $table->jsonb('datos_antes')->nullable();
            $table->jsonb('datos_despues')->nullable();

            // Solo created_at — esta tabla es append-only
            $table->timestamp('created_at')->useCurrent();

            $table->index('politica_id');
            $table->index('actor_id');
            $table->index('evento');
            $table->index('created_at');
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
