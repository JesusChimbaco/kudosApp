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
        Schema::table('habitos', function (Blueprint $table) {
            // Agregar la columna categoria_id después de user_id
            $table->foreignId('categoria_id')
                  ->after('user_id')
                  ->constrained('categorias')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('habitos', function (Blueprint $table) {
            // Eliminar la foreign key constraint y la columna
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
        });
    }
};
