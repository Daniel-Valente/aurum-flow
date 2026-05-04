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
        Schema::create('solicitud_aprobaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();

            $table->foreignId('role_id')->constrained();

            $table->foreignId('user_id')->constrained();

            $table->string('accion');

            $table->text('comentario')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['solicitud_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_aprobaciones');
    }
};
