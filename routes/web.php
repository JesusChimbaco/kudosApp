<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\HabitoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\Web\RecordatorioWebController;
use App\Http\Controllers\Api\RegistroDiarioController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('habitos', function () {
    return Inertia::render('Habitos/Index');
})->middleware(['auth', 'verified'])->name('habitos.index');

Route::get('habitos/{habitoId}/recordatorios', function ($habitoId) {
    return Inertia::render('Habitos/Recordatorios', [
        'habitoId' => (int) $habitoId
    ]);
})->middleware(['auth', 'verified'])->name('habitos.recordatorios');

// Rutas web para hábitos (sin token, usando sesión)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/web/habitos', [HabitoController::class, 'index']);
    Route::get('/api/web/habitos/{id}', [HabitoController::class, 'show']);
    Route::post('/api/web/habitos', [HabitoController::class, 'store']);
    // Soporta actualización desde la UI (PUT/PATCH)
    Route::match(['put', 'patch'], '/api/web/habitos/{id}', [HabitoController::class, 'update']);
    Route::delete('/api/web/habitos/{id}', [HabitoController::class, 'destroy']);
    Route::patch('/api/web/habitos/{id}/toggle-activo', [HabitoController::class, 'toggleActivo']);
    
    // Rutas para marcar hábitos como completados (registro diario)
    Route::post('/api/web/habitos/{habito}/completar', [RegistroDiarioController::class, 'completar']);
    Route::post('/api/web/habitos/{habito}/descompletar', [RegistroDiarioController::class, 'descompletar']);
    Route::get('/api/web/habitos/{habito}/registros', [RegistroDiarioController::class, 'historial']);
    Route::get('/api/web/habitos/{habito}/registro/{fecha}', [RegistroDiarioController::class, 'obtenerPorFecha']);
    
    // Rutas para categorías
    Route::get('/api/web/categorias', [CategoriaController::class, 'index']);
    
    // Rutas para recordatorios (autenticación por sesión)
    Route::get('/api/web/habitos/{habitoId}/recordatorios', [RecordatorioWebController::class, 'index']);
    Route::post('/api/web/habitos/{habitoId}/recordatorios', [RecordatorioWebController::class, 'store']);
    Route::patch('/api/web/habitos/{habitoId}/recordatorios/{recordatorioId}', [RecordatorioWebController::class, 'update']);
    Route::delete('/api/web/habitos/{habitoId}/recordatorios/{recordatorioId}', [RecordatorioWebController::class, 'destroy']);
    Route::post('/api/web/habitos/{habitoId}/recordatorios/{recordatorioId}/toggle', [RecordatorioWebController::class, 'toggle']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
