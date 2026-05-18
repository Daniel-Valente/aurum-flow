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
        Schema::create('gastos_compartidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_pagador_id')->constrained('gastos');
            $table->enum('tipo', ['empleado', 'cliente'])->default('empleado');
            $table->foreignId('empleado_receptor_id')->nullable()->constrained('empleados')->nullOnDelete();
            $table->string('cliente_descripcion')->nullable();
            $table->decimal('monto_compartido', 10, 2);
            $table->foreignId('gasto_receptor_id')->nullable()->constrained('gastos')->nullOnDelete();
            $table->enum('estatus', ['pendiente', 'vinculado', 'rechazado', 'sin_vincular']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_compartidos');
    }
};
