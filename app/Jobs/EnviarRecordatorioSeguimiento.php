<?php

namespace App\Jobs;

use App\Mail\RecordatorioSeguimientoHabito;
use App\Models\RecordatorioEnviado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EnviarRecordatorioSeguimiento implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public RecordatorioEnviado $recordatorioEnviado
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Verificar que no se haya completado ya
            if ($this->recordatorioEnviado->completado) {
                Log::info('Hábito ya completado, cancelando seguimiento', [
                    'recordatorio_enviado_id' => $this->recordatorioEnviado->id
                ]);
                return;
            }

            // Verificar que no se haya enviado seguimiento ya
            if ($this->recordatorioEnviado->seguimiento_enviado) {
                Log::info('Seguimiento ya enviado, saltando', [
                    'recordatorio_enviado_id' => $this->recordatorioEnviado->id
                ]);
                return;
            }

            // Cargar las relaciones necesarias
            $this->recordatorioEnviado->load('habito.user', 'habito.objetivo', 'recordatorio');
            
            $habito = $this->recordatorioEnviado->habito;
            $usuario = $habito->user;
            $objetivo = $habito->objetivo;

            // Verificar que el usuario tiene email
            if (!$usuario->email) {
                Log::warning('Usuario sin email, no se puede enviar seguimiento', [
                    'user_id' => $usuario->id
                ]);
                return;
            }

            // Enviar el email de seguimiento
            Mail::to($usuario->email)->send(
                new RecordatorioSeguimientoHabito(
                    user: $usuario,
                    habito: $habito,
                    objetivo: $objetivo,
                    mensajePersonalizado: $this->recordatorioEnviado->recordatorio->mensaje_personalizado
                )
            );

            // Marcar como seguimiento enviado
            $this->recordatorioEnviado->update([
                'seguimiento_enviado' => true,
                'seguimiento_enviado_at' => now(),
            ]);

            Log::info('Recordatorio de seguimiento enviado exitosamente', [
                'recordatorio_enviado_id' => $this->recordatorioEnviado->id,
                'habito_id' => $habito->id,
                'user_id' => $usuario->id,
                'email' => $usuario->email,
                'objetivo' => $objetivo?->nombre
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar recordatorio de seguimiento', [
                'recordatorio_enviado_id' => $this->recordatorioEnviado->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de seguimiento falló definitivamente', [
            'recordatorio_enviado_id' => $this->recordatorioEnviado->id,
            'error' => $exception->getMessage()
        ]);
    }
}
