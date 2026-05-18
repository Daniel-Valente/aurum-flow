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
        Schema::create('gasto_comprobantes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gasto_id')->constrained()->cascadeOnDelete();

            $table->string('archivo');
            $table->string('archivo_pdf')->nullable();
            $table->string('tipo')->nullable(); // factura | ticket | pdf
            $table->string('uuid')->nullable(); // CFDI si aplica

            $table->string('validacion_manual')->nullable()->default('pendiente'); // pendiente | aprobado | rechazado
            $table->foreignId('validado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comentario_validacion')->nullable();
            $table->timestamp('validado_en')->nullable();

            $table->decimal('monto', 12, 2)->nullable();
            $table->date('fecha_gasto')->nullable();

            $table->boolean('cfdi_compartido')->default(false);
            $table->foreignId('comprobante_origen_id')->nullable()->constrained('gasto_comprobantes')->nullOnDelete();
            $table->decimal('monto_total_cfdi', 10, 2)->nullable();

            $table->decimal('subtotal', 12, 2)->nullable();
            $table->decimal('descuento', 12, 2)->nullable();
            $table->decimal('iva', 12, 2)->nullable();
            $table->decimal('ieps', 12, 2)->nullable();
            $table->decimal('ish', 12, 2)->nullable();

            $table->decimal('tasa_iva', 5, 4)->nullable();
            $table->decimal('tasa_ieps', 5, 4)->nullable();
            $table->decimal('tasa_ish', 5, 4)->nullable();

            $table->string('sat_status')->nullable()->index(); // pendiente | vigente | cancelado | no_encontrado
            $table->timestamp('sat_checked_at')->nullable();
            $table->integer('sat_attempts')->default(0);
            $table->json('meta_cfdi')->nullable();
            $table->text('sat_last_error')->nullable();

            $table->foreignId('subido_por')->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_subida')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gasto_comprobantes');
    }
};
