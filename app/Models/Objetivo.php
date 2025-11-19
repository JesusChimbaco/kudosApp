<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Objetivo extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'objetivos';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nombre',
        'descripcion',
        'emoji',
        'color',
        'tipo',
        'fecha_inicio',
        'fecha_objetivo',
        'completado',
        'fecha_completado',
        'activo',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_objetivo' => 'date',
        'fecha_completado' => 'date',
        'completado' => 'boolean',
        'activo' => 'boolean',
    ];

    /**
     * Relación: Un objetivo pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Un objetivo tiene muchos hábitos.
     */
    public function habitos(): HasMany
    {
        return $this->hasMany(Habito::class);
    }

    /**
     * Scope para obtener solo objetivos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener solo objetivos completados.
     */
    public function scopeCompletados($query)
    {
        return $query->where('completado', true);
    }

    /**
     * Scope para obtener solo objetivos pendientes.
     */
    public function scopePendientes($query)
    {
        return $query->where('completado', false)->where('activo', true);
    }
}
