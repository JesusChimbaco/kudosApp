<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('objetivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('emoji', 10)->nullable();
            $table->string('color', 20)->nullable();
            $table->enum('tipo', ['salud', 'fitness', 'educacion', 'finanzas', 'bienestar', 'carrera', 'relaciones', 'otro'])->default('otro');
            $table->date('fecha_inicio')->default(DB::raw('CURRENT_DATE'));
            $table->date('fecha_objetivo')->nullable()->comment('Fecha límite para alcanzar el objetivo');
            $table->boolean('completado')->default(false);
            $table->date('fecha_completado')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('activo');
            $table->index('completado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objetivos');
    }
};
