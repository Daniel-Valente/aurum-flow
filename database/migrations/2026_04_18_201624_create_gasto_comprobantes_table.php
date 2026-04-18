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
            $table->string('tipo')->nullable();
            // factura | ticket
            $table->string('uuid')->nullable();
            // CFDI si aplica

            $table->decimal('monto', 12, 2)->nullable();

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
