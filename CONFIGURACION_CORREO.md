# 📧 Configuración de Correo Electrónico

## ✅ Sistema de Correo Implementado

Se ha implementado el envío de correo de confirmación al registrarse con las siguientes características:

### 📦 Componentes Creados:

1. **Mailable:** `App\Mail\RegistroConfirmado`
   - Envío asíncrono (usando cola)
   - Diseño profesional y responsivo
   - Información personalizada del usuario

2. **Vista del correo:** `resources/views/emails/registro-confirmado.blade.php`
   - Diseño moderno con degradados
   - Responsive para móviles
   - Muestra características de la app
   - Botón de acceso directo

3. **Integración:** El correo se envía automáticamente en `RegisteredUserController`

---

## 🔧 Configuración del Servidor de Correo

### Opción 1: Mailtrap (Recomendado para Desarrollo) 🧪

Mailtrap es un servicio gratuito para probar correos en desarrollo sin enviar emails reales.

1. **Crear cuenta en Mailtrap:**
   - Ve a [https://mailtrap.io/](https://mailtrap.io/)
   - Regístrate gratis

2. **Obtener credenciales:**
   - En el dashboard, selecciona tu inbox
   - Copia las credenciales SMTP

3. **Configurar `.env`:**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=tu_username_mailtrap
   MAIL_PASSWORD=tu_password_mailtrap
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="noreply@kudosapp.com"
   MAIL_FROM_NAME="KudosApp"
   ```

---

### Opción 2: Gmail (Para Producción) 📮

1. **Habilitar autenticación de 2 pasos en Gmail**
2. **Generar contraseña de aplicación:**
   - Ve a tu cuenta de Google
   - Seguridad → Contraseñas de aplicaciones
   - Genera una nueva para "Correo"

3. **Configurar `.env`:**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=tu_email@gmail.com
   MAIL_PASSWORD=tu_contraseña_de_aplicacion
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="noreply@kudosapp.com"
   MAIL_FROM_NAME="KudosApp"
   ```

⚠️ **Nota:** Usa la contraseña de aplicación generada, NO tu contraseña de Gmail normal.

---

### Opción 3: Log (Solo para Desarrollo - Configuración Actual) 📝

Los correos se guardan en `storage/logs/laravel.log` en lugar de enviarse.

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@kudosapp.com"
MAIL_FROM_NAME="KudosApp"
```

**Ventajas:**
- ✅ No necesitas configurar un servidor SMTP
- ✅ Puedes ver el contenido del correo en los logs
- ✅ Perfecto para desarrollo rápido

**Desventajas:**
- ❌ No pruebas el envío real
- ❌ No ves el diseño HTML renderizado

---

### Opción 4: Otros Servicios

#### SendGrid
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=tu_sendgrid_api_key
MAIL_ENCRYPTION=tls
```

#### Mailgun
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=tu-dominio.mailgun.org
MAILGUN_SECRET=tu_mailgun_secret
MAIL_FROM_ADDRESS="noreply@kudosapp.com"
```

#### AWS SES
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=tu_access_key
AWS_SECRET_ACCESS_KEY=tu_secret_key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS="noreply@kudosapp.com"
```

---

## 🚀 Activar el Sistema de Colas (Opcional pero Recomendado)

El correo se envía de forma asíncrona usando colas. Para que funcione correctamente:

### 1️⃣ Configurar la cola en `.env`:

```env
QUEUE_CONNECTION=database
```

### 2️⃣ Crear la tabla de trabajos (si no existe):

```bash
php artisan queue:table
php artisan migrate
```

### 3️⃣ Ejecutar el worker de colas:

```bash
php artisan queue:work
```

O en desarrollo, usa:
```bash
php artisan queue:listen
```

**Ventajas de usar colas:**
- ✅ El usuario no espera a que se envíe el correo
- ✅ Registro más rápido
- ✅ Si falla el envío, se reintenta automáticamente

---

## 🧪 Cómo Probar

### Usando Mailtrap o Log (Desarrollo):

1. **Configura tu `.env`** con una de las opciones anteriores

2. **Limpia cachés:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Si usas colas, inicia el worker:**
   ```bash
   php artisan queue:work
   ```

4. **Registra un nuevo usuario:**
   - Ve a `/register`
   - Completa el formulario
   - Haz clic en "Registrarse"

5. **Verifica el correo:**
   - **Mailtrap:** Ve a tu inbox en Mailtrap.io
   - **Log:** Abre `storage/logs/laravel.log` y busca el HTML del correo

---

## 📊 Vista Previa del Correo

El correo incluye:

✅ **Header atractivo** con degradado morado  
✅ **Saludo personalizado** con el nombre del usuario  
✅ **Email de confirmación** destacado  
✅ **Lista de características** de la app:
   - Crear y seguir hábitos
   - Registrar progreso diario
   - Recibir recordatorios
   - Desbloquear logros
   - Ver estadísticas

✅ **Botón de acción** para ir a la app  
✅ **Consejo motivacional**  
✅ **Footer profesional** con información legal  

---

## 🔄 Flujo Completo

```
Usuario se registra
        ↓
RegisteredUserController crea el usuario
        ↓
Se envía el correo (cola)
        ↓
Usuario recibe correo de bienvenida
        ↓
Usuario hace clic en "Ir a KudosApp"
        ↓
Usuario inicia sesión y usa la app
```

---

## 🎨 Personalización del Correo

Puedes personalizar el correo editando:

**Vista:** `resources/views/emails/registro-confirmado.blade.php`

**Clase Mailable:** `app/Mail/RegistroConfirmado.php`

### Cambiar el asunto:

```php
// En RegistroConfirmado.php
public function envelope(): Envelope
{
    return new Envelope(
        subject: 'Tu Nuevo Asunto Aquí 🎉',
    );
}
```

### Agregar más datos:

```php
// En RegistroConfirmado.php
public function content(): Content
{
    return new Content(
        view: 'emails.registro-confirmado',
        with: [
            'userName' => $this->user->nombre ?? $this->user->name,
            'userEmail' => $this->user->email,
            'registrationDate' => $this->user->fecha_registro->format('d/m/Y'),
        ]
    );
}
```

---

## ⚠️ Problemas Comunes

### El correo no se envía

1. **Verifica las credenciales en `.env`**
2. **Limpia cachés:** `php artisan config:clear`
3. **Verifica que el worker de colas esté corriendo:** `php artisan queue:work`
4. **Revisa los logs:** `storage/logs/laravel.log`

### Error de conexión SMTP

1. Verifica que el puerto esté correcto (587 para TLS, 465 para SSL)
2. Verifica que `MAIL_ENCRYPTION` sea `tls` o `ssl`
3. Verifica que el firewall no bloquee el puerto

### El correo va a spam

1. Configura SPF y DKIM en tu dominio
2. Usa un servicio profesional como SendGrid o Mailgun
3. No uses Gmail en producción (tiene límites)

---

## 📝 Siguiente Paso

Para ver el correo funcionando ahora mismo con la configuración actual (log):

1. Regístrate en la app
2. Abre el archivo: `storage/logs/laravel.log`
3. Busca el HTML del correo al final del archivo
4. Copia el HTML y ábrelo en un navegador para ver el diseño

¡Listo! 🎉
