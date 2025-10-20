<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders en orden
        $this->call([
            CategoriaSeeder::class,
            LogroSeeder::class,
        ]);

        // User::factory(10)->create();

        // Crear usuario de prueba solo si no existe
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'nombre' => 'Usuario de Prueba',
                'email' => 'test@example.com',
            ]);
        }
    }
}
