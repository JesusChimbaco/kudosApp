<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Objetivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ObjetivoController extends Controller
{
    /**
     * Obtener todos los objetivos del usuario autenticado.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Objetivo::where('user_id', $user->id)
            ->with('habitos');
        
        // Filtrar por activos si se especifica
        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }
        
        // Filtrar por completados si se especifica
        if ($request->has('completado')) {
            $query->where('completado', $request->boolean('completado'));
        }
        
        $objetivos = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $objetivos,
            'message' => 'Objetivos obtenidos correctamente'
        ], 200);
    }

    /**
     * Crear un nuevo objetivo.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'tipo' => 'required|in:salud,fitness,educacion,finanzas,bienestar,carrera,relaciones,otro',
            'fecha_inicio' => 'nullable|date',
            'fecha_objetivo' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $objetivo = Objetivo::create([
            'user_id' => Auth::id(),
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'emoji' => $request->emoji,
            'color' => $request->color,
            'tipo' => $request->tipo,
            'fecha_inicio' => $request->fecha_inicio ?? now(),
            'fecha_objetivo' => $request->fecha_objetivo,
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $objetivo,
            'message' => 'Objetivo creado exitosamente'
        ], 201);
    }

    /**
     * Obtener un objetivo específico.
     */
    public function show($id)
    {
        $objetivo = Objetivo::where('user_id', Auth::id())
            ->with(['habitos' => function($query) {
                $query->where('activo', true);
            }])
            ->find($id);

        if (!$objetivo) {
            return response()->json([
                'success' => false,
                'message' => 'Objetivo no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $objetivo
        ], 200);
    }

    /**
     * Actualizar un objetivo.
     */
    public function update(Request $request, $id)
    {
        $objetivo = Objetivo::where('user_id', Auth::id())->find($id);

        if (!$objetivo) {
            return response()->json([
                'success' => false,
                'message' => 'Objetivo no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:150',
            'descripcion' => 'nullable|string',
            'emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'tipo' => 'sometimes|required|in:salud,fitness,educacion,finanzas,bienestar,carrera,relaciones,otro',
            'fecha_objetivo' => 'nullable|date',
            'activo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $objetivo->update($request->only([
            'nombre', 'descripcion', 'emoji', 'color', 'tipo',
            'fecha_objetivo', 'activo'
        ]));

        return response()->json([
            'success' => true,
            'data' => $objetivo,
            'message' => 'Objetivo actualizado exitosamente'
        ], 200);
    }

    /**
     * Marcar un objetivo como completado.
     */
    public function marcarCompletado($id)
    {
        $objetivo = Objetivo::where('user_id', Auth::id())->find($id);

        if (!$objetivo) {
            return response()->json([
                'success' => false,
                'message' => 'Objetivo no encontrado'
            ], 404);
        }

        $objetivo->update([
            'completado' => true,
            'fecha_completado' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $objetivo,
            'message' => '¡Felicitaciones! Objetivo completado exitosamente'
        ], 200);
    }

    /**
     * Eliminar un objetivo.
     */
    public function destroy($id)
    {
        $objetivo = Objetivo::where('user_id', Auth::id())->find($id);

        if (!$objetivo) {
            return response()->json([
                'success' => false,
                'message' => 'Objetivo no encontrado'
            ], 404);
        }

        // Verificar si tiene hábitos asociados
        if ($objetivo->habitos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el objetivo porque tiene hábitos asociados'
            ], 400);
        }

        $objetivo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Objetivo eliminado exitosamente'
        ], 200);
    }
}
