#!/usr/bin/env php
<?php

/**
 * Script de verificación pre-deploy para Railway
 * Ejecutar: php railway-check.php
 */

echo "🔍 Verificando configuración para Railway...\n\n";

$errors = [];
$warnings = [];

// 1. Verificar archivos requeridos
echo "📋 Verificando archivos necesarios...\n";
$requiredFiles = [
    'Procfile' => '✅ Procfile encontrado',
    'railway.json' => '✅ railway.json encontrado',
    'nixpacks.toml' => '✅ nixpacks.toml encontrado',
    'composer.json' => '✅ composer.json encontrado',
    'package.json' => '✅ package.json encontrado',
];

foreach ($requiredFiles as $file => $message) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "  $message\n";
    } else {
        $errors[] = "❌ Falta archivo: $file";
    }
}

// 2. Verificar composer.json
echo "\n📦 Verificando composer.json...\n";
$composer = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);
if (isset($composer['require']['php'])) {
    echo "  ✅ Versión de PHP especificada: {$composer['require']['php']}\n";
} else {
    $warnings[] = "⚠️  No se especifica versión de PHP en composer.json";
}

// 3. Verificar .env.example
echo "\n🔧 Verificando configuración de entorno...\n";
if (file_exists(__DIR__ . '/.env.example')) {
    echo "  ✅ .env.example encontrado\n";
    
    $envExample = file_get_contents(__DIR__ . '/.env.example');
    $requiredEnvVars = [
        'APP_NAME',
        'APP_ENV',
        'APP_KEY',
        'APP_URL',
        'DB_CONNECTION',
        'MAIL_MAILER',
        'QUEUE_CONNECTION',
    ];
    
    foreach ($requiredEnvVars as $var) {
        if (strpos($envExample, $var) !== false) {
            echo "  ✅ Variable $var presente\n";
        } else {
            $warnings[] = "⚠️  Variable $var no encontrada en .env.example";
        }
    }
} else {
    $warnings[] = "⚠️  .env.example no encontrado";
}

// 4. Verificar migraciones
echo "\n🗄️  Verificando migraciones...\n";
$migrations = glob(__DIR__ . '/database/migrations/*.php');
echo "  ✅ " . count($migrations) . " migraciones encontradas\n";

// 5. Verificar comandos de schedule
echo "\n⏰ Verificando comandos programados...\n";
if (file_exists(__DIR__ . '/routes/console.php')) {
    $consoleContent = file_get_contents(__DIR__ . '/routes/console.php');
    if (strpos($consoleContent, 'Schedule::') !== false) {
        echo "  ✅ Comandos programados encontrados en routes/console.php\n";
    } else {
        $warnings[] = "⚠️  No se encontraron comandos programados";
    }
}

// 6. Verificar jobs
echo "\n💼 Verificando Jobs...\n";
$jobs = glob(__DIR__ . '/app/Jobs/*.php');
echo "  ✅ " . count($jobs) . " Jobs encontrados\n";

// 7. Verificar mails
echo "\n📧 Verificando Mailables...\n";
$mails = glob(__DIR__ . '/app/Mail/*.php');
echo "  ✅ " . count($mails) . " Mailables encontrados\n";

// 8. Verificar vistas de email
echo "\n📄 Verificando vistas de email...\n";
$emailViews = glob(__DIR__ . '/resources/views/emails/*.blade.php');
echo "  ✅ " . count($emailViews) . " vistas de email encontradas\n";

// 9. Verificar frontend build
echo "\n🎨 Verificando assets del frontend...\n";
if (file_exists(__DIR__ . '/vite.config.ts') || file_exists(__DIR__ . '/vite.config.js')) {
    echo "  ✅ Vite configurado\n";
} else {
    $warnings[] = "⚠️  No se encontró configuración de Vite";
}

// 10. Verificar .gitignore
echo "\n🚫 Verificando .gitignore...\n";
if (file_exists(__DIR__ . '/.gitignore')) {
    $gitignore = file_get_contents(__DIR__ . '/.gitignore');
    $importantIgnores = ['/vendor', '/node_modules', '.env'];
    foreach ($importantIgnores as $ignore) {
        if (strpos($gitignore, $ignore) !== false) {
            echo "  ✅ $ignore está en .gitignore\n";
        } else {
            $errors[] = "❌ $ignore NO está en .gitignore";
        }
    }
}

// Resumen
echo "\n" . str_repeat('=', 50) . "\n";
echo "📊 RESUMEN DE VERIFICACIÓN\n";
echo str_repeat('=', 50) . "\n\n";

if (empty($errors) && empty($warnings)) {
    echo "✅ TODO LISTO PARA DEPLOY EN RAILWAY!\n\n";
    echo "🚀 Próximos pasos:\n";
    echo "   1. Hacer commit de estos cambios\n";
    echo "   2. Push a GitHub\n";
    echo "   3. Conectar repositorio en Railway\n";
    echo "   4. Agregar PostgreSQL\n";
    echo "   5. Configurar variables de entorno\n";
    echo "   6. Crear servicios para web, worker y scheduler\n\n";
    exit(0);
} else {
    if (!empty($errors)) {
        echo "❌ ERRORES ENCONTRADOS:\n";
        foreach ($errors as $error) {
            echo "   $error\n";
        }
        echo "\n";
    }
    
    if (!empty($warnings)) {
        echo "⚠️  ADVERTENCIAS:\n";
        foreach ($warnings as $warning) {
            echo "   $warning\n";
        }
        echo "\n";
    }
    
    echo "Por favor corrige los errores antes de hacer deploy.\n\n";
    exit(1);
}
