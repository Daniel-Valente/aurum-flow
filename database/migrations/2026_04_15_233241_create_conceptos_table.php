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
        Schema::create('conceptos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo')->unique();
            $table->string('nombre');

            $table->string('categoria')->nullable();
            $table->string('descripcion')->nullable();

            $table->string('tipo_aplicacion', 20)->default('Diario')->index();
            //Diario | Evento | Viaje

            $table->integer('orden')->default(0);

            $table->boolean('requiere_factura')->default(true);
            $table->boolean('requiere_comprobante')->default(true);
            $table->boolean('requiere_uuid')->default(false);
            $table->boolean('permite_sin_factura')->default(false);
            $table->boolean('aplica_iva')->default(true);
            $table->boolean('acumulable_dia')->default(true);

            $table->decimal('tope_referencia', 10, 2)->nullable();

            $table->date('vigencia_desde')->nullable();
            $table->date('vigencia_hasta')->nullable();

            $table->boolean('estatus')->default(true)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conceptos');
    }
};
