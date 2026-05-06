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
            $table->date('fecha_gasto')->nullable()->after('monto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gasto_comprobantes', function (Blueprint $table) {
            $table->dropColumn('fecha_gasto');
        });
    }
};
