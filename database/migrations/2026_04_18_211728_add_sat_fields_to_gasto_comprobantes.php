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
            $table->string('sat_status')->nullable()->index();
            // pediente | vigente | cancelado | no_encontrado

            $table->timestamp('sat_checked_at')->nullable();

            $table->integer('sat_attempts')->default(0);
            $table->json('meta_cfdi')->nullable();

            $table->text('sat_last_error')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gasto_comprobantes', function (Blueprint $table) {
            //
        });
    }
};
