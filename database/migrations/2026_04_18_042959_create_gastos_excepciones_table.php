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
        Schema::create('gastos_excepciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained()->cascadeOnDelete();

            $table->integer('nivel');
            // 1 | 2

            $table->string('estatus')->default('pendiente');
            // pendiente | aprobado | rechazado

            $table->text('comentario')->nullable();

            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('resuelto_en')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_excepciones');
    }
};
