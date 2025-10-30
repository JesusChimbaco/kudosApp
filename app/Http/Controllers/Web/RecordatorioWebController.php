<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Habito;
use App\Models\Recordatorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RecordatorioWebController extends Controller
{
    /**
     * Obtener todos los recordatorios de un hábito
     */
    public function index($habitoId)
    {
        $habito = Habito::findOrFail($habitoId);
        
        // Verificar que el hábito pertenece al usuario autenticado
        if ($habito->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $recordatorios = $habito->recordatorios()
            ->orderBy('hora')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recordatorios
        ]);
    }

    /**
     * Crear un nuevo recordatorio para un hábito
     */
    public function store(Request $request, $habitoId)
    {
        $habito = Habito::findOrFail($habitoId);
        
        // Verificar que el hábito pertenece al usuario autenticado
        if ($habito->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'hora' => 'required|date_format:H:i',
            'dias_semana' => 'nullable|string',
            'tipo' => 'required|in:email,push',
            'mensaje_personalizado' => 'nullable|string|max:500',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar formato de días_semana si se proporciona
        if ($request->has('dias_semana') && !empty($request->dias_semana)) {
            $diasValidos = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
            $dias = array_map('trim', explode(',', $request->dias_semana));
            
            foreach ($dias as $dia) {
                if (!in_array($dia, $diasValidos)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de días inválido. Use: L,M,X,J,V,S,D'
                    ], 422);
                }
            }
        }

        $recordatorio = $habito->recordatorios()->create([
            'hora' => $request->hora,
            'dias_semana' => $request->dias_semana,
            'tipo' => $request->tipo,
            'mensaje_personalizado' => $request->mensaje_personalizado,
            'activo' => filter_var($request->activo ?? true, FILTER_VALIDATE_BOOLEAN)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recordatorio creado exitosamente',
            'data' => $recordatorio
        ], 201);
    }

    /**
     * Actualizar un recordatorio
     */
    public function update(Request $request, $habitoId, $recordatorioId)
    {
        $habito = Habito::findOrFail($habitoId);
        
        // Verificar que el hábito pertenece al usuario autenticado
        if ($habito->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $recordatorio = Recordatorio::findOrFail($recordatorioId);

        if ($recordatorio->habito_id !== $habito->id) {
            return response()->json([
                'success' => false,
                'message' => 'El recordatorio no pertenece a este hábito'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'hora' => 'sometimes|date_format:H:i',
            'dias_semana' => 'nullable|string',
            'tipo' => 'sometimes|in:email,push',
            'mensaje_personalizado' => 'nullable|string|max:500',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar formato de días_semana si se proporciona
        if ($request->has('dias_semana') && !empty($request->dias_semana)) {
            $diasValidos = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
            $dias = array_map('trim', explode(',', $request->dias_semana));
            
            foreach ($dias as $dia) {
                if (!in_array($dia, $diasValidos)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de días inválido. Use: L,M,X,J,V,S,D'
                    ], 422);
                }
            }
        }

        $recordatorio->update([
            'hora' => $request->hora,
            'dias_semana' => $request->dias_semana,
            'tipo' => $request->tipo,
            'mensaje_personalizado' => $request->mensaje_personalizado,
            'activo' => filter_var($request->activo ?? true, FILTER_VALIDATE_BOOLEAN)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recordatorio actualizado exitosamente',
            'data' => $recordatorio
        ]);
    }

    /**
     * Eliminar un recordatorio
     */
    public function destroy($habitoId, $recordatorioId)
    {
        $habito = Habito::findOrFail($habitoId);
        
        // Verificar que el hábito pertenece al usuario autenticado
        if ($habito->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $recordatorio = Recordatorio::findOrFail($recordatorioId);

        if ($recordatorio->habito_id !== $habito->id) {
            return response()->json([
                'success' => false,
                'message' => 'El recordatorio no pertenece a este hábito'
            ], 404);
        }

        $recordatorio->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recordatorio eliminado exitosamente'
        ]);
    }

    /**
     * Activar/desactivar un recordatorio
     */
    public function toggle($habitoId, $recordatorioId)
    {
        $habito = Habito::findOrFail($habitoId);
        
        // Verificar que el hábito pertenece al usuario autenticado
        if ($habito->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $recordatorio = Recordatorio::findOrFail($recordatorioId);

        if ($recordatorio->habito_id !== $habito->id) {
            return response()->json([
                'success' => false,
                'message' => 'El recordatorio no pertenece a este hábito'
            ], 404);
        }

        $recordatorio->update([
            'activo' => !$recordatorio->activo
        ]);

        return response()->json([
            'success' => true,
            'message' => $recordatorio->activo ? 'Recordatorio activado' : 'Recordatorio desactivado',
            'data' => $recordatorio
        ]);
    }
}
