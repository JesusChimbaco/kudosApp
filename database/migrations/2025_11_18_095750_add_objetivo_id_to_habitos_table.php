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
            $table->foreignId('objetivo_id')->nullable()->after('categoria_id')->constrained('objetivos')->onDelete('set null');
            $table->index('objetivo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('habitos', function (Blueprint $table) {
            $table->dropForeign(['objetivo_id']);
            $table->dropIndex(['objetivo_id']);
            $table->dropColumn('objetivo_id');
        });
    }
};
