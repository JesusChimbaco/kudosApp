<?php

namespace App\Console\Commands;

use App\Jobs\EnviarRecordatorioSeguimiento;
use App\Models\RecordatorioEnviado;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VerificarRecordatoriosSeguimiento extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recordatorios:verificar-seguimiento';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica y envía recordatorios de seguimiento para hábitos no completados después del tiempo especificado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando recordatorios que necesitan seguimiento...');
        
        try {
            // Buscar recordatorios enviados que necesitan seguimiento
            $recordatoriosPendientes = RecordatorioEnviado::with('recordatorio', 'habito')
                ->where('seguimiento_enviado', false)
                ->where('completado', false)
                ->get();

            if ($recordatoriosPendientes->isEmpty()) {
                $this->info('No hay recordatorios pendientes de seguimiento.');
                return Command::SUCCESS;
            }

            $enviados = 0;
            $ahora = Carbon::now();

            foreach ($recordatoriosPendientes as $recordatorioEnviado) {
                // Verificar si el recordatorio tiene seguimiento habilitado
                if (!$recordatorioEnviado->recordatorio->enviar_seguimiento) {
                    continue;
                }

                // Calcular cuántos minutos han pasado desde el envío
                $minutosDesdeEnvio = $recordatorioEnviado->created_at->diffInMinutes($ahora);
                $minutosNecesarios = $recordatorioEnviado->recordatorio->minutos_seguimiento ?? 5;

                // Si ya pasó el tiempo necesario, enviar seguimiento
                if ($minutosDesdeEnvio >= $minutosNecesarios) {
                    // Despachar el job de seguimiento
                    EnviarRecordatorioSeguimiento::dispatch($recordatorioEnviado);
                    $enviados++;

                    $this->info("Programado seguimiento para hábito: {$recordatorioEnviado->habito->nombre}");
                }
            }

            $this->info("Se programaron {$enviados} recordatorios de seguimiento.");
            
            Log::info('Verificación de seguimientos completada', [
                'recordatorios_revisados' => $recordatoriosPendientes->count(),
                'seguimientos_enviados' => $enviados
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error al verificar recordatorios de seguimiento: ' . $e->getMessage());
            
            Log::error('Error en comando de verificación de seguimientos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }
}
