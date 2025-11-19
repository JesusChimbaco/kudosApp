# 🚂 Guía de Despliegue en Railway - KudosApp

## 📋 Pre-requisitos

1. Cuenta en [Railway.app](https://railway.app)
2. Repositorio conectado a GitHub
3. Credenciales de email SMTP listas

---

## 🚀 Paso 1: Crear Proyecto en Railway

1. Ve a [Railway.app](https://railway.app)
2. Clic en **"New Project"**
3. Selecciona **"Deploy from GitHub repo"**
4. Busca y selecciona: `JesusChimbaco/kudosApp`
5. Railway comenzará a detectar automáticamente Laravel

---

## 🗄️ Paso 2: Agregar PostgreSQL

1. En tu proyecto de Railway, clic en **"New"**
2. Selecciona **"Database"** → **"Add PostgreSQL"**
3. Railway creará automáticamente la base de datos y configurará las variables

Railway inyectará automáticamente:
- `DATABASE_URL`
- `PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD`

---

## ⚙️ Paso 3: Configurar Variables de Entorno

En Railway → Tu Servicio → **Variables**, agregar:

### Variables Básicas:
```env
APP_NAME="Kudos"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-proyecto.up.railway.app
APP_TIMEZONE=America/Bogota

# Railway genera APP_KEY automáticamente, pero puedes establecerla:
APP_KEY=base64:tu-clave-generada-aqui
```

### Base de Datos (Railway las inyecta automáticamente):
```env
DB_CONNECTION=pgsql
# No necesitas configurar PGHOST, PGPORT, etc. Railway lo hace automáticamente
```

### Email (Gmail SMTP):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=kudosregisterconfirmation@gmail.com
MAIL_PASSWORD=tu-password-de-aplicacion-aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=kudosregisterconfirmation@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**⚠️ IMPORTANTE**: Usa una "Contraseña de Aplicación" de Gmail, no tu contraseña normal.
- Ve a: https://myaccount.google.com/apppasswords
- Crea una contraseña específica para Railway

### Queue y Sesión:
```env
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### Cache:
```env
CACHE_DRIVER=database
```

---

## 🔧 Paso 4: Configurar Servicios Múltiples

Tu app necesita 3 servicios corriendo simultáneamente:

### 4.1 Servicio Principal (Web)
- **Nombre**: `kudos-web`
- **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`
- **Procfile**: `web`
- Este es tu servidor web principal

### 4.2 Queue Worker
- En Railway, clic **"New"** → **"Empty Service"**
- **Nombre**: `kudos-worker`
- Conecta el mismo repositorio
- **Start Command**: `php artisan queue:work --tries=3 --timeout=90 --sleep=3`
- **Procfile**: `worker`
- Comparte las mismas variables de entorno

### 4.3 Scheduler
- En Railway, clic **"New"** → **"Empty Service"**
- **Nombre**: `kudos-scheduler`
- Conecta el mismo repositorio
- **Start Command**: `while true; do php artisan schedule:run; sleep 60; done`
- **Procfile**: `scheduler`
- Comparte las mismas variables de entorno

---

## 📦 Paso 5: Ejecutar Migraciones

Una vez desplegado, ejecuta en la terminal de Railway:

```bash
php artisan migrate --force
```

O configura en el **Deploy Command**:
```bash
php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
```

---

## ✅ Paso 6: Verificar el Despliegue

### Verificar que todo funciona:

1. **Web funcionando**:
   - Abre tu URL: `https://tu-proyecto.up.railway.app`

2. **Base de datos conectada**:
   ```bash
   php artisan tinker
   >>> User::count()
   ```

3. **Queue worker activo**:
   - Revisa logs del servicio `kudos-worker`
   - Deberías ver: "Processing jobs..."

4. **Scheduler activo**:
   - Revisa logs del servicio `kudos-scheduler`
   - Deberías ver ejecuciones cada minuto

5. **Emails funcionando**:
   - Crea un hábito con recordatorio
   - Verifica que llegue el email

---

## 🔍 Debugging

### Ver logs en tiempo real:
```bash
# En Railway CLI o en la interfaz web
railway logs
```

### Comandos útiles en Railway:
```bash
# Conectar a la terminal
railway run bash

# Limpiar cachés
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Verificar conexión a DB
php artisan db:show

# Ver trabajos fallidos
php artisan queue:failed

# Reintentar trabajos fallidos
php artisan queue:retry all
```

---

## 📊 Monitoreo

### Variables importantes para revisar:

1. **Logs del Scheduler**: Ver que corra cada minuto
2. **Logs del Worker**: Ver que procese jobs sin errores
3. **Logs del Web**: Ver requests HTTP

### Métricas en Railway:
- CPU usage
- Memory usage
- Network traffic

---

## 🔒 Seguridad

### Configuraciones recomendadas:

1. **Desactiva debug en producción**:
   ```env
   APP_DEBUG=false
   ```

2. **Configura CORS apropiadamente** en `config/cors.php`

3. **SSL/HTTPS**: Railway lo provee automáticamente

4. **Rate Limiting**: Ya configurado en `routes/api.php`

---

## 💰 Costos Estimados

- **Servicio Web**: ~$5 USD/mes
- **Queue Worker**: ~$5 USD/mes  
- **Scheduler**: ~$5 USD/mes
- **PostgreSQL**: ~$5 USD/mes

**Total**: ~$20 USD/mes para los 3 servicios + DB

Railway ofrece $5 USD gratis mensualmente en el plan Hobby.

---

## 🆘 Problemas Comunes

### Error: "No application encryption key has been set"
```bash
php artisan key:generate --show
# Copia el output y agrégalo a APP_KEY en Railway
```

### Error: "SQLSTATE[08006] [7] timeout expired"
- Verifica que las variables `PGHOST`, `PGPORT`, etc. estén configuradas
- Railway las inyecta automáticamente si agregaste PostgreSQL

### Emails no se envían:
1. Verifica que el Queue Worker esté corriendo
2. Revisa los logs: `php artisan queue:monitor`
3. Verifica credenciales SMTP de Gmail
4. Asegúrate de usar "Contraseña de Aplicación"

### Scheduler no ejecuta comandos:
- Verifica logs del servicio `kudos-scheduler`
- Debe mostrar ejecuciones cada 60 segundos

---

## 🎯 Checklist Final

- [ ] Proyecto creado en Railway
- [ ] PostgreSQL agregado
- [ ] Variables de entorno configuradas
- [ ] Servicio Web desplegado
- [ ] Queue Worker corriendo
- [ ] Scheduler corriendo
- [ ] Migraciones ejecutadas
- [ ] Aplicación accesible en URL
- [ ] Emails funcionando
- [ ] Recordatorios enviándose

---

## 🚀 Siguientes Pasos

1. Configurar dominio personalizado (opcional)
2. Configurar backups de base de datos
3. Configurar monitoring (Railway Metrics)
4. Configurar alertas por email

---

¡Listo! Tu aplicación debería estar corriendo en Railway. 🎉

Para soporte: https://railway.app/help
