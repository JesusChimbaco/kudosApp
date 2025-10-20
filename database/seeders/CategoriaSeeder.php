<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya hay categorías antes de insertar
        if (DB::table('categorias')->count() > 0) {
            $this->command->info('Las categorías ya existen, omitiendo seeder...');
            return;
        }

        $categorias = [
            [
                'nombre' => 'Salud',
                'descripcion' => 'Hábitos relacionados con la salud física y mental',
                'icono' => '🏃',
                'color' => '#4CAF50',
                'activo' => true,
            ],
            [
                'nombre' => 'Productividad',
                'descripcion' => 'Hábitos para mejorar la productividad personal',
                'icono' => '💼',
                'color' => '#2196F3',
                'activo' => true,
            ],
            [
                'nombre' => 'Aprendizaje',
                'descripcion' => 'Hábitos de estudio y aprendizaje continuo',
                'icono' => '📚',
                'color' => '#FF9800',
                'activo' => true,
            ],
            [
                'nombre' => 'Bienestar',
                'descripcion' => 'Hábitos de autocuidado y bienestar emocional',
                'icono' => '🧘',
                'color' => '#9C27B0',
                'activo' => true,
            ],
            [
                'nombre' => 'Finanzas',
                'descripcion' => 'Hábitos financieros y ahorro',
                'icono' => '💰',
                'color' => '#4CAF50',
                'activo' => true,
            ],
            [
                'nombre' => 'Social',
                'descripcion' => 'Hábitos sociales y relaciones interpersonales',
                'icono' => '👥',
                'color' => '#E91E63',
                'activo' => true,
            ],
        ];

        DB::table('categorias')->insert($categorias);
        $this->command->info('Categorías insertadas exitosamente!');
    }
}
