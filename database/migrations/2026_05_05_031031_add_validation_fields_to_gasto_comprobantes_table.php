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
        Schema::table('gasto_comprobantes', function (Blueprint $table) {
            $table->text('comentario_validacion')->nullable()->after('validado_por');
            $table->timestamp('validado_en')->nullable()->after('comentario_validacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gasto_comprobantes', function (Blueprint $table) {
            $table->dropForeign(['validado_por']);
            $table->dropColumn(['validado_por', 'comentario_validacion', 'validado_en']);
        });
    }
};
