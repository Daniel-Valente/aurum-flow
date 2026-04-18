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
        Schema::create('solicitudes_auditoria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();

            $table->string('evento');
            // created | enviado | aprobado | rechazado | comprobado

            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->json('datos')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_auditoria');
    }
};
