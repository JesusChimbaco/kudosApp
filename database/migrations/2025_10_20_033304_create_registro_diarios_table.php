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
        Schema::create('registro_diarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habito_id')->constrained('habitos')->onDelete('cascade');
            $table->date('fecha');
            $table->boolean('completado')->default(false);
            $table->integer('veces_completado')->default(0);
            $table->text('notas')->nullable();
            $table->time('hora_completado')->nullable();
            $table->enum('estado', ['completado', 'parcial', 'perdido', 'pendiente'])->default('pendiente');
            $table->timestamps();
            
            // Constraint unique
            $table->unique(['habito_id', 'fecha']);
            
            // Índices
            $table->index('habito_id');
            $table->index('fecha');
            $table->index('estado');
            $table->index(['habito_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_diarios');
    }
};
