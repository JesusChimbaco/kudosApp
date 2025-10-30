# 🧪 Script de Prueba - Sistema de Recordatorios

Este script te permite probar rápidamente el sistema de recordatorios.

## Pasos para Probar

### 1. Obtener Token de Autenticación

Primero necesitas un token. Usa Postman o cURL:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "tu-email@example.com",
    "password": "tu-password"
  }'
```

Guarda el token que recibes en la respuesta.

### 2. Crear un Recordatorio de Prueba

Crea un recordatorio que se dispare en **1 minuto** desde ahora:

```bash
# Reemplaza {habito_id} con un ID real de tus hábitos
# Reemplaza {token} con tu token de autenticación
# Reemplaza {HORA} con la hora actual + 1 minuto (ej: si son las 14:30, pon "14:31")

curl -X POST http://localhost:8000/api/habitos/{habito_id}/recordatorios \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "hora": "{HORA}",
    "tipo": "email",
    "mensaje_personalizado": "🧪 Este es un recordatorio de prueba",
    "activo": true
  }'
```

**Ejemplo real:**
```bash
curl -X POST http://localhost:8000/api/habitos/1/recordatorios \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abcd1234..." \
  -d '{
    "hora": "14:31",
    "tipo": "email",
    "mensaje_personalizado": "🧪 Este es un recordatorio de prueba",
    "activo": true
  }'
```

### 3. Iniciar el Scheduler (Terminal 1)

Abre una terminal y ejecuta:

```bash
php artisan schedule:work
```

Deberías ver algo como:
```
INFO  Running scheduled tasks every minute.
```

### 4. Iniciar el Queue Worker (Terminal 2)

Abre **otra terminal** y ejecuta:

```bash
php artisan queue:work
```

Deberías ver:
```
INFO  Processing jobs from the [default] queue.
```

### 5. Esperar y Verificar

Cuando llegue la hora configurada:

1. **Terminal 1 (Scheduler)** mostrará:
   ```
   ✓ Recordatorio despachado: [Nombre del hábito] para [Usuario]
   Proceso completado: 1 recordatorios despachados, 0 saltados
   ```

2. **Terminal 2 (Queue Worker)** procesará el job:
   ```
   [timestamp] Processing: App\Jobs\EnviarRecordatorioHabito
   [timestamp] Processed: App\Jobs\EnviarRecordatorioHabito
   ```

3. **Email enviado**: Revisa tu bandeja de entrada (puede tardar 1-2 minutos)

### 6. Verificar Logs

Si algo falla, revisa los logs:

```bash
# Windows PowerShell
Get-Content storage/logs/laravel.log -Tail 50

# O abre el archivo directamente
notepad storage/logs/laravel.log
```

---

## 🐛 Troubleshooting

### No se envía el email

**Problema:** El comando se ejecuta pero no llega el email

**Soluciones:**
1. Verifica configuración de email en `.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=tu-email@gmail.com
   MAIL_PASSWORD=tu-app-password  # NO tu contraseña normal
   MAIL_ENCRYPTION=tls
   ```

2. Si usas Gmail, necesitas crear una **Contraseña de Aplicación**:
   - Ve a https://myaccount.google.com/security
   - Habilita verificación en 2 pasos
   - Busca "Contraseñas de aplicaciones"
   - Genera una nueva para "Mail"
   - Usa esa contraseña en `MAIL_PASSWORD`

3. Reinicia el queue worker después de cambiar `.env`:
   ```bash
   # Presiona Ctrl+C en la terminal del queue worker
   # Luego ejecuta de nuevo:
   php artisan queue:work
   ```

### El scheduler no ejecuta el comando

**Problema:** No ves ningún mensaje en la terminal del scheduler

**Solución:**
1. Asegúrate de que el recordatorio tiene `activo = true`
2. Verifica que la hora coincida exactamente (formato 24 horas)
3. Verifica que hoy sea un día válido según `dias_semana`
4. Ejecuta manualmente el comando para ver qué pasa:
   ```bash
   php artisan recordatorios:enviar
   ```

### El comando se ejecuta pero dice "0 recordatorios despachados"

**Posibles causas:**
1. La hora no coincide exactamente
2. El día actual no está en `dias_semana`
3. El recordatorio está inactivo (`activo = false`)
4. El hábito está inactivo

**Verificar:**
```bash
# Ejecuta manualmente con la hora actual
php artisan recordatorios:enviar
```

---

## ✅ Checklist de Verificación

Antes de probar, asegúrate de:

- [ ] Tienes un usuario creado con email válido
- [ ] Tienes al menos un hábito creado
- [ ] Has configurado las variables de email en `.env`
- [ ] Si usas Gmail, tienes una App Password
- [ ] Has ejecutado `php artisan migrate`
- [ ] El scheduler está corriendo (`php artisan schedule:work`)
- [ ] El queue worker está corriendo (`php artisan queue:work`)
- [ ] Creaste un recordatorio con hora actual + 1 minuto
- [ ] El recordatorio tiene `activo = true`

---

## 📋 Comandos Útiles

### Ver recordatorios creados
```bash
php artisan tinker
>>> App\Models\Recordatorio::with('habito')->get();
```

### Borrar todos los recordatorios de prueba
```bash
php artisan tinker
>>> App\Models\Recordatorio::where('mensaje_personalizado', 'LIKE', '%prueba%')->delete();
```

### Ver jobs fallidos
```bash
php artisan queue:failed
```

### Limpiar jobs fallidos
```bash
php artisan queue:flush
```

### Reintentar jobs fallidos
```bash
php artisan queue:retry all
```

---

## 🎯 Escenarios de Prueba

### Prueba 1: Recordatorio Inmediato
```json
{
  "hora": "14:31",  // Hora actual + 1 minuto
  "tipo": "email",
  "mensaje_personalizado": "Prueba inmediata"
}
```

### Prueba 2: Recordatorio con Días Específicos
```json
{
  "hora": "15:00",
  "dias_semana": "L,M,X",  // Solo lunes, martes, miércoles
  "tipo": "email",
  "mensaje_personalizado": "Solo días laborales específicos"
}
```

### Prueba 3: Recordatorio Diario
```json
{
  "hora": "08:00",
  "tipo": "email",
  "mensaje_personalizado": "Recordatorio diario matutino"
  // Sin dias_semana = todos los días
}
```

---

## 📧 Ejemplo de Email que Recibirás

Cuando funcione correctamente, recibirás un email con:

**Asunto:** ⏰ Recordatorio: [Nombre del Hábito]

**Contenido:**
- Emoji del hábito
- Nombre del hábito
- Descripción
- Mensaje personalizado
- Racha actual 🔥
- Racha máxima 🏆
- Botón para marcar como completado
- Mensaje motivacional

---

## 🔄 Workflow Completo

```
1. Usuario crea recordatorio
   ↓
2. Scheduler se ejecuta cada minuto
   ↓
3. Verifica hora y día actual
   ↓
4. Encuentra recordatorios que coinciden
   ↓
5. Despacha Job a la cola
   ↓
6. Queue Worker procesa el Job
   ↓
7. Job envía email usando Mailable
   ↓
8. Usuario recibe email
```

---

## 💡 Tips

1. **Zona horaria:** Asegúrate de que tu servidor tenga la zona horaria correcta en `config/app.php`:
   ```php
   'timezone' => 'America/Mexico_City',
   ```

2. **Testing en desarrollo:** Usa `log` driver para emails de prueba:
   ```env
   MAIL_MAILER=log
   ```
   Los emails se guardarán en `storage/logs/laravel.log` en lugar de enviarse.

3. **Múltiples recordatorios:** Puedes tener varios recordatorios para el mismo hábito.

4. **Desactivar temporalmente:** Usa el endpoint `toggle` en lugar de eliminar.

---

¡Listo para probar! 🚀
