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
        Schema::create('presupuestos_solicitudes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();

            $table->decimal('monto_comprometido', 15, 2)->default(0);
            $table->decimal('monto_consumido', 15, 2)->default(0);

            $table->string('estatus', 20)->default('comprometido');

            $table->timestamps();

            $table->unique(['presupuesto_id', 'solicitud_id']);
            $table->index('solicitud_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos_solicitudes');
    }
};
