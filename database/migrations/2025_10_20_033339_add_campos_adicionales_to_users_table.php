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
        Schema::table('users', function (Blueprint $table) {
            // Solo agregar columnas si no existen
            if (!Schema::hasColumn('users', 'nombre')) {
                $table->string('nombre', 100)->after('id');
            }
            if (!Schema::hasColumn('users', 'fecha_registro')) {
                $table->timestamp('fecha_registro')->useCurrent()->after('password');
            }
            if (!Schema::hasColumn('users', 'tema')) {
                $table->string('tema', 20)->default('claro')->after('fecha_registro');
            }
            if (!Schema::hasColumn('users', 'notificaciones_activas')) {
                $table->boolean('notificaciones_activas')->default(true)->after('tema');
            }
            if (!Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true)->after('notificaciones_activas');
                $table->index('activo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'fecha_registro', 'tema', 'notificaciones_activas', 'activo']);
            $table->dropIndex(['users_activo_index']);
        });
    }
};
