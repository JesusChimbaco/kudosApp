# Script de configuración rápida para KudosApp (Windows PowerShell)
# Ejecutar como: .\setup.ps1

Write-Host "🚀 Configurando KudosApp..." -ForegroundColor Cyan
Write-Host ""

# Verificar que estamos en el directorio correcto
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Error: Este script debe ejecutarse desde la raíz del proyecto" -ForegroundColor Red
    exit 1
}

# Instalar dependencias de Composer
Write-Host "📦 Instalando dependencias de PHP..." -ForegroundColor Yellow
composer install --no-interaction

# Instalar dependencias de Node
Write-Host "📦 Instalando dependencias de Node.js..." -ForegroundColor Yellow
npm install

# Crear archivo .env si no existe
if (-not (Test-Path ".env")) {
    Write-Host "📝 Creando archivo .env..." -ForegroundColor Yellow
    Copy-Item .env.example .env
} else {
    Write-Host "ℹ️  El archivo .env ya existe, omitiendo..." -ForegroundColor Gray
}

# Generar clave de aplicación
Write-Host "🔑 Generando clave de aplicación..." -ForegroundColor Yellow
php artisan key:generate

# Limpiar cachés
Write-Host "🧹 Limpiando cachés..." -ForegroundColor Yellow
try { php artisan config:clear 2>$null } catch {}
try { php artisan cache:clear 2>$null } catch {}

Write-Host ""
Write-Host "✅ Configuración básica completada!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Próximos pasos:" -ForegroundColor Cyan
Write-Host "1. Crea la base de datos en PostgreSQL (nombre sugerido: kudosBD)" -ForegroundColor White
Write-Host "2. Configura las credenciales de BD en el archivo .env:" -ForegroundColor White
Write-Host "   - DB_DATABASE=kudosBD" -ForegroundColor Gray
Write-Host "   - DB_USERNAME=postgres" -ForegroundColor Gray
Write-Host "   - DB_PASSWORD=tu_contraseña" -ForegroundColor Gray
Write-Host "3. Ejecuta las migraciones: php artisan migrate" -ForegroundColor White
Write-Host "4. Ejecuta los seeders:" -ForegroundColor White
Write-Host "   - php artisan db:seed --class=CategoriaSeeder" -ForegroundColor Gray
Write-Host "   - php artisan db:seed --class=LogroSeeder" -ForegroundColor Gray
Write-Host "5. Inicia el servidor: php artisan serve" -ForegroundColor White
Write-Host "6. En otra terminal, compila assets: npm run dev" -ForegroundColor White
Write-Host ""
Write-Host "📚 Para más información, consulta INSTALACION.md" -ForegroundColor Cyan
