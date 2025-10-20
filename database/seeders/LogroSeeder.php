<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya hay logros antes de insertar
        if (DB::table('logros')->count() > 0) {
            $this->command->info('Los logros ya existen, omitiendo seeder...');
            return;
        }

        $logros = [
            [
                'codigo' => 'RACHA_3',
                'nombre' => 'Principiante Constante',
                'descripcion' => 'Completa un hábito 3 días seguidos',
                'icono' => '🔥',
                'tipo' => 'racha',
                'criterio_valor' => 3,
                'puntos' => 10,
                'activo' => true,
            ],
            [
                'codigo' => 'RACHA_7',
                'nombre' => 'Semana Perfecta',
                'descripcion' => 'Completa un hábito 7 días seguidos',
                'icono' => '⭐',
                'tipo' => 'racha',
                'criterio_valor' => 7,
                'puntos' => 25,
                'activo' => true,
            ],
            [
                'codigo' => 'RACHA_30',
                'nombre' => 'Mes de Disciplina',
                'descripcion' => 'Completa un hábito 30 días seguidos',
                'icono' => '🏆',
                'tipo' => 'racha',
                'criterio_valor' => 30,
                'puntos' => 100,
                'activo' => true,
            ],
            [
                'codigo' => 'RACHA_100',
                'nombre' => 'Maestro de Hábitos',
                'descripcion' => 'Completa un hábito 100 días seguidos',
                'icono' => '👑',
                'tipo' => 'racha',
                'criterio_valor' => 100,
                'puntos' => 500,
                'activo' => true,
            ],
            [
                'codigo' => 'CANTIDAD_10',
                'nombre' => 'Decatleta',
                'descripcion' => 'Completa un hábito 10 veces',
                'icono' => '✅',
                'tipo' => 'cantidad',
                'criterio_valor' => 10,
                'puntos' => 15,
                'activo' => true,
            ],
            [
                'codigo' => 'CANTIDAD_50',
                'nombre' => 'Persistente',
                'descripcion' => 'Completa un hábito 50 veces',
                'icono' => '💪',
                'tipo' => 'cantidad',
                'criterio_valor' => 50,
                'puntos' => 50,
                'activo' => true,
            ],
            [
                'codigo' => 'CANTIDAD_100',
                'nombre' => 'Centenario',
                'descripcion' => 'Completa un hábito 100 veces',
                'icono' => '🎯',
                'tipo' => 'cantidad',
                'criterio_valor' => 100,
                'puntos' => 150,
                'activo' => true,
            ],
            [
                'codigo' => 'PRIMER_HABITO',
                'nombre' => 'Primer Paso',
                'descripcion' => 'Crea tu primer hábito',
                'icono' => '🌱',
                'tipo' => 'especial',
                'criterio_valor' => 1,
                'puntos' => 5,
                'activo' => true,
            ],
            [
                'codigo' => 'MADRUGADOR',
                'nombre' => 'Madrugador',
                'descripcion' => 'Completa un hábito antes de las 7 AM',
                'icono' => '🌅',
                'tipo' => 'especial',
                'criterio_valor' => 1,
                'puntos' => 20,
                'activo' => true,
            ],
            [
                'codigo' => 'PERFECCIONISTA',
                'nombre' => 'Perfeccionista',
                'descripcion' => 'Completa todos tus hábitos en un día',
                'icono' => '💯',
                'tipo' => 'especial',
                'criterio_valor' => 1,
                'puntos' => 30,
                'activo' => true,
            ],
        ];

        DB::table('logros')->insert($logros);
        $this->command->info('Logros insertados exitosamente!');
    }
}
