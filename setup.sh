#!/bin/bash
# Script de configuración rápida para KudosApp
# Para Windows PowerShell, usa setup.ps1 en su lugar

echo "🚀 Configurando KudosApp..."
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: Este script debe ejecutarse desde la raíz del proyecto"
    exit 1
fi

# Instalar dependencias de Composer
echo "📦 Instalando dependencias de PHP..."
composer install --no-interaction

# Instalar dependencias de Node
echo "📦 Instalando dependencias de Node.js..."
npm install

# Crear archivo .env si no existe
if [ ! -f ".env" ]; then
    echo "📝 Creando archivo .env..."
    cp .env.example .env
else
    echo "ℹ️  El archivo .env ya existe, omitiendo..."
fi

# Generar clave de aplicación
echo "🔑 Generando clave de aplicación..."
php artisan key:generate

# Limpiar cachés
echo "🧹 Limpiando cachés..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

echo ""
echo "✅ Configuración básica completada!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Crea la base de datos en PostgreSQL (nombre sugerido: kudosBD)"
echo "2. Configura las credenciales de BD en el archivo .env:"
echo "   - DB_DATABASE=kudosBD"
echo "   - DB_USERNAME=postgres"
echo "   - DB_PASSWORD=tu_contraseña"
echo "3. Ejecuta las migraciones: php artisan migrate"
echo "4. Ejecuta los seeders:"
echo "   - php artisan db:seed --class=CategoriaSeeder"
echo "   - php artisan db:seed --class=LogroSeeder"
echo "5. Inicia el servidor: php artisan serve"
echo "6. En otra terminal, compila assets: npm run dev"
echo ""
echo "📚 Para más información, consulta INSTALACION.md"
