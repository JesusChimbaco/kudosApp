# 🚀 Guía Rápida - 5 Minutos

## TL;DR - Instalación Express

### Opción 1: Script Automático (Recomendado) ⚡

**Windows:**
```powershell
git clone https://github.com/JesusChimbaco/kudosApp.git
cd kudosApp
.\setup.ps1
```

**Linux/Mac:**
```bash
git clone https://github.com/JesusChimbaco/kudosApp.git
cd kudosApp
chmod +x setup.sh
./setup.sh
```

Luego:
1. Crea BD en pgAdmin (nombre: `kudosBD`)
2. Edita `.env` con tus credenciales de PostgreSQL
3. Ejecuta:
   ```bash
   php artisan migrate
   php artisan db:seed --class=CategoriaSeeder
   php artisan db:seed --class=LogroSeeder
   ```
4. Inicia: `php artisan serve` + `npm run dev`

---

### Opción 2: Manual Completo 📝

```bash
# 1. Clonar
git clone https://github.com/JesusChimbaco/kudosApp.git
cd kudosApp

# 2. Dependencias
composer install
npm install

# 3. Configurar
cp .env.example .env          # Linux/Mac
Copy-Item .env.example .env   # Windows
php artisan key:generate

# 4. Base de Datos
# - Crea BD en pgAdmin: kudosBD
# - Edita .env:
#   DB_DATABASE=kudosBD
#   DB_USERNAME=postgres
#   DB_PASSWORD=tu_contraseña

# 5. Migrar
php artisan config:clear
php artisan migrate
php artisan db:seed --class=CategoriaSeeder
php artisan db:seed --class=LogroSeeder

# 6. Iniciar (2 terminales)
php artisan serve    # Terminal 1
npm run dev          # Terminal 2
```

**Visita:** http://localhost:8000

---

## 🔍 Verificación Rápida

```bash
php artisan db:show
# Debe mostrar: Database: kudosBD, Tables: 15

php artisan tinker --execute="echo App\Models\Categoria::count() . ' categorías';"
# Debe mostrar: 6 categorías
```

---

## 📚 Documentación Completa

- **[INSTALACION.md](INSTALACION.md)** - Guía detallada paso a paso
- **[CHECKLIST_INSTALACION.md](CHECKLIST_INSTALACION.md)** - Checklist interactivo
- **[README.md](README.md)** - Información general del proyecto

---

## ❓ Problemas Comunes

| Error | Solución |
|-------|----------|
| `could not find driver` | Habilita `pdo_pgsql` en `php.ini` |
| `Connection refused` | Verifica PostgreSQL corriendo + credenciales en `.env` |
| `Class not found` | `composer dump-autoload` |
| `Tabla ya existe` | Usa una BD nueva y vacía |

---

## 🆘 Ayuda

¿Problemas? Consulta:
1. [INSTALACION.md](INSTALACION.md) sección "Solución de Problemas"
2. Contacta al equipo
3. Abre un Issue en GitHub

---

**¡Instalación en 5 minutos!** ⏱️
