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
        Schema::create('comprobaciones_tarjeta', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->unique();

            $table->foreignId('empleado_id')->constrained('empleados')->restrictOnDelete();
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();
            $table->foreignId('solicitud_id')->nullable()->constrained('solicitudes')->nullOnDelete();

            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('descripcion', 500)->nullable();
            $table->decimal('monto_total', 12, 2)->default(0);

            $table->boolean('es_extension')->default(false);

            $table->string('estatus', 20)->default('abierta'); //abierta | en_revision | conciliada | rechazada
            $table->text('motivo_rechazo')->nullable();

            $table->foreignId('conciliado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('conciliado_en')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('empleado_id');
            $table->index('proyecto_id');
            $table->index('solicitud_id');
            $table->index('estatus');
            $table->index(['empleado_id', 'estatus']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobaciones_tarjeta');
    }
};
