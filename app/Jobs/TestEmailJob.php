<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestEmailJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 300;
    public $tries = 1;

    public function handle(): void
    {
        Log::info('=== INICIO TEST EMAIL JOB ===');
        
        try {
            Log::info('Paso 1: Verificando configuración de email', [
                'MAIL_MAILER' => config('mail.default'),
                'MAIL_HOST' => config('mail.mailers.smtp.host'),
                'MAIL_PORT' => config('mail.mailers.smtp.port'),
                'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
                'MAIL_FROM' => config('mail.from.address'),
            ]);

            Log::info('Paso 2: Intentando enviar email de prueba');

            Mail::raw('Este es un email de prueba desde Railway', function ($message) {
                $message->to(config('mail.mailers.smtp.username'))
                    ->subject('Test Email - Railway');
            });

            Log::info('Paso 3: Email enviado exitosamente');
            
        } catch (\Exception $e) {
            Log::error('ERROR EN TEST EMAIL JOB', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
        
        Log::info('=== FIN TEST EMAIL JOB ===');
    }
}
