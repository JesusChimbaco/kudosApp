<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Habito> $habitos
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Logro> $logros
 * @property-read int|null $habitos_count
 * @property-read int|null $logros_count
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nombre',
        'email',
        'password',
        'fecha_registro',
        'tema',
        'notificaciones_activas',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'fecha_registro' => 'datetime',
            'notificaciones_activas' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Relación: Un usuario tiene muchos hábitos.
     * Equivalente a @OneToMany en Spring Boot
     */
    public function habitos(): HasMany
    {
        return $this->hasMany(Habito::class);
    }

    /**
     * Relación: Muchos a Muchos con Logro (tabla pivote: logro_usuario).
     * Equivalente a @ManyToMany en Spring Boot
     */
    public function logros(): BelongsToMany
    {
        return $this->belongsToMany(Logro::class, 'logro_usuario')
            ->withPivot('fecha_obtenido', 'habito_id');
    }

    /**
     * Scope para obtener solo usuarios activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}

