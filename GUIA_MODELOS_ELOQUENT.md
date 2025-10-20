# 📚 Guía de Modelos Eloquent - Para desarrolladores Spring Boot

## 🎯 Comparación: Spring Boot JPA vs Laravel Eloquent

### Spring Boot (JPA/Hibernate)
```java
@Entity
@Table(name = "habito")
public class Habito {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    
    @Column(name = "nombre", length = 150, nullable = false)
    private String nombre;
    
    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "usuario_id", nullable = false)
    private Usuario usuario;
    
    @OneToMany(mappedBy = "habito", cascade = CascadeType.ALL)
    private List<RegistroDiario> registros;
}
```

### Laravel (Eloquent ORM)
```php
class Habito extends Model {
    protected $fillable = ['nombre', 'user_id'];
    
    // @ManyToOne
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
    
    // @OneToMany
    public function registrosDiarios(): HasMany {
        return $this->hasMany(RegistroDiario::class);
    }
}
```

## 📋 Modelos Creados

### ✅ 1. User (Usuario)
**Archivo:** `app/Models/User.php`

**Relaciones:**
- `hasMany(Habito)` - Un usuario tiene muchos hábitos
- `belongsToMany(Logro)` - Relación muchos a muchos con logros

**Atributos especiales:**
- `nombre` - Nombre del usuario
- `tema` - Tema de la interfaz (claro/oscuro)
- `notificaciones_activas` - Si tiene notificaciones activadas
- `activo` - Si el usuario está activo

**Ejemplo de uso:**
```php
// Obtener todos los hábitos de un usuario
$user = User::find(1);
$habitos = $user->habitos;

// Obtener logros obtenidos
$logros = $user->logros;

// Crear un nuevo hábito para el usuario
$user->habitos()->create([
    'nombre' => 'Hacer ejercicio',
    'frecuencia' => 'diario',
    'objetivo_diario' => 1
]);
```

---

### ✅ 2. Categoria
**Archivo:** `app/Models/Categoria.php`

**Atributos:**
- `nombre` - Nombre de la categoría
- `descripcion` - Descripción
- `icono` - Emoji o icono
- `color` - Color en formato hexadecimal
- `activo` - Si está activa

**Scopes (equivalente a Query Methods en Spring):**
- `activas()` - Solo categorías activas

**Ejemplo de uso:**
```php
// Obtener todas las categorías activas
$categorias = Categoria::activas()->get();

// Crear una nueva categoría
Categoria::create([
    'nombre' => 'Deporte',
    'icono' => '⚽',
    'color' => '#FF5722'
]);
```

---

### ✅ 3. Habito
**Archivo:** `app/Models/Habito.php`

**Relaciones:**
- `belongsTo(User)` - Pertenece a un usuario
- `hasMany(RegistroDiario)` - Tiene muchos registros diarios
- `hasMany(Recordatorio)` - Tiene muchos recordatorios

**Atributos principales:**
- `nombre` - Nombre del hábito
- `frecuencia` - diario, semanal, mensual, personalizado
- `racha_actual` / `racha_maxima` - Rachas de días consecutivos
- `objetivo_diario` - Cuántas veces al día
- `activo` - Si está activo

**Scopes:**
- `activos()` - Solo hábitos activos
- `porFrecuencia($frecuencia)` - Filtrar por frecuencia

**Accessors/Mutators:**
- `dias_seman_array` - Convierte "L,M,X" a array y viceversa

**Ejemplo de uso:**
```php
// Crear un hábito diario
$habito = Habito::create([
    'user_id' => 1,
    'nombre' => 'Meditar 10 minutos',
    'frecuencia' => 'diario',
    'objetivo_diario' => 1,
    'emoji' => '🧘',
    'racha_actual' => 0
]);

// Obtener hábitos activos del usuario
$habitosActivos = User::find(1)->habitos()->activos()->get();

// Filtrar hábitos diarios
$habitosDiarios = Habito::porFrecuencia('diario')->get();

// Trabajar con días de la semana
$habito->dias_seman_array = ['L', 'M', 'X', 'V']; // Mutator
$dias = $habito->dias_seman_array; // Accessor: ['L', 'M', 'X', 'V']
```

---

### ✅ 4. RegistroDiario
**Archivo:** `app/Models/RegistroDiario.php`

**Relaciones:**
- `belongsTo(Habito)` - Pertenece a un hábito

**Atributos:**
- `fecha` - Fecha del registro
- `completado` - Boolean
- `estado` - completado, parcial, perdido, pendiente
- `veces_completado` - Contador
- `notas` - Notas del día

**Scopes:**
- `completados()` - Solo completados
- `porEstado($estado)` - Filtrar por estado
- `entreFechas($inicio, $fin)` - Rango de fechas

**Ejemplo de uso:**
```php
// Registrar completado hoy
$registro = RegistroDiario::create([
    'habito_id' => 1,
    'fecha' => today(),
    'completado' => true,
    'estado' => 'completado',
    'veces_completado' => 1,
    'hora_completado' => now()
]);

// Obtener registros completados del mes
$registros = RegistroDiario::completados()
    ->entreFechas(now()->startOfMonth(), now()->endOfMonth())
    ->get();

// Estadísticas del hábito
$habito = Habito::find(1);
$totalCompletados = $habito->registrosDiarios()
    ->completados()
    ->count();
```

---

### ✅ 5. Recordatorio
**Archivo:** `app/Models/Recordatorio.php`

**Relaciones:**
- `belongsTo(Habito)` - Pertenece a un hábito

**Atributos:**
- `hora` - Hora del recordatorio
- `tipo` - push, email
- `dias_semana` - Días que se activa
- `activo` - Si está activo

**Scopes:**
- `activos()` - Solo activos
- `porTipo($tipo)` - Filtrar por tipo

**Ejemplo de uso:**
```php
// Crear recordatorio diario
$recordatorio = Recordatorio::create([
    'habito_id' => 1,
    'hora' => '07:00:00',
    'tipo' => 'push',
    'dias_semana' => 'L,M,X,J,V',
    'mensaje_personalizado' => '¡Hora de meditar!'
]);

// Obtener recordatorios activos del hábito
$recordatorios = Habito::find(1)
    ->recordatorios()
    ->activos()
    ->get();
```

---

### ✅ 6. Logro
**Archivo:** `app/Models/Logro.php`

**Relaciones:**
- `belongsToMany(User)` - Muchos a muchos (tabla pivote: logro_usuario)

**Atributos:**
- `codigo` - Código único (RACHA_7, CANTIDAD_100)
- `tipo` - racha, cantidad, tiempo, especial
- `criterio_valor` - Valor necesario para obtenerlo
- `puntos` - Puntos que otorga

**Scopes:**
- `activos()` - Solo activos
- `porTipo($tipo)` - Filtrar por tipo
- `porCodigo($codigo)` - Buscar por código

**Ejemplo de uso:**
```php
// Asignar logro a usuario
$user = User::find(1);
$logro = Logro::porCodigo('RACHA_7')->first();

$user->logros()->attach($logro->id, [
    'fecha_obtenido' => now(),
    'habito_id' => 1
]);

// Obtener todos los logros del usuario
$logrosObtenidos = $user->logros;

// Verificar si tiene un logro específico
$tieneLogro = $user->logros()
    ->where('codigo', 'RACHA_7')
    ->exists();

// Obtener logros de rachas
$logrosRacha = Logro::porTipo('racha')->get();
```

---

## 🔗 Tipos de Relaciones

### 1. One to Many (Uno a Muchos)

**Spring Boot:**
```java
@OneToMany(mappedBy = "usuario")
private List<Habito> habitos;
```

**Laravel:**
```php
public function habitos(): HasMany {
    return $this->hasMany(Habito::class);
}
```

### 2. Many to One (Muchos a Uno)

**Spring Boot:**
```java
@ManyToOne
@JoinColumn(name = "usuario_id")
private Usuario usuario;
```

**Laravel:**
```php
public function user(): BelongsTo {
    return $this->belongsTo(User::class);
}
```

### 3. Many to Many (Muchos a Muchos)

**Spring Boot:**
```java
@ManyToMany
@JoinTable(name = "logro_usuario",
    joinColumns = @JoinColumn(name = "usuario_id"),
    inverseJoinColumns = @JoinColumn(name = "logro_id"))
private List<Logro> logros;
```

**Laravel:**
```php
public function logros(): BelongsToMany {
    return $this->belongsToMany(Logro::class, 'logro_usuario')
        ->withPivot('fecha_obtenido', 'habito_id')
        ->withTimestamps();
}
```

---

## 🎨 Características Especiales de Eloquent

### 1. Scopes (Query Methods)

**Spring Boot:**
```java
List<Habito> findByActivoTrue();
List<Habito> findByFrecuencia(String frecuencia);
```

**Laravel:**
```php
// Definir en el modelo
public function scopeActivos($query) {
    return $query->where('activo', true);
}

// Usar
$habitos = Habito::activos()->get();
```

### 2. Accessors y Mutators (Getters/Setters automáticos)

**Accessor** (Getter):
```php
public function getDiasSemanArrayAttribute() {
    return explode(',', $this->dias_semana);
}

// Uso: $habito->dias_seman_array
```

**Mutator** (Setter):
```php
public function setDiasSemanArrayAttribute($value) {
    $this->attributes['dias_semana'] = implode(',', $value);
}

// Uso: $habito->dias_seman_array = ['L', 'M', 'X'];
```

### 3. Mass Assignment (Asignación Masiva)

```php
// Equivalente a setters en Spring Boot
protected $fillable = ['nombre', 'email', 'password'];

// Uso
$user = User::create([
    'nombre' => 'Juan',
    'email' => 'juan@example.com'
]);
```

---

## 💡 Consultas Comunes

### Eager Loading (evitar N+1)

**Spring Boot:**
```java
@EntityGraph(attributePaths = {"habitos", "logros"})
User findById(Long id);
```

**Laravel:**
```php
$user = User::with(['habitos', 'logros'])->find(1);
```

### Consultas Complejas

```php
// Obtener usuarios con al menos 5 hábitos activos
$usuarios = User::has('habitos', '>=', 5)->get();

// Obtener hábitos con registros completados hoy
$habitos = Habito::whereHas('registrosDiarios', function($query) {
    $query->where('fecha', today())
          ->where('completado', true);
})->get();

// Contar registros por hábito
$habito = Habito::withCount('registrosDiarios')->find(1);
echo $habito->registros_diarios_count;
```

---

## 🚀 Ejemplos Prácticos

### Crear un hábito completo con recordatorios

```php
$habito = Habito::create([
    'user_id' => auth()->id(),
    'nombre' => 'Leer 30 minutos',
    'frecuencia' => 'diario',
    'objetivo_diario' => 1,
    'emoji' => '📚'
]);

$habito->recordatorios()->create([
    'hora' => '20:00:00',
    'tipo' => 'push',
    'dias_semana' => 'L,M,X,J,V,S,D',
    'activo' => true
]);
```

### Registrar completado y actualizar racha

```php
$habito = Habito::find(1);

// Crear registro del día
$registro = $habito->registrosDiarios()->create([
    'fecha' => today(),
    'completado' => true,
    'estado' => 'completado',
    'veces_completado' => 1,
    'hora_completado' => now()
]);

// Actualizar racha
$habito->increment('racha_actual');
if ($habito->racha_actual > $habito->racha_maxima) {
    $habito->update(['racha_maxima' => $habito->racha_actual]);
}
```

### Asignar logro cuando se cumple criterio

```php
$habito = Habito::find(1);

if ($habito->racha_actual == 7) {
    $logro = Logro::porCodigo('RACHA_7')->first();
    
    $habito->user->logros()->attach($logro->id, [
        'fecha_obtenido' => now(),
        'habito_id' => $habito->id
    ]);
}
```

---

## 📖 Recursos

- [Eloquent ORM Documentation](https://laravel.com/docs/eloquent)
- [Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [Query Builder](https://laravel.com/docs/queries)

---

¡Modelos Eloquent listos para usar! 🎉
