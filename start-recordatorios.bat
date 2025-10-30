@echo off
echo ========================================
echo Sistema de Recordatorios - KudosApp
echo ========================================
echo.
echo Iniciando servicios...
echo.
echo [1/2] Scheduler (revisa recordatorios cada minuto)
echo [2/2] Queue Worker (procesa envio de emails)
echo.
echo Presiona Ctrl+C para detener todos los servicios
echo ========================================
echo.

start "KudosApp - Scheduler" cmd /k "php artisan schedule:work"
timeout /t 2 /nobreak >nul
start "KudosApp - Queue Worker" cmd /k "php artisan queue:work --tries=3 --timeout=90"

echo.
echo ✓ Servicios iniciados en ventanas separadas
echo.
echo Para detener: Cierra las ventanas o presiona Ctrl+C en cada una
echo.
pause
