<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar el envío de recordatorios cada minuto
Schedule::command('recordatorios:enviar')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Verificar recordatorios de seguimiento cada minuto
Schedule::command('recordatorios:verificar-seguimiento')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
