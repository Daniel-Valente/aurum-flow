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
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();

            $table->string('tipo'); // Lo validaremos en el CHECK
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->nullOnDelete();
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();

            $table->decimal('monto_total', 15, 2);
            $table->decimal('monto_gastado', 15, 2)->default(0);
            $table->decimal('monto_comprometido', 15, 2)->default(0);

            // Eliminamos la columna virtual de aquí para evitar el error de sintaxis

            $table->string('periodo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('renovable')->default(false);
            $table->string('frecuencia_renovacion')->nullable();
            $table->decimal('alerta_porcentaje', 5, 2)->default(80.00);
            $table->decimal('critico_porcentaje', 5, 2)->default(95.00);
            $table->boolean('activo')->default(true);
            $table->string('estatus')->default('borrador');

            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('aprobado_en')->nullable();
            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 1. Agregamos la columna calculada como STORED (Forma correcta en Postgres)
        DB::statement("ALTER TABLE presupuestos ADD COLUMN monto_disponible DECIMAL(15,2)
            GENERATED ALWAYS AS (monto_total - monto_gastado - monto_comprometido) STORED");

        // 2. Agregamos el CHECK de integridad que querías al principio
        DB::statement("ALTER TABLE presupuestos ADD CONSTRAINT check_presupuesto_tipo CHECK (
            (tipo = 'empresa' AND empresa_id IS NOT NULL AND area_id IS NULL AND empleado_id IS NULL AND proyecto_id IS NULL) OR
            (tipo = 'area' AND area_id IS NOT NULL AND empleado_id IS NULL AND proyecto_id IS NULL) OR
            (tipo = 'empleado' AND empleado_id IS NOT NULL AND proyecto_id IS NULL) OR
            (tipo = 'proyecto' AND proyecto_id IS NOT NULL)
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};
