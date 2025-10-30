<?php

namespace App\Console\Commands;

use App\Jobs\EnviarRecordatorioHabito;
use App\Models\Recordatorio;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnviarRecordatoriosHabitos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recordatorios:enviar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de hábitos por email según la hora configurada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $horaActual = $now->format('H:i');
        $diaActual = $this->obtenerDiaActual($now);

        $this->info("Verificando recordatorios para {$horaActual} - {$diaActual}");

        // Obtener recordatorios activos de tipo email que coincidan con la hora actual
        // Usamos whereRaw para comparar solo la parte de hora ignorando la fecha
        $recordatorios = Recordatorio::activos()
            ->porTipo('email')
            ->whereRaw("TO_CHAR(hora, 'HH24:MI') = ?", [$horaActual])
            ->with('habito.user')
            ->get();

        $enviados = 0;
        $saltados = 0;

        foreach ($recordatorios as $recordatorio) {
            // Verificar si hoy es un día válido para este recordatorio
            if (!$this->esDiaValido($recordatorio, $diaActual)) {
                $saltados++;
                continue;
            }

            // Verificar que el hábito existe y está activo
            if (!$recordatorio->habito || !$recordatorio->habito->activo) {
                $saltados++;
                continue;
            }

            // Despachar el job a la cola
            EnviarRecordatorioHabito::dispatch($recordatorio);
            $enviados++;

            $this->line("✓ Recordatorio despachado: {$recordatorio->habito->nombre} para {$recordatorio->habito->user->name}");
        }

        $this->info("Proceso completado: {$enviados} recordatorios despachados, {$saltados} saltados");

        Log::info('Comando de recordatorios ejecutado', [
            'hora' => $horaActual,
            'dia' => $diaActual,
            'enviados' => $enviados,
            'saltados' => $saltados
        ]);

        return Command::SUCCESS;
    }

    /**
     * Obtiene la letra del día actual (L, M, X, J, V, S, D)
     */
    private function obtenerDiaActual(Carbon $fecha): string
    {
        $dias = [
            0 => 'D', // Domingo
            1 => 'L', // Lunes
            2 => 'M', // Martes
            3 => 'X', // Miércoles
            4 => 'J', // Jueves
            5 => 'V', // Viernes
            6 => 'S', // Sábado
        ];

        return $dias[$fecha->dayOfWeek];
    }

    /**
     * Verifica si el día actual está en los días configurados del recordatorio
     */
    private function esDiaValido(Recordatorio $recordatorio, string $diaActual): bool
    {
        // Si no hay días configurados, se asume que es todos los días
        if (empty($recordatorio->dias_semana)) {
            return true;
        }

        // Convertir la cadena de días en array (formato: "L,M,X,J,V")
        $diasConfigurados = array_map('trim', explode(',', $recordatorio->dias_semana));

        return in_array($diaActual, $diasConfigurados);
    }
}
