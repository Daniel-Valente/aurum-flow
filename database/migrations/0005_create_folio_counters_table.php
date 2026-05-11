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
        Schema::create('folio_counters', function (Blueprint $table) {
            $table->id();
            $table->string('prefix');
            $table->integer('year');
            $table->unsignedBigInteger('current')->default(0);
            $table->unique(['prefix', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folio_counters');
    }
};
