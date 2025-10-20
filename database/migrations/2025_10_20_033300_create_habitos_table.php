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
        Schema::create('habitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('emoji', 10)->nullable();
            $table->string('color', 20)->nullable();
            $table->enum('frecuencia', ['diario', 'semanal', 'mensual', 'personalizado']);
            $table->string('dias_semana', 50)->nullable()->comment('Formato: L,M,X,J,V,S,D o 1,2,3,4,5,6,7');
            $table->integer('veces_por_semana')->nullable();
            $table->integer('objetivo_diario')->default(1);
            $table->time('hora_preferida')->nullable();
            $table->integer('racha_actual')->default(0);
            $table->integer('racha_maxima')->default(0);
            $table->boolean('activo')->default(true);
            $table->date('fecha_inicio')->default(DB::raw('CURRENT_DATE'));
            $table->date('fecha_fin')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('activo');
            $table->index('fecha_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitos');
    }
};
