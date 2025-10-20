<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habito extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'habitos';

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
        'frecuencia',
        'dias_semana',
        'veces_por_semana',
        'objetivo_diario',
        'hora_preferida',
        'racha_actual',
        'racha_maxima',
        'activo',
        'fecha_inicio',
        'fecha_fin',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'hora_preferida' => 'datetime',
        'activo' => 'boolean',
        'veces_por_semana' => 'integer',
        'objetivo_diario' => 'integer',
        'racha_actual' => 'integer',
        'racha_maxima' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Un hábito pertenece a un usuario.
     * Equivalente a @ManyToOne en Spring Boot
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Un hábito tiene muchos registros diarios.
     * Equivalente a @OneToMany en Spring Boot
     */
    public function registrosDiarios(): HasMany
    {
        return $this->hasMany(RegistroDiario::class);
    }

    /**
     * Relación: Un hábito tiene muchos recordatorios.
     * Equivalente a @OneToMany en Spring Boot
     */
    public function recordatorios(): HasMany
    {
        return $this->hasMany(Recordatorio::class);
    }

    /**
     * Scope para obtener solo hábitos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por frecuencia.
     */
    public function scopePorFrecuencia($query, $frecuencia)
    {
        return $query->where('frecuencia', $frecuencia);
    }

    /**
     * Accessor: Obtener los días de la semana como array.
     */
    public function getDiasSemanArrayAttribute()
    {
        return $this->dias_semana ? explode(',', $this->dias_semana) : [];
    }

    /**
     * Mutator: Establecer los días de la semana desde un array.
     */
    public function setDiasSemanArrayAttribute($value)
    {
        $this->attributes['dias_semana'] = is_array($value) ? implode(',', $value) : $value;
    }
}

