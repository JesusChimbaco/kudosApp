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
        Schema::create('recordatorios_enviados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recordatorio_id')->constrained('recordatorios')->onDelete('cascade');
            $table->foreignId('habito_id')->constrained('habitos')->onDelete('cascade');
            $table->date('fecha_envio');
            $table->time('hora_envio');
            $table->boolean('seguimiento_enviado')->default(false);
            $table->timestamp('seguimiento_enviado_at')->nullable();
            $table->boolean('completado')->default(false)->comment('Si el usuario completó el hábito');
            $table->timestamp('completado_at')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['recordatorio_id', 'fecha_envio']);
            $table->index(['seguimiento_enviado', 'completado']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recordatorios_enviados');
    }
};
