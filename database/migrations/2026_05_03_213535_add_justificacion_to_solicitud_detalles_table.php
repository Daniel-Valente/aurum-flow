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
        Schema::table('solicitud_detalles', function (Blueprint $table) {
            $table->text('justificacion_exceso')->nullable()->after('monto_estimado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitud_detalles', function (Blueprint $table) {
            $table->dropColumn('justificacion_exceso');
        });
    }
};
