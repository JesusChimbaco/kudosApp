# 📧 API de Recordatorios - KudosApp

## Descripción
Esta API permite gestionar recordatorios (notificaciones por email o push) para hábitos. Los usuarios pueden configurar a qué hora y en qué días de la semana desean recibir recordatorios.

---

## 🔐 Autenticación
Todas las rutas requieren autenticación mediante Bearer Token (Sanctum):
```
Authorization: Bearer {token}
```

---

## 📋 Endpoints Disponibles

### 1. Listar Recordatorios de un Hábito
Obtiene todos los recordatorios configurados para un hábito específico.

**Endpoint:** `GET /api/habitos/{habito_id}/recordatorios`

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "habito_id": 5,
      "activo": true,
      "hora": "08:00:00",
      "dias_semana": "L,M,X,J,V",
      "tipo": "email",
      "mensaje_personalizado": "¡Es hora de hacer ejercicio!",
      "created_at": "2025-01-15T10:00:00.000000Z",
      "updated_at": "2025-01-15T10:00:00.000000Z"
    }
  ]
}
```

---

### 2. Crear Recordatorio
Crea un nuevo recordatorio para un hábito.

**Endpoint:** `POST /api/habitos/{habito_id}/recordatorios`

**Body (JSON):**
```json
{
  "hora": "08:00",
  "dias_semana": "L,M,X,J,V",
  "tipo": "email",
  "mensaje_personalizado": "¡Es hora de hacer ejercicio!",
  "activo": true
}
```

**Parámetros:**
- `hora` (requerido): Hora del recordatorio en formato HH:mm (ej: "08:00", "14:30")
- `dias_semana` (opcional): Días de la semana separados por comas
  - Valores válidos: `L` (Lunes), `M` (Martes), `X` (Miércoles), `J` (Jueves), `V` (Viernes), `S` (Sábado), `D` (Domingo)
  - Ejemplos: 
    - "L,M,X,J,V" (Lunes a Viernes)
    - "S,D" (Fines de semana)
    - "L,X,V" (Días específicos)
  - Si se omite o está vacío, se enviará todos los días
- `tipo` (requerido): Tipo de recordatorio
  - Valores: `email` o `push`
- `mensaje_personalizado` (opcional): Mensaje personalizado (máximo 500 caracteres)
- `activo` (opcional): Estado del recordatorio (default: true)

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Recordatorio creado exitosamente",
  "data": {
    "id": 1,
    "habito_id": 5,
    "activo": true,
    "hora": "08:00:00",
    "dias_semana": "L,M,X,J,V",
    "tipo": "email",
    "mensaje_personalizado": "¡Es hora de hacer ejercicio!",
    "created_at": "2025-01-15T10:00:00.000000Z",
    "updated_at": "2025-01-15T10:00:00.000000Z"
  }
}
```

**Errores comunes:**
```json
{
  "success": false,
  "message": "Formato de días inválido. Use: L,M,X,J,V,S,D"
}
```

---

### 3. Ver Recordatorio Específico
Obtiene los detalles de un recordatorio.

**Endpoint:** `GET /api/habitos/{habito_id}/recordatorios/{recordatorio_id}`

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "habito_id": 5,
    "activo": true,
    "hora": "08:00:00",
    "dias_semana": "L,M,X,J,V",
    "tipo": "email",
    "mensaje_personalizado": "¡Es hora de hacer ejercicio!",
    "created_at": "2025-01-15T10:00:00.000000Z",
    "updated_at": "2025-01-15T10:00:00.000000Z"
  }
}
```

---

### 4. Actualizar Recordatorio
Actualiza un recordatorio existente.

**Endpoint:** `PUT/PATCH /api/habitos/{habito_id}/recordatorios/{recordatorio_id}`

**Body (JSON):**
```json
{
  "hora": "09:00",
  "activo": true,
  "mensaje_personalizado": "¡Recuerda hacer ejercicio ahora!"
}
```

**Nota:** Todos los campos son opcionales. Solo se actualizarán los campos enviados.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Recordatorio actualizado exitosamente",
  "data": {
    "id": 1,
    "habito_id": 5,
    "activo": true,
    "hora": "09:00:00",
    "dias_semana": "L,M,X,J,V",
    "tipo": "email",
    "mensaje_personalizado": "¡Recuerda hacer ejercicio ahora!",
    "created_at": "2025-01-15T10:00:00.000000Z",
    "updated_at": "2025-01-15T11:30:00.000000Z"
  }
}
```

---

### 5. Eliminar Recordatorio
Elimina un recordatorio.

**Endpoint:** `DELETE /api/habitos/{habito_id}/recordatorios/{recordatorio_id}`

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Recordatorio eliminado exitosamente"
}
```

---

### 6. Activar/Desactivar Recordatorio
Alterna el estado activo/inactivo de un recordatorio sin eliminarlo.

**Endpoint:** `POST /api/habitos/{habito_id}/recordatorios/{recordatorio_id}/toggle`

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Recordatorio activado",
  "data": {
    "id": 1,
    "habito_id": 5,
    "activo": true,
    "hora": "08:00:00",
    "dias_semana": "L,M,X,J,V",
    "tipo": "email",
    "mensaje_personalizado": "¡Es hora de hacer ejercicio!",
    "created_at": "2025-01-15T10:00:00.000000Z",
    "updated_at": "2025-01-15T12:00:00.000000Z"
  }
}
```

---

## 📧 Sistema de Envío de Recordatorios

### ¿Cómo funciona?

1. **Comando Programado:** Laravel ejecuta automáticamente el comando `recordatorios:enviar` cada minuto
2. **Verificación:** El comando busca recordatorios activos cuya hora coincida con la hora actual
3. **Validación de Días:** Verifica que el día actual esté en `dias_semana`
4. **Cola de Jobs:** Despacha un Job a la cola para cada recordatorio que coincida
5. **Envío de Email:** El Job envía el email personalizado al usuario

### Activar el Scheduler en Desarrollo

En **Windows**, ejecuta este comando en una terminal aparte:
```powershell
php artisan schedule:work
```

En **Producción** (Linux/Servidor), agrega a crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Procesar la Cola de Jobs

Para que los emails se envíen, necesitas ejecutar el worker de colas:

```bash
php artisan queue:work
```

En producción, usa un supervisor como Supervisor o pm2 para mantener el worker corriendo.

---

## 🧪 Ejemplos de Uso

### Ejemplo 1: Crear recordatorio de lunes a viernes a las 8:00 AM

```javascript
fetch('http://localhost:8000/api/habitos/5/recordatorios', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer tu-token-aqui'
  },
  body: JSON.stringify({
    hora: '08:00',
    dias_semana: 'L,M,X,J,V',
    tipo: 'email',
    mensaje_personalizado: '¡Buenos días! Es hora de hacer ejercicio 💪',
    activo: true
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

### Ejemplo 2: Crear recordatorio solo sábados y domingos

```javascript
fetch('http://localhost:8000/api/habitos/3/recordatorios', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer tu-token-aqui'
  },
  body: JSON.stringify({
    hora: '10:00',
    dias_semana: 'S,D',
    tipo: 'email',
    mensaje_personalizado: '¡Feliz fin de semana! Hora de leer 📚'
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

### Ejemplo 3: Crear recordatorio diario (todos los días)

```javascript
fetch('http://localhost:8000/api/habitos/7/recordatorios', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer tu-token-aqui'
  },
  body: JSON.stringify({
    hora: '22:00',
    tipo: 'email',
    mensaje_personalizado: '¡Hora de dormir! Recuerda tu hábito de meditación 🧘'
    // dias_semana se omite para que sea todos los días
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

### Ejemplo 4: Desactivar temporalmente un recordatorio

```javascript
fetch('http://localhost:8000/api/habitos/5/recordatorios/1/toggle', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer tu-token-aqui'
  }
})
.then(res => res.json())
.then(data => console.log(data));
```

### Ejemplo 5: Actualizar la hora de un recordatorio

```javascript
fetch('http://localhost:8000/api/habitos/5/recordatorios/1', {
  method: 'PATCH',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer tu-token-aqui'
  },
  body: JSON.stringify({
    hora: '07:30'
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

---

## 🐛 Testing Manual

### Probar el Comando Manualmente

Puedes ejecutar el comando manualmente para ver qué recordatorios se enviarían:

```bash
php artisan recordatorios:enviar
```

### Ver Logs

Los logs del sistema están en `storage/logs/laravel.log`. Puedes verificar:
- Recordatorios despachados
- Emails enviados
- Errores en el envío

### Revisar Jobs en la Cola

```bash
php artisan queue:failed  # Ver jobs fallidos
php artisan queue:retry {id}  # Reintentar un job específico
php artisan queue:retry all  # Reintentar todos los jobs fallidos
```

---

## ⚙️ Configuración

### Variables de Entorno (.env)

Asegúrate de tener configurado el email:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=database
```

### Crear las Tablas de Colas

Si aún no lo has hecho:

```bash
php artisan queue:table
php artisan migrate
```

---

## 🎯 Casos de Uso Comunes

### 1. Recordatorio Matutino (Todos los días)
```json
{
  "hora": "07:00",
  "tipo": "email",
  "mensaje_personalizado": "¡Buenos días! Hora de comenzar el día con energía ☀️"
}
```

### 2. Recordatorio Laboral (Lunes a Viernes)
```json
{
  "hora": "09:00",
  "dias_semana": "L,M,X,J,V",
  "tipo": "email",
  "mensaje_personalizado": "Recuerda revisar tus objetivos del día 📋"
}
```

### 3. Recordatorio de Fin de Semana
```json
{
  "hora": "10:00",
  "dias_semana": "S,D",
  "tipo": "email",
  "mensaje_personalizado": "¡Tiempo para ti! Disfruta tu hábito 🎉"
}
```

### 4. Múltiples Recordatorios para el Mismo Hábito
Puedes crear varios recordatorios para un mismo hábito:
- Uno por la mañana (07:00)
- Uno al mediodía (12:00)
- Uno por la noche (20:00)

---

## 📊 Modelo de Datos

### Estructura de la tabla `recordatorios`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único del recordatorio |
| habito_id | bigint | ID del hábito asociado |
| activo | boolean | Si el recordatorio está activo |
| hora | time | Hora del recordatorio (HH:mm) |
| dias_semana | string | Días separados por comas (L,M,X,J,V,S,D) |
| tipo | enum | Tipo: 'email' o 'push' |
| mensaje_personalizado | string | Mensaje opcional (max 500 chars) |
| created_at | timestamp | Fecha de creación |
| updated_at | timestamp | Última actualización |

---

## 🔒 Seguridad y Permisos

- Solo puedes gestionar recordatorios de tus propios hábitos
- La autorización se valida automáticamente usando Laravel Policies
- Los tokens se validan con Laravel Sanctum

---

## ✅ Checklist de Implementación

Para usar este sistema completo:

- [x] Migración de `recordatorios` ejecutada
- [x] Modelo `Recordatorio` creado
- [x] Controlador `RecordatorioController` implementado
- [x] Rutas API registradas
- [x] Mailable `RecordatorioHabito` configurado
- [x] Job `EnviarRecordatorioHabito` creado
- [x] Comando `EnviarRecordatoriosHabitos` programado
- [ ] Configurar variables de entorno de email
- [ ] Ejecutar `php artisan schedule:work` en desarrollo
- [ ] Ejecutar `php artisan queue:work` para procesar emails
- [ ] Configurar cron en producción

---

## 📞 Soporte

Si tienes problemas:
1. Verifica los logs en `storage/logs/laravel.log`
2. Revisa la configuración de email en `.env`
3. Asegúrate de que el scheduler y queue worker estén corriendo
4. Verifica que los recordatorios tengan `activo = true`
