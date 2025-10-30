<?php

namespace App\Jobs;

use App\Mail\RecordatorioHabito;
use App\Models\Recordatorio;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EnviarRecordatorioHabito implements ShouldQueue
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
     * Create a new job instance.
     */
    public function __construct(
        public Recordatorio $recordatorio
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Verificar que el recordatorio esté activo
            if (!$this->recordatorio->activo) {
                Log::info('Recordatorio inactivo, saltando envío', [
                    'recordatorio_id' => $this->recordatorio->id
                ]);
                return;
            }

            // Cargar las relaciones necesarias
            $this->recordatorio->load('habito.user');
            
            $habito = $this->recordatorio->habito;
            $usuario = $habito->user;

            // Verificar que el usuario tiene email
            if (!$usuario->email) {
                Log::warning('Usuario sin email, no se puede enviar recordatorio', [
                    'user_id' => $usuario->id,
                    'recordatorio_id' => $this->recordatorio->id
                ]);
                return;
            }

            // Enviar el email
            Mail::to($usuario->email)->send(
                new RecordatorioHabito(
                    user: $usuario,
                    habito: $habito,
                    mensajePersonalizado: $this->recordatorio->mensaje_personalizado
                )
            );

            Log::info('Recordatorio enviado exitosamente', [
                'recordatorio_id' => $this->recordatorio->id,
                'habito_id' => $habito->id,
                'user_id' => $usuario->id,
                'email' => $usuario->email
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar recordatorio', [
                'recordatorio_id' => $this->recordatorio->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-lanzar la excepción para que el job se reintente
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de recordatorio falló definitivamente después de todos los intentos', [
            'recordatorio_id' => $this->recordatorio->id,
            'error' => $exception->getMessage()
        ]);
    }
}
