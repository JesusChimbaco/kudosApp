# 🧪 Test Manual de Modelos Eloquent

Este archivo contiene ejemplos de código que puedes ejecutar en **Tinker** para probar los modelos.

## 🚀 Acceder a Tinker

```bash
php artisan tinker
```

## 📋 Pruebas Paso a Paso

### 1️⃣ Verificar que los modelos están cargados

```php
// Verificar User
App\Models\User::count();

// Verificar Categoria
App\Models\Categoria::count();

// Verificar Logro
App\Models\Logro::count();
```

### 2️⃣ Crear un usuario de prueba

```php
$user = App\Models\User::create([
    'name' => 'Juan Pérez',
    'nombre' => 'Juan Pérez',
    'email' => 'juan@example.com',
    'password' => bcrypt('password'),
    'tema' => 'claro',
    'notificaciones_activas' => true,
    'activo' => true
]);

// Ver el usuario creado
$user;
```

### 3️⃣ Crear un hábito para el usuario

```php
$habito = App\Models\Habito::create([
    'user_id' => $user->id,
    'nombre' => 'Hacer ejercicio 30 min',
    'descripcion' => 'Cardio y estiramiento',
    'emoji' => '🏃',
    'color' => '#FF5722',
    'frecuencia' => 'diario',
    'objetivo_diario' => 1,
    'fecha_inicio' => today(),
    'activo' => true
]);

// Ver el hábito
$habito;
```

### 4️⃣ Probar relaciones (User -> Habitos)

```php
// Obtener hábitos del usuario
$user->habitos;

// Acceso inverso (Habito -> User)
$habito->user;
```

### 5️⃣ Crear un registro diario

```php
$registro = App\Models\RegistroDiario::create([
    'habito_id' => $habito->id,
    'fecha' => today(),
    'completado' => true,
    'estado' => 'completado',
    'veces_completado' => 1,
    'hora_completado' => now()
]);

// Ver registros del hábito
$habito->registrosDiarios;
```

### 6️⃣ Crear un recordatorio

```php
$recordatorio = App\Models\Recordatorio::create([
    'habito_id' => $habito->id,
    'hora' => '07:00:00',
    'tipo' => 'push',
    'dias_semana' => 'L,M,X,J,V',
    'activo' => true,
    'mensaje_personalizado' => '¡Hora de hacer ejercicio!'
]);

// Ver recordatorios del hábito
$habito->recordatorios;
```

### 7️⃣ Asignar un logro al usuario

```php
// Buscar un logro
$logro = App\Models\Logro::where('codigo', 'PRIMER_HABITO')->first();

// Asignar al usuario
$user->logros()->attach($logro->id, [
    'fecha_obtenido' => now(),
    'habito_id' => $habito->id
]);

// Ver logros del usuario
$user->logros;
```

### 8️⃣ Probar Scopes

```php
// Solo categorías activas
App\Models\Categoria::activas()->get();

// Solo hábitos activos
App\Models\Habito::activos()->get();

// Hábitos diarios
App\Models\Habito::porFrecuencia('diario')->get();

// Registros completados
App\Models\RegistroDiario::completados()->get();

// Logros de tipo racha
App\Models\Logro::porTipo('racha')->get();
```

### 9️⃣ Consultas avanzadas

```php
// Eager Loading - Cargar usuario con sus hábitos
$user = App\Models\User::with('habitos')->find(1);

// Contar hábitos del usuario
$user->habitos()->count();

// Usuario con hábitos activos
$user = App\Models\User::with(['habitos' => function($query) {
    $query->where('activo', true);
}])->find(1);

// Hábito con sus registros del mes actual
$habito = App\Models\Habito::with(['registrosDiarios' => function($query) {
    $query->whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()]);
}])->find(1);
```

### 🔟 Actualizar racha

```php
$habito = App\Models\Habito::find(1);

// Incrementar racha
$habito->increment('racha_actual');

// Actualizar racha máxima si es necesario
if ($habito->racha_actual > $habito->racha_maxima) {
    $habito->update(['racha_maxima' => $habito->racha_actual]);
}

$habito->fresh(); // Recargar desde BD
```

---

## 🎯 Ejemplos Completos de Flujos

### Flujo 1: Usuario crea hábito y lo completa por primera vez

```php
// 1. Crear usuario
$user = App\Models\User::factory()->create();

// 2. Crear hábito
$habito = $user->habitos()->create([
    'nombre' => 'Meditar',
    'frecuencia' => 'diario',
    'objetivo_diario' => 1,
    'emoji' => '🧘'
]);

// 3. Asignar logro "Primer Hábito"
$logro = App\Models\Logro::porCodigo('PRIMER_HABITO')->first();
$user->logros()->attach($logro->id, [
    'fecha_obtenido' => now(),
    'habito_id' => $habito->id
]);

// 4. Registrar primer día completado
$registro = $habito->registrosDiarios()->create([
    'fecha' => today(),
    'completado' => true,
    'estado' => 'completado',
    'veces_completado' => 1
]);

// 5. Actualizar racha
$habito->increment('racha_actual');
$habito->increment('racha_maxima');

// Verificar
$user->fresh()->logros;
$habito->fresh();
```

### Flujo 2: Calcular estadísticas del mes

```php
$user = App\Models\User::find(1);
$habito = $user->habitos()->first();

// Registros completados este mes
$completadosEsteMes = $habito->registrosDiarios()
    ->whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()])
    ->where('completado', true)
    ->count();

// Porcentaje de completado
$diasDelMes = now()->daysInMonth;
$porcentaje = ($completadosEsteMes / $diasDelMes) * 100;

echo "Completado: {$completadosEsteMes}/{$diasDelMes} días ({$porcentaje}%)";
```

### Flujo 3: Dashboard del usuario

```php
$user = App\Models\User::with([
    'habitos' => function($query) {
        $query->activos();
    },
    'habitos.registrosDiarios' => function($query) {
        $query->where('fecha', today());
    },
    'logros'
])->find(1);

// Hábitos activos
$habitosActivos = $user->habitos->count();

// Hábitos completados hoy
$completadosHoy = $user->habitos->filter(function($habito) {
    return $habito->registrosDiarios->where('completado', true)->isNotEmpty();
})->count();

// Total de logros
$totalLogros = $user->logros->count();

echo "Hábitos activos: {$habitosActivos}\n";
echo "Completados hoy: {$completadosHoy}/{$habitosActivos}\n";
echo "Logros obtenidos: {$totalLogros}\n";
```

---

## 🐛 Comandos Útiles de Tinker

```php
// Salir de Tinker
exit

// Limpiar pantalla
!clear

// Ver estructura de un modelo
App\Models\Habito::find(1)->toArray();

// Ver relaciones cargadas
App\Models\User::with('habitos')->find(1)->getRelations();

// Última query SQL ejecutada
DB::enableQueryLog();
App\Models\Habito::activos()->get();
DB::getQueryLog();
```

---

## ✅ Verificación Final

Ejecuta esto en Tinker para verificar que todo funciona:

```php
// Verificar que hay datos
echo "Usuarios: " . App\Models\User::count() . "\n";
echo "Categorías: " . App\Models\Categoria::count() . "\n";
echo "Logros: " . App\Models\Logro::count() . "\n";

// Verificar relaciones
$user = App\Models\User::with(['habitos', 'logros'])->first();
if ($user) {
    echo "Usuario: {$user->nombre}\n";
    echo "Hábitos: {$user->habitos->count()}\n";
    echo "Logros: {$user->logros->count()}\n";
}
```

---

¡Prueba los modelos en Tinker! 🚀
