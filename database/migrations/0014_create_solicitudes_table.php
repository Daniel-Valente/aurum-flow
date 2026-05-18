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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();

            $table->string('folio')->unique();

            $table->foreignId('empleado_id')->constrained();
            $table->foreignId('area_id')->nullable()->constrained('areas');
            $table->foreignId('proyecto_id')->nullable()->constrained();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('presupuesto_id')->nullable()->constrained('presupuestos')->nullOnDelete();

            $table->timestamp('fecha_solicitud')->useCurrent();

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->text('motivo')->nullable();

            $table->decimal('monto_total', 12, 2)->default(0);

            $table->text('motivo_rechazo')->nullable();
            $table->text('motivo_cancelacion')->nullable();

            $table->string('estatus')->default('Borrador')->index();
            // Borrador | Pendiente | Autorizado | Rechazado | Comprobado | Cancelado

            $table->timestamps();
            $table->softDeletes();

            $table->index('empresa_id');
            $table->index('presupuesto_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
