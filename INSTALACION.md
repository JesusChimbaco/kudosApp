# 🚀 Guía de Instalación - KudosApp

Esta guía te ayudará a configurar el proyecto Laravel en tu máquina local.

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

- ✅ **PHP** >= 8.2
- ✅ **Composer** (gestor de dependencias de PHP)
- ✅ **Node.js** >= 18.x y **npm**
- ✅ **PostgreSQL** >= 12
- ✅ **pgAdmin** (opcional, para administrar la base de datos)
- ✅ **Git**

---

## 🔧 Pasos de Instalación

### 1️⃣ Clonar el Repositorio

```bash
git clone https://github.com/JesusChimbaco/kudosApp.git
cd kudosApp
```

### 2️⃣ Instalar Dependencias de PHP

```bash
composer install
```

Este comando instalará todas las dependencias de Laravel y paquetes PHP necesarios.

### 3️⃣ Instalar Dependencias de Node.js

```bash
npm install
```

Esto instalará las dependencias de frontend (Vue 3, Inertia.js, TypeScript, etc.).

### 4️⃣ Crear el Archivo de Configuración `.env`

```bash
# En Windows PowerShell:
Copy-Item .env.example .env

# En Linux/Mac:
cp .env.example .env
```

### 5️⃣ Generar la Clave de Aplicación

```bash
php artisan key:generate
```

Este comando generará una clave única para tu aplicación (campo `APP_KEY` en `.env`).

### 6️⃣ Crear la Base de Datos en PostgreSQL

**Opción A: Usando pgAdmin**
1. Abre pgAdmin
2. Click derecho en **Databases → Create → Database**
3. Configuración:
   - **Database name**: `kudosBD` (o el nombre que prefieras)
   - **Owner**: `postgres`
   - **Encoding**: `UTF8`
4. Click en **Save**

**Opción B: Usando SQL**
```sql
CREATE DATABASE kudosBD
    WITH 
    OWNER = postgres
    ENCODING = 'UTF8'
    CONNECTION LIMIT = -1;
```

### 7️⃣ Configurar la Conexión a la Base de Datos

Abre el archivo `.env` y actualiza estos valores:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=kudosBD          # Nombre de tu base de datos
DB_USERNAME=postgres          # Tu usuario de PostgreSQL
DB_PASSWORD=tu_contraseña     # Tu contraseña de PostgreSQL
```

⚠️ **Importante**: Cambia `tu_contraseña` por tu contraseña real de PostgreSQL.

### 8️⃣ Limpiar Cachés

```bash
php artisan config:clear
php artisan cache:clear
```

### 9️⃣ Verificar Conexión a la Base de Datos

```bash
php artisan db:show
```

Deberías ver algo como:
```
Database ........................... kudosBD
Host ............................... 127.0.0.1
Tables ............................. 0
```

Si ves un error, verifica:
- ✅ Que PostgreSQL esté corriendo
- ✅ Que las credenciales en `.env` sean correctas
- ✅ Que la base de datos exista

### 🔟 Ejecutar las Migraciones

```bash
php artisan migrate
```

Este comando creará todas las tablas en la base de datos:
- ✅ `users` (usuarios)
- ✅ `categorias` (categorías de hábitos)
- ✅ `habitos` (hábitos)
- ✅ `registro_diarios` (registros diarios)
- ✅ `recordatorios` (recordatorios)
- ✅ `logros` (logros/achievements)
- ✅ `logro_usuario` (relación usuarios-logros)
- ✅ Tablas del sistema (cache, jobs, sessions, etc.)

### 1️⃣1️⃣ Poblar Datos Iniciales (Seeders)

```bash
php artisan db:seed --class=CategoriaSeeder
php artisan db:seed --class=LogroSeeder
```

Esto insertará:
- ✅ 6 categorías predefinidas (Salud, Productividad, Ejercicio, etc.)
- ✅ 10 logros iniciales

### 1️⃣2️⃣ Compilar Assets de Frontend

**Para desarrollo:**
```bash
npm run dev
```

**Para producción:**
```bash
npm run build
```

### 1️⃣3️⃣ Iniciar el Servidor de Desarrollo

En una **nueva terminal**, ejecuta:

```bash
php artisan serve
```

La aplicación estará disponible en: **http://localhost:8000**

---

## ✅ Verificación Final

Ejecuta este comando para verificar que todo esté correcto:

```bash
php artisan tinker --execute="echo 'Categorías: ' . App\Models\Categoria::count() . PHP_EOL; echo 'Logros: ' . App\Models\Logro::count() . PHP_EOL; echo 'Usuarios: ' . App\Models\User::count() . PHP_EOL;"
```

Deberías ver:
```
Categorías: 6
Logros: 10
Usuarios: 0
```

---

## 🎯 Comandos Útiles

### Durante el Desarrollo

```bash
# Compilar assets en modo desarrollo (con hot reload)
npm run dev

# Servidor Laravel
php artisan serve

# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver rutas disponibles
php artisan route:list

# Acceder a la consola interactiva
php artisan tinker
```

### Migraciones

```bash
# Ejecutar migraciones
php artisan migrate

# Revertir última migración
php artisan migrate:rollback

# Revertir todas las migraciones y volver a ejecutar
php artisan migrate:fresh

# Revertir, migrar y poblar datos
php artisan migrate:fresh --seed
```

### Tests

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests con cobertura
php artisan test --coverage
```

---

## 🐛 Solución de Problemas Comunes

### Error: "could not find driver"
**Problema**: Falta la extensión de PostgreSQL para PHP

**Solución**:
1. Abre `php.ini`
2. Busca `;extension=pdo_pgsql` y `;extension=pgsql`
3. Quita el `;` al inicio de ambas líneas
4. Reinicia el servidor

### Error: "SQLSTATE[08006] Connection refused"
**Problema**: PostgreSQL no está corriendo o las credenciales son incorrectas

**Solución**:
1. Verifica que PostgreSQL esté corriendo
2. Revisa las credenciales en `.env`
3. Ejecuta `php artisan config:clear`

### Error: "No application encryption key has been specified"
**Problema**: Falta la clave de aplicación

**Solución**:
```bash
php artisan key:generate
```

### Error: "Class 'Categoria' not found"
**Problema**: El autoloader no está actualizado

**Solución**:
```bash
composer dump-autoload
```

### Tabla "migrations" ya existe
**Problema**: La base de datos ya tiene tablas

**Solución**:
1. Elimina la base de datos antigua en pgAdmin
2. Crea una nueva base de datos vacía
3. Actualiza `.env` con el nuevo nombre
4. Ejecuta `php artisan migrate`

---

## 📚 Recursos Adicionales

- [Documentación de Laravel](https://laravel.com/docs)
- [Documentación de Inertia.js](https://inertiajs.com/)
- [Documentación de Vue 3](https://vuejs.org/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)

---

## 🤝 Contribuir

Si encuentras algún problema o tienes sugerencias:

1. Crea un **Issue** en GitHub
2. Envía un **Pull Request** con mejoras
3. Contacta al equipo de desarrollo

---

## 📝 Notas Importantes

### Modelos Eloquent Disponibles

El proyecto incluye los siguientes modelos con relaciones:

- `User` - Usuarios
- `Categoria` - Categorías de hábitos
- `Habito` - Hábitos de usuarios
- `RegistroDiario` - Registros diarios de hábitos
- `Recordatorio` - Recordatorios de hábitos
- `Logro` - Logros/achievements

### Estructura de Carpetas Clave

```
kudosApp/
├── app/
│   ├── Http/Controllers/    # Controladores
│   ├── Models/              # Modelos Eloquent
│   └── Providers/           # Service Providers
├── database/
│   ├── migrations/          # Migraciones de BD
│   ├── seeders/            # Seeders de datos
│   └── factories/          # Factories para tests
├── resources/
│   ├── js/                 # Vue 3 + TypeScript
│   └── views/              # Vistas Blade
├── routes/
│   ├── web.php            # Rutas web
│   └── auth.php           # Rutas de autenticación
└── tests/                 # Tests PHPUnit
```

### Variables de Entorno Importantes

Además de la configuración de la base de datos, estas son otras variables importantes en `.env`:

```env
APP_NAME=KudosApp
APP_ENV=local              # Cambia a 'production' en producción
APP_DEBUG=true             # Cambia a 'false' en producción
APP_URL=http://localhost:8000

SESSION_DRIVER=database    # Sesiones guardadas en BD
```

---

## 🎉 ¡Listo para Desarrollar!

Una vez completados todos los pasos:

1. Visita **http://localhost:8000**
2. Regístrate como nuevo usuario
3. Comienza a explorar la aplicación

¡Feliz coding! 🚀
