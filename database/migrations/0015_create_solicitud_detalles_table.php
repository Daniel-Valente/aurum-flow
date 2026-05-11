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
        Schema::create('solicitud_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();
            $table->foreignId('concepto_id')->constrained();

            $table->decimal('monto_estimado', 12, 2);

            $table->text('justificacion_exceso')->nullable();

            $table->timestamps();

            $table->index(['solicitud_id', 'concepto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_detalles');
    }
};
