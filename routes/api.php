<?php

use App\Http\Controllers\Api\HabitoController;
use App\Http\Controllers\Api\RegistroDiarioController;
use App\Http\Controllers\Api\RecordatorioController;
use App\Http\Controllers\Api\ObjetivoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Ruta pública para generar token (solo para desarrollo/testing)
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Credenciales inválidas'
        ], 401);
    }

    if (!$user->activo) {
        return response()->json([
            'success' => false,
            'message' => 'Usuario inactivo'
        ], 403);
    }

    // Eliminar tokens anteriores (opcional)
    $user->tokens()->delete();

    // Crear nuevo token
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login exitoso',
        'data' => [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]
    ]);
});

// Rutas protegidas por autenticación
Route::middleware('auth:sanctum')->group(function () {
    
    // Ruta para obtener el usuario autenticado
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load(['habitos', 'logros']),
        ]);
    });

    // Logout - Revocar token actual
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    });

    // Rutas de Hábitos
    Route::prefix('habitos')->group(function () {
        // Rutas personalizadas (deben ir ANTES del resource)
        Route::get('/activos', [HabitoController::class, 'activos']);
        Route::get('/estadisticas', [HabitoController::class, 'estadisticas']);
        Route::patch('/{id}/toggle-activo', [HabitoController::class, 'toggleActivo']);
        
        // Rutas de Registro Diario (marcar como completado)
        Route::post('/{habito}/completar', [RegistroDiarioController::class, 'completar']);
        Route::post('/{habito}/descompletar', [RegistroDiarioController::class, 'descompletar']);
        Route::get('/{habito}/registros', [RegistroDiarioController::class, 'historial']);
        Route::get('/{habito}/registro/{fecha}', [RegistroDiarioController::class, 'obtenerPorFecha']);
        Route::get('/{habito}/estadisticas-detalladas', [RegistroDiarioController::class, 'estadisticasDetalladas']);
        
        // Rutas de Recordatorios
        Route::post('/{habito}/recordatorios/{recordatorio}/toggle', [RecordatorioController::class, 'toggle']);
        Route::apiResource('{habito}/recordatorios', RecordatorioController::class);
    });

    // CRUD completo de hábitos
    Route::apiResource('habitos', HabitoController::class);

    // Rutas de Objetivos
    Route::prefix('objetivos')->group(function () {
        Route::post('/{id}/completar', [ObjetivoController::class, 'marcarCompletado']);
    });
    Route::apiResource('objetivos', ObjetivoController::class);
});
