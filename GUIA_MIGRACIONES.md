# 📚 Guía de Migraciones Laravel - Para desarrolladores Spring Boot

## 🎯 Conceptos Clave: Spring Boot vs Laravel

### Spring Boot (JPA/Hibernate)
```java
@Entity
@Table(name = "usuario")
public class Usuario {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    
    @Column(name = "nombre", length = 100, nullable = false)
    private String nombre;
    
    @OneToMany(mappedBy = "usuario", cascade = CascadeType.ALL)
    private List<Habito> habitos;
}
```

### Laravel (Eloquent ORM)
**Migración (define estructura):**
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 100);
    $table->timestamps();
});
```

**Modelo (equivalente a @Entity):**
```php
class User extends Model {
    protected $fillable = ['nombre'];
    
    public function habitos() {
        return $this->hasMany(Habito::class);
    }
}
```

## 📋 Estructura Creada

### Migraciones Generadas:

1. ✅ `2025_10_20_033254_create_categorias_table.php` - Tabla de categorías
2. ✅ `2025_10_20_033300_create_habitos_table.php` - Tabla de hábitos
3. ✅ `2025_10_20_033304_create_registro_diarios_table.php` - Registros diarios
4. ✅ `2025_10_20_033309_create_recordatorios_table.php` - Recordatorios
5. ✅ `2025_10_20_033313_create_logros_table.php` - Logros/Achievements
6. ✅ `2025_10_20_033317_create_logro_usuario_table.php` - Tabla pivote
7. ✅ `2025_10_20_033339_add_campos_adicionales_to_users_table.php` - Campos adicionales en users

### Seeders Generados:

1. ✅ `CategoriaSeeder.php` - Datos iniciales de categorías
2. ✅ `LogroSeeder.php` - Datos iniciales de logros

## 🚀 Comandos para Ejecutar

### 1. Verificar configuración de base de datos

Edita el archivo `.env` con tus credenciales de PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nombre_de_tu_base
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 2. Ejecutar las migraciones

```bash
# Ver el estado de las migraciones
php artisan migrate:status

# Ejecutar todas las migraciones pendientes
php artisan migrate

# Si necesitas empezar desde cero (CUIDADO: borra todo)
php artisan migrate:fresh

# Ejecutar migraciones y seeders juntos
php artisan migrate:fresh --seed
```

### 3. Ejecutar los seeders

```bash
# Ejecutar todos los seeders
php artisan db:seed

# Ejecutar un seeder específico
php artisan db:seed --class=CategoriaSeeder
php artisan db:seed --class=LogroSeeder
```

### 4. Comandos útiles para desarrollo

```bash
# Rollback de la última migración
php artisan migrate:rollback

# Rollback de todas las migraciones
php artisan migrate:reset

# Rollback + migrate de nuevo
php artisan migrate:refresh

# Rollback + migrate + seed
php artisan migrate:refresh --seed
```

## 🔧 Crear nuevas migraciones

```bash
# Crear una nueva tabla
php artisan make:migration create_nombre_tabla_table

# Modificar una tabla existente
php artisan make:migration add_columna_to_tabla_table --table=tabla

# Crear una migración con modelo
php artisan make:model NombreModelo -m
```

## 📊 Tipos de Datos en Migraciones

| PostgreSQL SQL | Laravel Migration |
|---------------|-------------------|
| `BIGSERIAL PRIMARY KEY` | `$table->id()` |
| `VARCHAR(100)` | `$table->string('nombre', 100)` |
| `TEXT` | `$table->text('descripcion')` |
| `BOOLEAN` | `$table->boolean('activo')` |
| `INTEGER` | `$table->integer('cantidad')` |
| `DATE` | `$table->date('fecha')` |
| `TIME` | `$table->time('hora')` |
| `TIMESTAMP` | `$table->timestamp('fecha_hora')` |
| `BIGINT REFERENCES` | `$table->foreignId('user_id')->constrained()` |

## 🔗 Relaciones (Foreign Keys)

### En SQL:
```sql
FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
```

### En Laravel:
```php
$table->foreignId('user_id')
      ->constrained('users')
      ->onDelete('cascade');
```

## 🎨 Índices

### En SQL:
```sql
CREATE INDEX idx_habito_usuario ON habito(usuario_id);
```

### En Laravel:
```php
$table->index('user_id');
```

## ⚠️ Diferencias Importantes

### 1. Nombres de tablas
- **PostgreSQL/SQL**: `usuario`, `habito`, `categoria`
- **Laravel**: `users`, `habitos`, `categorias` (usa plural por convención)

### 2. Timestamps automáticos
Laravel maneja automáticamente `created_at` y `updated_at` con:
```php
$table->timestamps();
```

No necesitas triggers como en PostgreSQL. Laravel lo actualiza automáticamente.

### 3. Soft Deletes (Borrado suave)
En lugar de una columna `activo`, Laravel usa:
```php
$table->softDeletes(); // Crea deleted_at
```

Pero en este caso mantenemos `activo` porque lo requiere tu diseño original.

## 🎓 Siguientes Pasos

### 1. Crear los Modelos Eloquent

```bash
php artisan make:model Categoria
php artisan make:model Habito
php artisan make:model RegistroDiario
php artisan make:model Recordatorio
php artisan make:model Logro
```

### 2. Definir relaciones en los modelos

Ejemplo para `User.php`:
```php
class User extends Model {
    public function habitos() {
        return $this->hasMany(Habito::class);
    }
    
    public function logros() {
        return $this->belongsToMany(Logro::class, 'logro_usuario')
                    ->withPivot('fecha_obtenido', 'habito_id')
                    ->withTimestamps();
    }
}
```

### 3. Crear Controllers

```bash
php artisan make:controller HabitoController --resource
php artisan make:controller CategoriaController --resource
```

## 📖 Recursos Útiles

- [Documentación de Migraciones](https://laravel.com/docs/migrations)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [Database Seeding](https://laravel.com/docs/seeding)

## 💡 Tips para desarrolladores Spring Boot

1. **@Repository** → En Laravel se llaman **Repositories** (patrón opcional)
2. **@Service** → En Laravel puedes usar **Services** en `app/Services/`
3. **@RestController** → En Laravel usa **Controllers** con `return response()->json()`
4. **application.properties** → En Laravel es `.env`
5. **@Autowired** → En Laravel usa **Dependency Injection** en constructores

¡Espero que esta guía te ayude a entender mejor Laravel viniendo de Spring Boot! 🚀
