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
            $table->string('archivo_pdf')->nullable()->after('archivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gasto_comprobantes', function (Blueprint $table) {
            $table->dropColumn(['archivo_pdf']);
        });
    }
};
