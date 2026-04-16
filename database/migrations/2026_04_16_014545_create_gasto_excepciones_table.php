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
        Schema::create('gasto_excepciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gasto_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('nivel_actual')->default('GerentePendiente');
            $table->string('estatus')->default('Pendiente');

            $table->foreignId('aprobado_gerente_por')->nullable()->constrained('empleados')->nullOnDelete();
            $table->foreignId('aprobado_admin_por')->nullable()->constrained('empleados')->nullOnDelete();

            $table->string('motivo')->nullable();

            $table->timestamp('aprobado_gerente_at')->nullable();
            $table->timestamp('aprobado_admin_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gasto_excepciones');
    }
};
