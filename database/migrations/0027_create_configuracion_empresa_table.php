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

            // Foreign key a empresas (NULL = configuración global/default)
            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->cascadeOnDelete();

            // Reglas de validación
            $table->unsignedSmallInteger('dias_habiles_comprobacion')->default(5);
            $table->unsignedSmallInteger('cfdi_dias_antes_permitidos')->default(3);
            $table->unsignedSmallInteger('cfdi_dias_despues_permitidos')->default(10);

            // RFC
            $table->string('rfc_empresa', 13)->nullable();
            $table->boolean('validar_rfc_receptor')->default(true);

            // Auto-aprobaciones
            $table->boolean('propina_auto_aprueba')->default(true);
            $table->boolean('gasto_compartido_auto_aprueba')->default(true);
            $table->boolean('gasto_cliente_auto_aprueba')->default(true);

            $table->string('validador_tickets', 20)->default('finanzas');

            // Por defecto heredará de la empresa, pero permite override
            $table->string('moneda', 3)->nullable();
            $table->string('pais', 2)->nullable();

            $table->timestamps();

            // Una configuración por empresa
            $table->unique('empresa_id');
            $table->index('empresa_id');
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
