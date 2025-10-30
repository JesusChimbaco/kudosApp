<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n📋 Recordatorios en la base de datos:\n";
echo str_repeat("=", 80) . "\n\n";

$recordatorios = App\Models\Recordatorio::with('habito.user')->get();

if ($recordatorios->isEmpty()) {
    echo "❌ No hay recordatorios en la base de datos.\n\n";
    exit;
}

foreach ($recordatorios as $r) {
    echo "🔔 Recordatorio ID: {$r->id}\n";
    echo "   Hábito: {$r->habito->nombre}\n";
    echo "   Usuario: {$r->habito->user->name} ({$r->habito->user->email})\n";
    echo "   Hora: {$r->hora}\n";
    echo "   Días: " . ($r->dias_semana ?: 'Todos los días') . "\n";
    echo "   Tipo: {$r->tipo}\n";
    echo "   Activo: " . ($r->activo ? '✅ Sí' : '❌ No') . "\n";
    echo str_repeat("-", 80) . "\n";
}

echo "\n";
echo "🕐 Hora actual: " . now()->format('H:i') . "\n";
echo "📅 Día actual: " . ['D', 'L', 'M', 'X', 'J', 'V', 'S'][now()->dayOfWeek] . " (" . now()->translatedFormat('l') . ")\n";
echo "\n";
