# ✅ Checklist de Instalación - KudosApp

Usa este checklist para asegurarte de completar todos los pasos necesarios.

## 📋 Pre-instalación

- [ ] PHP 8.2 o superior instalado
- [ ] Composer instalado
- [ ] Node.js 18.x o superior instalado
- [ ] PostgreSQL 12+ instalado y corriendo
- [ ] Git instalado

### Verificar versiones:
```bash
php --version
composer --version
node --version
npm --version
psql --version
```

---

## 🚀 Instalación

### 1. Clonar y Preparar

- [ ] Repositorio clonado: `git clone https://github.com/JesusChimbaco/kudosApp.git`
- [ ] Navegado a la carpeta: `cd kudosApp`

### 2. Dependencias

- [ ] Dependencias PHP instaladas: `composer install`
- [ ] Dependencias Node.js instaladas: `npm install`

### 3. Configuración

- [ ] Archivo `.env` creado desde `.env.example`
- [ ] Clave de aplicación generada: `php artisan key:generate`

### 4. Base de Datos

- [ ] Base de datos creada en PostgreSQL (nombre: `kudosBD`)
- [ ] Credenciales configuradas en `.env`:
  ```env
  DB_CONNECTION=pgsql
  DB_HOST=127.0.0.1
  DB_PORT=5432
  DB_DATABASE=kudosBD
  DB_USERNAME=postgres
  DB_PASSWORD=tu_contraseña_real
  ```

### 5. Verificación de Conexión

- [ ] Cachés limpiados: `php artisan config:clear`
- [ ] Conexión verificada: `php artisan db:show`
  - Debe mostrar: `Database: kudosBD`, `Tables: 0`

### 6. Migraciones

- [ ] Migraciones ejecutadas: `php artisan migrate`
  - Debe crear 15 tablas sin errores

### 7. Datos Iniciales

- [ ] Categorías insertadas: `php artisan db:seed --class=CategoriaSeeder`
- [ ] Logros insertados: `php artisan db:seed --class=LogroSeeder`

### 8. Verificación de Datos

- [ ] Datos verificados con Tinker:
  ```bash
  php artisan tinker --execute="echo 'Categorías: ' . App\Models\Categoria::count() . PHP_EOL; echo 'Logros: ' . App\Models\Logro::count() . PHP_EOL;"
  ```
  - Debe mostrar: `Categorías: 6`, `Logros: 10`

### 9. Servidor y Assets

- [ ] Servidor iniciado: `php artisan serve`
- [ ] Assets compilados (en otra terminal): `npm run dev`

### 10. Prueba Final

- [ ] Aplicación accesible en: http://localhost:8000
- [ ] Página de login/register visible
- [ ] Registro de nuevo usuario funcional
- [ ] Login funcional

---

## 🎯 Comandos Completos (Copiar y Pegar)

### Windows PowerShell:

```powershell
# 1. Clonar
git clone https://github.com/JesusChimbaco/kudosApp.git
cd kudosApp

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar
Copy-Item .env.example .env
php artisan key:generate

# 4. Ahora crea la BD en pgAdmin (kudosBD)
# 5. Edita .env con tus credenciales de PostgreSQL

# 6. Limpiar y verificar
php artisan config:clear
php artisan db:show

# 7. Migrar
php artisan migrate

# 8. Poblar datos
php artisan db:seed --class=CategoriaSeeder
php artisan db:seed --class=LogroSeeder

# 9. Verificar
php artisan tinker --execute="echo 'Categorías: ' . App\Models\Categoria::count() . PHP_EOL; echo 'Logros: ' . App\Models\Logro::count() . PHP_EOL;"

# 10. Iniciar (en terminales separadas)
php artisan serve
npm run dev
```

---

## ❌ Si Algo Sale Mal

### Error: "could not find driver"
```bash
# Habilita las extensiones de PostgreSQL en php.ini:
# extension=pdo_pgsql
# extension=pgsql
# Luego reinicia
```

### Error: "Connection refused"
```bash
# 1. Verifica que PostgreSQL esté corriendo
# 2. Verifica credenciales en .env
# 3. Ejecuta: php artisan config:clear
```

### Error: "Class not found"
```bash
composer dump-autoload
```

### Tabla ya existe
```bash
# Crea una nueva BD vacía y actualiza .env
```

---

## 📞 Ayuda

Si tienes problemas:
1. Consulta **INSTALACION.md** para más detalles
2. Revisa la sección de "Solución de Problemas"
3. Contacta al equipo

---

## ✅ Instalación Completada

Una vez completado todo el checklist:
- ✅ La aplicación está corriendo en http://localhost:8000
- ✅ Puedes registrar usuarios
- ✅ Puedes hacer login
- ✅ La base de datos tiene 6 categorías y 10 logros

¡Listo para desarrollar! 🚀
