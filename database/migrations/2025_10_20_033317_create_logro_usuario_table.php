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
        // Solo crear si no existe
        if (!Schema::hasTable('logro_usuario')) {
            Schema::create('logro_usuario', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('logro_id')->constrained('logros')->onDelete('cascade');
                $table->timestamp('fecha_obtenido')->useCurrent();
                $table->foreignId('habito_id')->nullable()->constrained('habitos')->onDelete('set null');
                $table->timestamp('created_at')->useCurrent();
                
                // Constraint unique
                $table->unique(['user_id', 'logro_id', 'habito_id']);
                
                // Índices
                $table->index('user_id');
                $table->index('logro_id');
                $table->index('habito_id');
                $table->index('fecha_obtenido');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logro_usuario');
    }
};
