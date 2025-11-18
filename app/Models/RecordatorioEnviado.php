<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordatorioEnviado extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'recordatorios_enviados';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recordatorio_id',
        'habito_id',
        'fecha_envio',
        'hora_envio',
        'seguimiento_enviado',
        'seguimiento_enviado_at',
        'completado',
        'completado_at',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_envio' => 'date',
        'seguimiento_enviado' => 'boolean',
        'seguimiento_enviado_at' => 'datetime',
        'completado' => 'boolean',
        'completado_at' => 'datetime',
    ];

    /**
     * Relación: Un recordatorio enviado pertenece a un recordatorio.
     */
    public function recordatorio(): BelongsTo
    {
        return $this->belongsTo(Recordatorio::class);
    }

    /**
     * Relación: Un recordatorio enviado pertenece a un hábito.
     */
    public function habito(): BelongsTo
    {
        return $this->belongsTo(Habito::class);
    }

    /**
     * Scope para recordatorios que necesitan seguimiento.
     */
    public function scopeNecesitaSeguimiento($query)
    {
        return $query->where('seguimiento_enviado', false)
                     ->where('completado', false)
                     ->whereNotNull('created_at');
    }
}
