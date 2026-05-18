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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();

            $table->string('codigo');
            $table->string('nombre', 255);
            $table->string('nombre_comercial', 255)->nullable();
            $table->string('rfc', 13)->unique();

            $table->text('domicilio_fiscal')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('estado', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('pais', 100)->default('México');

            $table->string('telefono', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('sitio_web', 255)->nullable();

            $table->string('moneda', 3)->default('MXN');
            $table->string('timezone', 50)->default('America/Mexico_City');
            $table->string('logo_path', 500)->nullable();

            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('rfc');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
