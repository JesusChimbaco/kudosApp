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
        Schema::create('recordatorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habito_id')->constrained('habitos')->onDelete('cascade');
            $table->boolean('activo')->default(true);
            $table->time('hora');
            $table->string('dias_semana', 50)->nullable()->comment('Formato: L,M,X,J,V,S,D o 1,2,3,4,5,6,7');
            $table->enum('tipo', ['push', 'email']);
            $table->text('mensaje_personalizado')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('habito_id');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recordatorios');
    }
};
