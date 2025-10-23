<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\HabitoController;
use App\Http\Controllers\CategoriaController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('habitos', function () {
    return Inertia::render('Habitos/Index');
})->middleware(['auth', 'verified'])->name('habitos.index');

// Rutas web para hábitos (sin token, usando sesión)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/web/habitos', [HabitoController::class, 'index']);
    Route::post('/api/web/habitos', [HabitoController::class, 'store']);
    Route::delete('/api/web/habitos/{id}', [HabitoController::class, 'destroy']);
    Route::patch('/api/web/habitos/{id}/toggle-activo', [HabitoController::class, 'toggleActivo']);
    
    // Rutas para categorías
    Route::get('/api/web/categorias', [CategoriaController::class, 'index']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
