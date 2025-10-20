# 🔍 Verificación de Conexión a PostgreSQL

## ✅ La conexión SÍ está funcionando

He verificado y tu aplicación Laravel **SÍ está conectada** a PostgreSQL:
- Base de datos: `kudos_BD`
- Host: `127.0.0.1:5432`
- Usuario: `postgres`
- Total de tablas: **22 tablas**
- Total de usuarios registrados: **2 usuarios**

---

## 🤔 ¿Por qué no ves los usuarios en pgAdmin?

### Posibles razones:

### 1️⃣ **Estás viendo la tabla incorrecta**

Laravel usa la tabla **`users`** (en plural), NO `usuario`.

En pgAdmin, busca:
```
kudos_BD → Schemas → public → Tables → users
```

**NO busques:** `usuario` (esa es de tu SQL original)

---

### 2️⃣ **Necesitas refrescar pgAdmin**

Después de registrar un usuario:
1. Click derecho en `Tables`
2. Selecciona **"Refresh"**
3. Luego abre la tabla `users`

---

### 3️⃣ **Caché de transacciones**

Si registraste el usuario pero hubo un error, es posible que la transacción no se haya completado.

---

## 🧪 Verificación Manual

### Opción 1: Usando Tinker (Recomendado)

```bash
php artisan tinker
```

Luego ejecuta:

```php
// Ver total de usuarios
User::count();

// Ver todos los usuarios
User::all();

// Ver usuarios con campos específicos
User::all(['id', 'name', 'nombre', 'email', 'created_at']);

// Ver el último usuario registrado
User::latest()->first();
```

### Opción 2: Directamente en pgAdmin

1. Abre pgAdmin
2. Conecta a tu servidor PostgreSQL
3. Navega a: `Databases → kudos_BD → Schemas → public → Tables`
4. Click derecho en `users` → **Refresh**
5. Click derecho en `users` → `View/Edit Data` → `All Rows`

También puedes ejecutar esta query SQL en pgAdmin:

```sql
SELECT id, name, nombre, email, created_at 
FROM users 
ORDER BY created_at DESC;
```

---

## 📋 Nombres de Tablas en Laravel vs tu SQL

| Tu SQL Original | Laravel (Real) |
|-----------------|----------------|
| `usuario` | `users` |
| `categoria` | `categorias` |
| `habito` | `habitos` |
| `registro_diario` | `registro_diarios` |
| `recordatorio` | `recordatorios` |
| `logro` | `logros` |
| `logro_usuario` | `logro_usuario` |

**Laravel usa PLURAL en inglés para `users`**, pero mantenemos español para las demás tablas.

---

## 🔬 Script de Diagnóstico Completo

Crea este archivo y ejecútalo:

**Archivo:** `diagnostico-db.php` (en la raíz del proyecto)

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DIAGNÓSTICO DE BASE DE DATOS ===\n\n";

// 1. Verificar conexión
try {
    DB::connection()->getPdo();
    echo "✅ Conexión exitosa a PostgreSQL\n";
    echo "   Base de datos: " . DB::connection()->getDatabaseName() . "\n\n";
} catch (\Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 2. Verificar tablas
echo "📋 Tablas en la base de datos:\n";
$tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
foreach ($tables as $table) {
    echo "   - {$table->tablename}\n";
}
echo "\n";

// 3. Verificar usuarios
echo "👥 Usuarios registrados:\n";
$users = App\Models\User::all(['id', 'name', 'nombre', 'email', 'created_at']);
echo "   Total: " . $users->count() . " usuarios\n\n";

if ($users->count() > 0) {
    echo "   Lista de usuarios:\n";
    foreach ($users as $user) {
        echo "   ID: {$user->id} | {$user->nombre} ({$user->email}) | Creado: {$user->created_at}\n";
    }
} else {
    echo "   ⚠️  No hay usuarios registrados\n";
}

echo "\n";

// 4. Verificar otras tablas
echo "📊 Resumen de datos:\n";
echo "   Categorías: " . App\Models\Categoria::count() . "\n";
echo "   Logros: " . App\Models\Logro::count() . "\n";
echo "   Hábitos: " . App\Models\Habito::count() . "\n";

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
```

**Ejecutar:**
```bash
php diagnostico-db.php
```

---

## 🎯 Prueba Rápida: Crear un Usuario

Ejecuta esto en Tinker para crear un usuario de prueba:

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Test Database',
    'nombre' => 'Usuario de Prueba',
    'email' => 'test-db@example.com',
    'password' => bcrypt('password'),
    'tema' => 'claro',
    'notificaciones_activas' => true,
    'activo' => true
]);

echo "Usuario creado con ID: " . $user->id;
```

Ahora ve a pgAdmin y ejecuta:

```sql
SELECT * FROM users WHERE email = 'test-db@example.com';
```

Si aparece, **todo está funcionando correctamente**.

---

## 🐛 Problemas Comunes

### Problema 1: "No veo la tabla users"

**Solución:**
- Asegúrate de estar en la base de datos correcta (`kudos_BD`)
- Refresca el árbol de tablas en pgAdmin
- Verifica que las migraciones se ejecutaron: `php artisan migrate:status`

### Problema 2: "El usuario se registró pero no aparece"

**Solución:**
1. Verifica en Tinker: `User::latest()->first()`
2. Si aparece ahí pero no en pgAdmin, es un problema de caché de pgAdmin
3. Cierra y vuelve a abrir pgAdmin

### Problema 3: "Tabla 'usuario' no existe"

**Solución:**
- Laravel usa `users`, no `usuario`
- Si tienes ambas tablas, probablemente ejecutaste el SQL original antes de las migraciones
- Puedes eliminar la tabla `usuario` si no la estás usando:
  ```sql
  DROP TABLE IF EXISTS usuario CASCADE;
  ```

---

## 📝 Comandos Útiles

```bash
# Ver estado de migraciones
php artisan migrate:status

# Ver configuración de base de datos
php artisan db:show

# Acceder a Tinker
php artisan tinker

# Limpiar caché
php artisan config:clear
php artisan cache:clear

# Ver logs
tail -f storage/logs/laravel.log
```

---

## ✅ Checklist de Verificación

- [ ] La tabla se llama `users` (no `usuario`)
- [ ] Refrescar pgAdmin después de crear usuarios
- [ ] Verificar en Tinker que el usuario existe
- [ ] Ejecutar: `SELECT * FROM users;` en pgAdmin
- [ ] Verificar que estás en la base de datos `kudos_BD`
- [ ] Las migraciones están ejecutadas (`php artisan migrate:status`)

---

¡Si sigues estos pasos, deberías ver tus usuarios sin problema! 🎉
