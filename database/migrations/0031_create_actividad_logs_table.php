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
        Schema::create('actividad_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 255)->nullable(); // Guardar nombre por si se elimina el user
            $table->string('user_email', 255)->nullable();
            $table->string('user_role', 100)->nullable();

            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();

            $table->string('evento', 100); // created, updated, deleted, viewed, exported, etc.
            $table->string('modulo', 100); // solicitudes, gastos, presupuestos, usuarios, etc.

            $table->morphs('entidad'); // entidad_type, entidad_id
            $table->string('entidad_descripcion', 500)->nullable(); // Descripción legible

            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();
            $table->json('metadatos')->nullable(); // Información adicional contextual

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id', 255)->nullable();

            $table->string('ciudad', 100)->nullable();
            $table->string('pais', 100)->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();

            $table->enum('severidad', ['info', 'warning', 'danger', 'critical'])->default('info');
            $table->boolean('es_sensible')->default(false); // Acciones sensibles (cambios financieros, etc.)

            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('evento');
            $table->index('modulo');
            $table->index('created_at');
            $table->index('severidad');
            $table->index('es_sensible');

            $table->index(['user_id', 'created_at']);
            $table->index(['empresa_id', 'created_at']);
            $table->index(['modulo', 'evento', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_logs');
    }
};
