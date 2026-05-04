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
        Schema::create('flujos_aprobacion', function (Blueprint $table) {
            $table->id();

            $table->string('tipo_solicitud')->default('viaticos');

            $table->foreignId('role_id')->constrained();

            $table->integer('orden')->default(1);

            $table->boolean('requerido')->default(false);

            $table->integer('minimo_aprobaciones')->default(2);

            $table->boolean('estatus')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flujos_aprobacion');
    }
};
