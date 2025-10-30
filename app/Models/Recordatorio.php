<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recordatorio extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'recordatorios';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'habito_id',
        'activo',
        'hora',
        'dias_semana',
        'tipo',
        'mensaje_personalizado',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Un recordatorio pertenece a un hábito.
     * Equivalente a @ManyToOne en Spring Boot
     */
    public function habito(): BelongsTo
    {
        return $this->belongsTo(Habito::class);
    }

    /**
     * Scope para obtener solo recordatorios activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por tipo.
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Accessor: Obtener los días de la semana como array.
     */
    public function getDiasSemanArrayAttribute()
    {
        return $this->dias_semana ? explode(',', $this->dias_semana) : [];
    }
}

