<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Logro extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'logros';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'icono',
        'tipo',
        'criterio_valor',
        'puntos',
        'activo',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'criterio_valor' => 'integer',
        'puntos' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Muchos a Muchos con User (tabla pivote: logro_usuario).
     * Equivalente a @ManyToMany en Spring Boot
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'logro_usuario')
            ->withPivot('fecha_obtenido', 'habito_id')
            ->withTimestamps();
    }

    /**
     * Scope para obtener solo logros activos.
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
     * Scope para buscar por código.
     */
    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
    }
}

