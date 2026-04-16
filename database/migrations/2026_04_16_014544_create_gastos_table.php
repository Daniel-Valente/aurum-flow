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
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();
            $table->foreignId('concepto_id')->constrained();

            $table->date('fecha_gasto')->index();

            $table->decimal('monto', 12, 2);

            $table->string('rfc_proveedor', 15)->nullable();
            $table->uuid('uuid_factura')->nullable()->index();

            $table->string('archivo_xml')->nullable();
            $table->string('archivo_pdf')->nullable();

            $table->string('estatus')->default('Validado')->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['solicitud_id', 'concepto_id', 'fecha_gasto']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
