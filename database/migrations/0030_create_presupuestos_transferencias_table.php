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
        Schema::create('presupuestos_transferencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('presupuesto_origen_id')->constrained('presupuestos')->cascadeOnDelete();
            $table->foreignId('presupuesto_destino_id')->constrained('presupuestos')->cascadeOnDelete();

            $table->decimal('monto', 15, 2);

            $table->text('motivo');
            $table->text('notas')->nullable();

            $table->enum('estatus', ['pendiente', 'aprobada', 'rechazada', 'cancelada'])
                ->default('pendiente');

            $table->foreignId('solicitado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamp('solicitado_en')->useCurrent();

            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('aprobado_en')->nullable();
            $table->text('comentario_aprobacion')->nullable();

            $table->timestamps();

            $table->index('presupuesto_origen_id');
            $table->index('presupuesto_destino_id');
            $table->index('estatus');
            $table->index('solicitado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos_transferencias');
    }
};
