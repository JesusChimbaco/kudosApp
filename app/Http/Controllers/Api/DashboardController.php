<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habito;
use App\Models\Objetivo;
use App\Models\RegistroDiario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obtener estadísticas del dashboard del usuario autenticado
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        try {
            $user = Auth::user();
            $hoy = Carbon::today();
            $inicioSemana = $hoy->copy()->startOfWeek();
            $finSemana = $hoy->copy()->endOfWeek();
            $inicioMes = $hoy->copy()->startOfMonth();
            $finMes = $hoy->copy()->endOfMonth();

            // Obtener hábitos activos del usuario con objetivos
            $habitosActivos = $user->habitos()
                ->with(['objetivo'])
                ->where('activo', true)
                ->get();

            // Calcular progreso semanal
            $progresoSemanal = $this->calcularProgresoSemanal($habitosActivos, $inicioSemana, $finSemana);
            
            // Calcular progreso mensual
            $progresoMensual = $this->calcularProgresoMensual($habitosActivos, $inicioMes, $finMes);
            
            // Calcular racha global
            $rachaGlobal = $this->calcularRachaGlobal($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'semanal' => $progresoSemanal,
                    'mensual' => $progresoMensual,
                    'rachaGlobal' => $rachaGlobal
                ],
                'message' => 'Estadísticas obtenidas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular progreso semanal basado en objetivos
     */
    private function calcularProgresoSemanal($habitos, $inicioSemana, $finSemana)
    {
        $objetivosIds = $habitos->whereNotNull('objetivo_id')
            ->pluck('objetivo_id')
            ->unique();

        $totalObjetivos = count($objetivosIds);
        $objetivosConProgreso = 0;

        foreach ($objetivosIds as $objetivoId) {
            $habitosDelObjetivo = $habitos->where('objetivo_id', $objetivoId);
            $tieneProgreso = false;

            foreach ($habitosDelObjetivo as $habito) {
                $registros = RegistroDiario::where('habito_id', $habito->id)
                    ->where('completado', true)
                    ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
                    ->exists();

                if ($registros) {
                    $tieneProgreso = true;
                    break;
                }
            }

            if ($tieneProgreso) {
                $objetivosConProgreso++;
            }
        }

        $porcentaje = $totalObjetivos > 0 ? round(($objetivosConProgreso / $totalObjetivos) * 100, 1) : 0;

        return [
            'completados' => $objetivosConProgreso,
            'total' => $totalObjetivos,
            'porcentaje' => $porcentaje
        ];
    }

    /**
     * Calcular progreso mensual basado en objetivos
     */
    private function calcularProgresoMensual($habitos, $inicioMes, $finMes)
    {
        $objetivosIds = $habitos->whereNotNull('objetivo_id')
            ->pluck('objetivo_id')
            ->unique();

        $totalObjetivos = count($objetivosIds);
        $objetivosConProgreso = 0;

        foreach ($objetivosIds as $objetivoId) {
            $habitosDelObjetivo = $habitos->where('objetivo_id', $objetivoId);
            $tieneProgreso = false;

            foreach ($habitosDelObjetivo as $habito) {
                $registros = RegistroDiario::where('habito_id', $habito->id)
                    ->where('completado', true)
                    ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
                    ->exists();

                if ($registros) {
                    $tieneProgreso = true;
                    break;
                }
            }

            if ($tieneProgreso) {
                $objetivosConProgreso++;
            }
        }

        $porcentaje = $totalObjetivos > 0 ? round(($objetivosConProgreso / $totalObjetivos) * 100, 1) : 0;

        return [
            'completados' => $objetivosConProgreso,
            'total' => $totalObjetivos,
            'porcentaje' => $porcentaje
        ];
    }

    /**
     * Calcular racha global del usuario
     * La racha se mantiene si al menos un hábito fue completado cada día
     */
    private function calcularRachaGlobal($user)
    {
        $fechaActual = Carbon::today();
        $racha = 0;

        // Comenzar desde ayer hacia atrás
        $fecha = $fechaActual->copy()->subDay();

        while (true) {
            // Verificar si hay al menos un hábito completado en esta fecha
            $habitoCompletado = RegistroDiario::whereHas('habito', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('fecha', $fecha->toDateString())
            ->where('completado', true)
            ->exists();

            if ($habitoCompletado) {
                $racha++;
                $fecha->subDay();
            } else {
                break;
            }

            // Límite de seguridad para evitar bucles infinitos
            if ($racha >= 365) {
                break;
            }
        }

        return $racha;
    }

    /**
     * Obtener resumen de objetivos del usuario
     */
    public function objetivosResumen()
    {
        try {
            $user = Auth::user();
            
            $objetivos = Objetivo::where('user_id', $user->id)
                ->with(['habitos' => function ($query) {
                    $query->where('activo', true);
                }])
                ->where('activo', true)
                ->get();

            $resumen = $objetivos->map(function ($objetivo) {
                $habitosCount = $objetivo->habitos->count();
                $habitosConProgreso = 0;

                foreach ($objetivo->habitos as $habito) {
                    $registrosRecientes = RegistroDiario::where('habito_id', $habito->id)
                        ->where('completado', true)
                        ->where('fecha', '>=', Carbon::now()->subWeek()->toDateString())
                        ->exists();

                    if ($registrosRecientes) {
                        $habitosConProgreso++;
                    }
                }

                $progreso = $habitosCount > 0 ? round(($habitosConProgreso / $habitosCount) * 100, 1) : 0;

                return [
                    'id' => $objetivo->id,
                    'nombre' => $objetivo->nombre,
                    'descripcion' => $objetivo->descripcion,
                    'emoji' => $objetivo->emoji,
                    'color' => $objetivo->color,
                    'tipo' => $objetivo->tipo,
                    'habitos_count' => $habitosCount,
                    'progreso' => $progreso,
                    'completado' => $objetivo->completado
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $resumen,
                'message' => 'Resumen de objetivos obtenido correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el resumen de objetivos: ' . $e->getMessage()
            ], 500);
        }
    }
}
