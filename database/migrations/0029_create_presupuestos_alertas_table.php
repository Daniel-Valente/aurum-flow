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
        Schema::create('presupuestos_alertas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();

            $table->enum('tipo', [
                'alerta',       // 80% consumido (warning)
                'critico',      // 95% consumido (danger)
                'agotado',      // 100% consumido
                'excedido',     // >100% consumido (sobregiro)
                'proximo_vencer', // 7 días para vencer
            ]);

            $table->enum('severidad', ['info', 'warning', 'danger', 'critical'])
                ->default('info');

            $table->string('titulo', 255);
            $table->text('mensaje');

            $table->decimal('porcentaje_consumido', 5, 2);
            $table->decimal('monto_disponible', 15, 2);
            $table->integer('dias_restantes')->nullable();

            $table->boolean('notificado')->default(false);
            $table->timestamp('notificado_en')->nullable();
            $table->boolean('resuelto')->default(false);
            $table->timestamp('resuelto_en')->nullable();
            $table->text('resolucion')->nullable();

            $table->timestamps();

            $table->index('presupuesto_id');
            $table->index('tipo');
            $table->index('severidad');
            $table->index('notificado');
            $table->index('resuelto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos_alertas');
    }
};
