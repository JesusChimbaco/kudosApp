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
        Schema::table('recordatorios', function (Blueprint $table) {
            $table->boolean('enviar_seguimiento')->default(true)->after('mensaje_personalizado')->comment('Si enviar recordatorio de seguimiento después de 5 minutos');
            $table->integer('minutos_seguimiento')->default(5)->after('enviar_seguimiento')->comment('Minutos a esperar antes de enviar recordatorio de seguimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recordatorios', function (Blueprint $table) {
            $table->dropColumn(['enviar_seguimiento', 'minutos_seguimiento']);
        });
    }
};
