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
        Schema::create('politicas_gastos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')->constrained();
            $table->foreignId('concepto_id')->constrained();

            // Tope absoluto autorizado en este tipo de límite
            $table->decimal('monto_max', 12, 2);

            // Diario | Viaje | Evento
            $table->string('tipo_limite', 20)->default('Diario');

            // --- Tramos documentales ---
            // Hasta aquí, sin documento requerido (libre)
            $table->decimal('monto_libre', 12, 2)->nullable();
            // De aquí en adelante, ticket / recibo es suficiente
            $table->decimal('monto_comprobante', 12, 2)->nullable();
            // De aquí en adelante, CFDI (XML + PDF) es obligatorio
            $table->decimal('monto_factura', 12, 2)->nullable();

            // Al recibir un CFDI con UUID, consultarlo ante el SAT automáticamente
            $table->boolean('valida_sat')->default(false);

            // El concepto puede registrarse varias veces el mismo día (por este rol)
            $table->boolean('acumulable_dia')->default(true);

            // Se puede superar monto_max con justificación aprobada
            $table->boolean('permite_excepcion')->default(false);

            $table->boolean('permite_propina')->default(false);

            $table->decimal('propina_max_porcentaje', 5, 2)->nullable();

            $table->date('vigencia_desde')->nullable();
            $table->date('vigencia_hasta')->nullable();

            $table->boolean('estatus')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['role_id', 'concepto_id']);

            // Una sola política vigente por rol + concepto + tipo de límite + inicio de vigencia
            $table->unique([
                'role_id',
                'concepto_id',
                'tipo_limite',
                'vigencia_desde',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('politicas_gastos');
    }
};
