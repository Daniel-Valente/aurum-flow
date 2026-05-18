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
        Schema::create('presupuestos_movimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();

            $table->enum('tipo', [
                'gasto',            // Gasto real (solicitud comprobada)
                'compromiso',       // Compromiso (solicitud autorizada)
                'liberacion',       // Liberación de compromiso (solicitud rechazada/cancelada)
                'ajuste_incremento',// Ajuste manual: aumentar presupuesto
                'ajuste_decremento',// Ajuste manual: reducir presupuesto
                'transferencia_in', // Transferencia recibida de otro presupuesto
                'transferencia_out',// Transferencia enviada a otro presupuesto
            ]);

            $table->decimal('monto', 15, 2);

            $table->decimal('saldo_gastado', 15, 2);
            $table->decimal('saldo_comprometido', 15, 2);
            $table->decimal('saldo_disponible', 15, 2);

            // Origen del movimiento (polimórfico)
            $table->morphs('origen'); // origen_type, origen_id
            // Ejemplo: Solicitud, GastoComprobante, TransferenciaPresupuesto, etc.

            $table->text('concepto')->nullable();
            $table->text('notas')->nullable();

            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_movimiento')->useCurrent();

            $table->timestamps();

            $table->index('presupuesto_id');
            $table->index('tipo');
            $table->index('fecha_movimiento');
            $table->index(['presupuesto_id', 'fecha_movimiento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos_movimientos');
    }
};
