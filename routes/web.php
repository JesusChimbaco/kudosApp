<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\HabitoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\Web\RecordatorioWebController;
use App\Http\Controllers\Api\RegistroDiarioController;
use App\Http\Controllers\Api\ObjetivoController;
use App\Http\Controllers\Api\DashboardController;
use App\Jobs\TestEmailJob;

Route::get('/test-email', function () {
    TestEmailJob::dispatch();
    return 'Job de prueba despachado. Revisa los logs del worker.';
});

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('habitos', function () {
    return Inertia::render('Habitos/Index');
})->middleware(['auth', 'verified'])->name('habitos.index');

Route::get('calendario', function () {
    return Inertia::render('Calendario/Index');
})->middleware(['auth', 'verified'])->name('calendario.index');

Route::get('objetivos', function () {
    return Inertia::render('Objetivos/Index');
})->middleware(['auth', 'verified'])->name('objetivos.index');

Route::get('objetivos/{objetivoId}', function ($objetivoId) {
    return Inertia::render('Objetivos/Show', [
        'objetivoId' => (int) $objetivoId
    ]);
})->middleware(['auth', 'verified'])->name('objetivos.show');

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
    
    // Rutas para objetivos (autenticación por sesión)
    Route::get('/api/web/objetivos', [ObjetivoController::class, 'index']);
    Route::get('/api/web/objetivos/{id}', [ObjetivoController::class, 'show']);
    Route::post('/api/web/objetivos', [ObjetivoController::class, 'store']);
    Route::match(['put', 'patch'], '/api/web/objetivos/{id}', [ObjetivoController::class, 'update']);
    Route::delete('/api/web/objetivos/{id}', [ObjetivoController::class, 'destroy']);
    Route::post('/api/web/objetivos/{id}/completar', [ObjetivoController::class, 'marcarCompletado']);
    
    // Rutas para dashboard y estadísticas
    Route::get('/api/web/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/api/web/dashboard/objetivos-resumen', [DashboardController::class, 'objetivosResumen']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
