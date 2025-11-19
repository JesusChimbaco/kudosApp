<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habito;
use App\Models\Recordatorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RecordatorioController extends Controller
{
    use AuthorizesRequests;
    /**
     * Obtener todos los recordatorios de un hábito
     */
    public function index(Habito $habito)
    {
        $this->authorize('view', $habito);

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
    public function store(Request $request, Habito $habito)
    {
        $this->authorize('update', $habito);

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
            'activo' => $request->activo ?? true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recordatorio creado exitosamente',
            'data' => $recordatorio
        ], 201);
    }

    /**
     * Mostrar un recordatorio específico
     */
    public function show(Habito $habito, Recordatorio $recordatorio)
    {
        $this->authorize('view', $habito);

        if ($recordatorio->habito_id !== $habito->id) {
            return response()->json([
                'success' => false,
                'message' => 'El recordatorio no pertenece a este hábito'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $recordatorio
        ]);
    }

    /**
     * Actualizar un recordatorio
     */
    public function update(Request $request, Habito $habito, Recordatorio $recordatorio)
    {
        $this->authorize('update', $habito);

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

        $recordatorio->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Recordatorio actualizado exitosamente',
            'data' => $recordatorio
        ]);
    }

    /**
     * Eliminar un recordatorio
     */
    public function destroy(Habito $habito, Recordatorio $recordatorio)
    {
        $this->authorize('update', $habito);

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
    public function toggle(Habito $habito, Recordatorio $recordatorio)
    {
        $this->authorize('update', $habito);

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
