Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Sistema de Recordatorios - KudosApp" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Iniciando servicios..." -ForegroundColor Yellow
Write-Host ""
Write-Host "[1/2] Scheduler (revisa recordatorios cada minuto)" -ForegroundColor Green
Write-Host "[2/2] Queue Worker (procesa envio de emails)" -ForegroundColor Green
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Iniciar Scheduler en nueva ventana
Start-Process pwsh -ArgumentList "-NoExit", "-Command", "Write-Host 'SCHEDULER - Revisando recordatorios cada minuto...' -ForegroundColor Green; php artisan schedule:work"

Start-Sleep -Seconds 2

# Iniciar Queue Worker en nueva ventana
Start-Process pwsh -ArgumentList "-NoExit", "-Command", "Write-Host 'QUEUE WORKER - Procesando emails...' -ForegroundColor Cyan; php artisan queue:work --tries=3 --timeout=90"

Write-Host ""
Write-Host "✓ Servicios iniciados en ventanas separadas" -ForegroundColor Green
Write-Host ""
Write-Host "Para detener: Cierra las ventanas de PowerShell" -ForegroundColor Yellow
Write-Host ""
