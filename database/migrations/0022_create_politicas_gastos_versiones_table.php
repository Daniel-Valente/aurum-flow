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
        Schema::create('politicas_gastos_versiones', function (Blueprint $table) {
            $table->id();

            // Referencia a la política padre
            $table->foreignId('politica_id')
                ->constrained('politicas_gastos')
                ->cascadeOnDelete();

            // --- Snapshot completo al momento de la versión ---
            $table->foreignId('role_id')->constrained();
            $table->foreignId('concepto_id')->constrained();

            $table->decimal('monto_max', 12, 2);

            // Diario | Viaje | Evento
            $table->string('tipo_limite', 20)->default('Diario');

            // Tramos documentales (snapshot)
            $table->decimal('monto_libre', 12, 2)->nullable();
            $table->decimal('monto_comprobante', 12, 2)->nullable();
            $table->decimal('monto_factura', 12, 2)->nullable();

            $table->boolean('valida_sat')->default(false);
            $table->boolean('acumulable_dia')->default(true);
            $table->boolean('permite_excepcion')->default(false);

            $table->boolean('permite_propina')->default(false);
            $table->decimal('propina_max_porcentaje', 5, 2)->nullable();

            $table->date('vigencia_desde')->nullable();
            $table->date('vigencia_hasta')->nullable();

            // --- Metadatos de la versión ---
            $table->string('motivo')->nullable();

            $table->foreignId('creado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('aprobado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            // Borrador | Aprobada | Inactiva
            $table->string('estatus', 20)->default('Aprobada');

            $table->timestamps();

            // Índice principal para las consultas del validador
            $table->index(['role_id', 'concepto_id', 'estatus']);
            $table->index(['politica_id', 'estatus']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('politicas_gastos_versiones');
    }
};
