<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habito;
use App\Models\RegistroDiario;
use App\Models\RecordatorioEnviado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistroDiarioController extends Controller
{
    /**
     * Marcar un hábito como completado para una fecha específica
     * 
     * POST /api/habitos/{habito}/completar
     */
    public function completar(Request $request, $habitoId)
    {
        $request->validate([
            'fecha' => 'nullable|date',
            'notas' => 'nullable|string|max:500',
            'veces' => 'nullable|integer|min:1',
        ]);

        $habito = Habito::where('id', $habitoId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $veces = $request->input('veces', 1);

        try {
            DB::beginTransaction();

            // Buscar o crear el registro del día
            $registro = RegistroDiario::firstOrCreate(
                [
                    'habito_id' => $habito->id,
                    'fecha' => $fecha,
                ],
                [
                    'completado' => false,
                    'veces_completado' => 0,
                    'estado' => 'pendiente',
                ]
            );

            // Incrementar las veces completado
            $registro->veces_completado += $veces;
            $registro->hora_completado = now();
            $registro->completado = true;
            
            // Actualizar notas si se proporcionan
            if ($request->has('notas')) {
                $registro->notas = $request->input('notas');
            }

            // Determinar el estado según el objetivo
            if ($habito->objetivo_diario) {
                if ($registro->veces_completado >= $habito->objetivo_diario) {
                    $registro->estado = 'completado';
                } else {
                    $registro->estado = 'parcial';
                }
            } else {
                $registro->estado = 'completado';
            }

            $registro->save();

            // Actualizar racha del hábito
            $this->actualizarRacha($habito);

            // Marcar recordatorios enviados de hoy como completados
            RecordatorioEnviado::where('habito_id', $habito->id)
                ->whereDate('fecha_envio', $fecha)
                ->where('completado', false)
                ->update([
                    'completado' => true,
                    'completado_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hábito marcado como completado',
                'data' => [
                    'registro' => $registro,
                    'racha_actual' => $habito->fresh()->racha_actual,
                    'racha_maxima' => $habito->fresh()->racha_maxima,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar el hábito como completado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Desmarcar un hábito (restar veces o eliminar completado)
     * 
     * POST /api/habitos/{habito}/descompletar
     */
    public function descompletar(Request $request, $habitoId)
    {
        $request->validate([
            'fecha' => 'nullable|date',
            'veces' => 'nullable|integer|min:1',
        ]);

        $habito = Habito::where('id', $habitoId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $veces = $request->input('veces', 1);

        $registro = RegistroDiario::where('habito_id', $habito->id)
            ->where('fecha', $fecha)
            ->first();

        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => 'No hay registro para esta fecha',
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Restar veces completado
            $registro->veces_completado = max(0, $registro->veces_completado - $veces);

            if ($registro->veces_completado === 0) {
                $registro->completado = false;
                $registro->estado = 'pendiente';
                $registro->hora_completado = null;
            } else {
                // Actualizar estado según objetivo
                if ($habito->objetivo_diario && $registro->veces_completado < $habito->objetivo_diario) {
                    $registro->estado = 'parcial';
                }
            }

            $registro->save();

            // Actualizar racha del hábito
            $this->actualizarRacha($habito);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado',
                'data' => [
                    'registro' => $registro,
                    'racha_actual' => $habito->fresh()->racha_actual,
                    'racha_maxima' => $habito->fresh()->racha_maxima,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener el historial de registros de un hábito
     * 
     * GET /api/habitos/{habito}/registros
     */
    public function historial($habitoId, Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $habito = Habito::where('id', $habitoId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $query = RegistroDiario::where('habito_id', $habito->id)
            ->orderBy('fecha', 'desc');

        // Filtrar por rango de fechas si se proporciona
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->entreFechas($request->fecha_inicio, $request->fecha_fin);
        } elseif ($request->has('fecha_inicio')) {
            $query->where('fecha', '>=', $request->fecha_inicio);
        } elseif ($request->has('fecha_fin')) {
            $query->where('fecha', '<=', $request->fecha_fin);
        }

        // Limitar resultados
        $limit = $request->input('limit', 30);
        $registros = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $registros,
        ], 200);
    }

    /**
     * Obtener el registro de un hábito para una fecha específica
     * 
     * GET /api/habitos/{habito}/registro/{fecha}
     */
    public function obtenerPorFecha($habitoId, $fecha)
    {
        $habito = Habito::where('id', $habitoId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $registro = RegistroDiario::where('habito_id', $habito->id)
            ->where('fecha', $fecha)
            ->first();

        if (!$registro) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No hay registro para esta fecha',
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $registro,
        ], 200);
    }

    /**
     * Obtener estadísticas de un hábito
     * 
     * GET /api/habitos/{habito}/estadisticas-detalladas
     */
    public function estadisticasDetalladas($habitoId, Request $request)
    {
        $request->validate([
            'periodo' => 'nullable|in:semana,mes,trimestre,año,total',
        ]);

        $habito = Habito::where('id', $habitoId)
            ->where('user_id', Auth::id())
            ->with('registrosDiarios')
            ->firstOrFail();

        $periodo = $request->input('periodo', 'mes');
        
        // Determinar rango de fechas según periodo
        switch ($periodo) {
            case 'semana':
                $fechaInicio = Carbon::now()->startOfWeek();
                break;
            case 'mes':
                $fechaInicio = Carbon::now()->startOfMonth();
                break;
            case 'trimestre':
                $fechaInicio = Carbon::now()->startOfQuarter();
                break;
            case 'año':
                $fechaInicio = Carbon::now()->startOfYear();
                break;
            default:
                $fechaInicio = $habito->fecha_inicio ?? Carbon::now()->subYear();
        }

        $registros = RegistroDiario::where('habito_id', $habito->id)
            ->where('fecha', '>=', $fechaInicio)
            ->get();

        $estadisticas = [
            'total_dias' => $registros->count(),
            'dias_completados' => $registros->where('estado', 'completado')->count(),
            'dias_parciales' => $registros->where('estado', 'parcial')->count(),
            'dias_perdidos' => $registros->where('estado', 'perdido')->count(),
            'veces_total' => $registros->sum('veces_completado'),
            'promedio_veces_por_dia' => $registros->avg('veces_completado'),
            'racha_actual' => $habito->racha_actual,
            'racha_maxima' => $habito->racha_maxima,
            'tasa_completitud' => $registros->count() > 0 
                ? round(($registros->where('estado', 'completado')->count() / $registros->count()) * 100, 2)
                : 0,
            'periodo' => $periodo,
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_fin' => Carbon::now()->toDateString(),
        ];

        return response()->json([
            'success' => true,
            'data' => $estadisticas,
        ], 200);
    }

    /**
     * Actualizar la racha del hábito
     */
    private function actualizarRacha(Habito $habito)
    {
        // Obtener todos los registros completados ordenados por fecha
        $registrosCompletados = RegistroDiario::where('habito_id', $habito->id)
            ->where('estado', 'completado')
            ->orderBy('fecha', 'desc')
            ->get();

        if ($registrosCompletados->isEmpty()) {
            $habito->racha_actual = 0;
            $habito->save();
            return;
        }

        // Calcular racha actual
        $rachaActual = 0;
        $fechaActual = Carbon::today();
        
        foreach ($registrosCompletados as $registro) {
            $fechaRegistro = Carbon::parse($registro->fecha);
            
            // Si es el primer registro y es de hoy o ayer, empieza la racha
            if ($rachaActual === 0) {
                if ($fechaRegistro->isSameDay($fechaActual) || $fechaRegistro->isSameDay($fechaActual->copy()->subDay())) {
                    $rachaActual = 1;
                    $fechaActual = $fechaRegistro;
                } else {
                    // Si el registro más reciente no es de hoy ni ayer, no hay racha
                    break;
                }
            } else {
                // Verificar si el registro es del día anterior
                if ($fechaRegistro->isSameDay($fechaActual->copy()->subDay())) {
                    $rachaActual++;
                    $fechaActual = $fechaRegistro;
                } else {
                    // Si hay un salto de días, se rompe la racha
                    break;
                }
            }
        }

        // Actualizar racha actual
        $habito->racha_actual = $rachaActual;

        // Actualizar racha máxima si es necesario
        if ($rachaActual > $habito->racha_maxima) {
            $habito->racha_maxima = $rachaActual;
        }

        $habito->save();
    }
}
