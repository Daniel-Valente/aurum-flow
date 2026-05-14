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
        Schema::create('configuracion_empresa', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('dias_habiles_comprobacion')->default(5);

            $table->unsignedSmallInteger('cfdi_dias_antes_permitidos')->default(3);
            $table->unsignedSmallInteger('cfdi_dias_despues_permitidos')->default(10);

            $table->string('rfc_empresa', 13)->nullable();
            $table->boolean('validar_rfc_receptor')->default(true);

            $table->boolean('propina_auto_aprueba')->default(true);
            $table->boolean('gasto_compartido_auto_aprueba')->default(true);
            $table->boolean('gasto_cliente_auto_aprueba')->default(true);

            $table->string('validador_tickets', 20)->default('finanzas');

            $table->string('moneda', 3)->default('MXN');
            $table->string('pais', 2)->default('MX');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_empresa');
    }
};
