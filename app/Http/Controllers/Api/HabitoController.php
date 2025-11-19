<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HabitoController extends Controller
{
    /**
     * Display a listing of the authenticated user's habits.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Obtener solo los hábitos del usuario autenticado
        $habitos = Auth::user()->habitos()
            ->with(['categoria', 'objetivo', 'recordatorios']) // Eager loading
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $habitos,
            'message' => 'Hábitos obtenidos correctamente'
        ]);
    }

    /**
     * Store a newly created habit in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'categoria_id' => 'required|exists:categorias,id',
            'objetivo_id' => 'nullable|exists:objetivos,id',
            'frecuencia' => ['required', Rule::in(['diario', 'semanal', 'mensual', 'personalizado'])],
            'dias_semana' => 'nullable|array',
            'dias_semana.*' => 'integer|min:0|max:6',
            'hora_recordatorio' => 'nullable|date_format:H:i',
            'objetivo_dias' => 'nullable|integer|min:1',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'boolean',
        ]);

        // Asignar el usuario autenticado
        $validated['user_id'] = Auth::id();

        // Convertir array de días a JSON si existe
        if (isset($validated['dias_semana'])) {
            $validated['dias_semana'] = json_encode($validated['dias_semana']);
        }

        // Crear el hábito
        $habito = Habito::create($validated);

        // Cargar relaciones para la respuesta
        $habito->load(['categoria', 'objetivo', 'recordatorios']);

        return response()->json([
            'success' => true,
            'data' => $habito,
            'message' => 'Hábito creado exitosamente'
        ], 201);
    }

    /**
     * Display the specified habit.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Buscar el hábito
        $habito = Habito::with(['categoria', 'objetivo', 'recordatorios', 'registrosDiarios'])
            ->findOrFail($id);

        // Verificar que el hábito pertenezca al usuario autenticado
        if ($habito->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver este hábito'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $habito,
            'message' => 'Hábito obtenido correctamente'
        ]);
    }

    /**
     * Update the specified habit in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Buscar el hábito
        $habito = Habito::findOrFail($id);

        // Verificar que el hábito pertenezca al usuario autenticado
        if ($habito->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar este hábito'
            ], 403);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'categoria_id' => 'sometimes|required|exists:categorias,id',
            'frecuencia' => ['sometimes', 'required', Rule::in(['diario', 'semanal', 'mensual', 'personalizado'])],
            'dias_semana' => 'nullable|array',
            'dias_semana.*' => 'integer|min:0|max:6',
            'hora_recordatorio' => 'nullable|date_format:H:i',
            'objetivo_dias' => 'nullable|integer|min:1',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'boolean',
        ]);

        // Convertir array de días a JSON si existe
        if (isset($validated['dias_semana'])) {
            $validated['dias_semana'] = json_encode($validated['dias_semana']);
        }

        // Actualizar el hábito
        $habito->update($validated);

        // Recargar relaciones
        $habito->load(['categoria', 'recordatorios']);

        return response()->json([
            'success' => true,
            'data' => $habito,
            'message' => 'Hábito actualizado exitosamente'
        ]);
    }

    /**
     * Remove the specified habit from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Buscar el hábito
        $habito = Habito::findOrFail($id);

        // Verificar que el hábito pertenezca al usuario autenticado
        if ($habito->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este hábito'
            ], 403);
        }

        // Eliminar el hábito (cascade eliminará registros y recordatorios)
        $habito->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hábito eliminado exitosamente'
        ]);
    }

    /**
     * Toggle the active status of a habit.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActivo($id)
    {
        $habito = Habito::findOrFail($id);

        // Verificar permisos
        if ($habito->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar este hábito'
            ], 403);
        }

        // Cambiar el estado
        $habito->activo = !$habito->activo;
        $habito->save();

        return response()->json([
            'success' => true,
            'data' => $habito,
            'message' => $habito->activo ? 'Hábito activado' : 'Hábito desactivado'
        ]);
    }

    /**
     * Get user's active habits.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activos()
    {
        $habitos = Auth::user()->habitos()
            ->activos()
            ->with(['categoria', 'recordatorios'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $habitos,
            'message' => 'Hábitos activos obtenidos correctamente'
        ]);
    }

    /**
     * Get habits statistics for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function estadisticas()
    {
        $user = Auth::user();

        $stats = [
            'total_habitos' => $user->habitos()->count(),
            'habitos_activos' => $user->habitos()->activos()->count(),
            'habitos_inactivos' => $user->habitos()->where('activo', false)->count(),
            'por_frecuencia' => $user->habitos()
                ->selectRaw('frecuencia, COUNT(*) as total')
                ->groupBy('frecuencia')
                ->pluck('total', 'frecuencia'),
            'por_categoria' => $user->habitos()
                ->join('categorias', 'habitos.categoria_id', '=', 'categorias.id')
                ->selectRaw('categorias.nombre, COUNT(*) as total')
                ->groupBy('categorias.nombre')
                ->pluck('total', 'nombre'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Estadísticas obtenidas correctamente'
        ]);
    }
}
