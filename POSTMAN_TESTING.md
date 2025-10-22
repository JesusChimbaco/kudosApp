# 🧪 Guía de Pruebas con Postman

## 📋 Configuración Inicial

### 1️⃣ Obtener Token de Autenticación

**Endpoint:**
```
POST http://localhost:8000/api/login
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "email": "jesus@example.com",
  "password": "tu_contraseña"
}
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Jesus Chimbaco",
      "email": "jesus@example.com"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456789",
    "token_type": "Bearer"
  }
}
```

⚠️ **IMPORTANTE:** Copia el `token` de la respuesta. Lo usarás en todas las demás peticiones.

---

## 2️⃣ Configurar Autenticación en Postman

### Opción A: En cada petición

1. Ve a la pestaña **Authorization**
2. Selecciona **Type: Bearer Token**
3. Pega el token en el campo **Token**

### Opción B: Variable de entorno (Recomendado)

1. Crea un entorno en Postman llamado "KudosApp Local"
2. Agrega estas variables:
   ```
   base_url = http://localhost:8000
   api_token = (pega aquí tu token después del login)
   ```
3. En cada petición usa:
   - URL: `{{base_url}}/api/habitos`
   - Authorization: Bearer Token → `{{api_token}}`

---

## 3️⃣ Probar Endpoints de Hábitos

### ✅ Listar Hábitos

```
GET {{base_url}}/api/habitos
```

**Headers:**
```
Authorization: Bearer {{api_token}}
Accept: application/json
```

---

### ✅ Crear Hábito

```
POST {{base_url}}/api/habitos
```

**Headers:**
```
Authorization: Bearer {{api_token}}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "nombre": "Hacer ejercicio",
  "descripcion": "30 minutos de ejercicio cardiovascular",
  "categoria_id": 3,
  "frecuencia": "diario",
  "hora_recordatorio": "07:00",
  "objetivo_dias": 30,
  "fecha_inicio": "2025-10-22",
  "activo": true
}
```

---

### ✅ Ver Hábito Específico

```
GET {{base_url}}/api/habitos/1
```

**Headers:**
```
Authorization: Bearer {{api_token}}
Accept: application/json
```

---

### ✅ Actualizar Hábito

```
PATCH {{base_url}}/api/habitos/1
```

**Headers:**
```
Authorization: Bearer {{api_token}}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "nombre": "Hacer ejercicio actualizado",
  "activo": false
}
```

---

### ✅ Eliminar Hábito

```
DELETE {{base_url}}/api/habitos/1
```

**Headers:**
```
Authorization: Bearer {{api_token}}
Accept: application/json
```

---

### ✅ Toggle Activo/Inactivo

```
PATCH {{base_url}}/api/habitos/1/toggle-activo
```

**Headers:**
```
Authorization: Bearer {{api_token}}
Accept: application/json
```

---

### ✅ Obtener Hábitos Activos

```
GET {{base_url}}/api/habitos/activos
```

**Headers:**
```
Authorization: Bearer {{api_token}}
Accept: application/json
```

---

### ✅ Obtener Estadísticas

```
GET {{base_url}}/api/habitos/estadisticas
```

**Headers:**
```
Authorization: Bearer {{api_token}}
Accept: application/json
```

---

### ✅ Cerrar Sesión (Revocar Token)

```
POST {{base_url}}/api/logout
```

**Headers:**
```
Authorization: Bearer {{api_token}}
Accept: application/json
```

---

## 🚨 Errores Comunes

### Error 401 - Unauthenticated
**Causa:** No estás enviando el token o el token es inválido.

**Solución:**
1. Verifica que el token esté en el header Authorization
2. Verifica que sea: `Bearer {token}` (con espacio después de Bearer)
3. Si el token expiró, haz login nuevamente

### Error 403 - Forbidden
**Causa:** Estás intentando acceder a un hábito que no te pertenece.

**Solución:** Solo puedes ver/editar/eliminar tus propios hábitos.

### Error 422 - Validation Error
**Causa:** Los datos enviados no cumplen las validaciones.

**Solución:** Revisa el mensaje de error en la respuesta. Te indica qué campo falta o es inválido.

### Error 500 - Internal Server Error
**Causa:** Error en el servidor.

**Solución:** 
1. Verifica los logs: `storage/logs/laravel.log`
2. Verifica que Laravel esté corriendo: `php artisan serve`
3. Limpia cachés: `php artisan optimize:clear`

---

## 📦 Colección de Postman

Puedes importar esta colección JSON en Postman:

```json
{
  "info": {
    "name": "KudosApp API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Authentication",
      "item": [
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"jesus@example.com\",\n  \"password\": \"password\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/login",
              "host": ["{{base_url}}"],
              "path": ["api", "login"]
            }
          }
        },
        {
          "name": "Logout",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{api_token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/logout",
              "host": ["{{base_url}}"],
              "path": ["api", "logout"]
            }
          }
        }
      ]
    },
    {
      "name": "Hábitos",
      "item": [
        {
          "name": "Listar Hábitos",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{api_token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/habitos",
              "host": ["{{base_url}}"],
              "path": ["api", "habitos"]
            }
          }
        },
        {
          "name": "Crear Hábito",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{api_token}}"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"nombre\": \"Hacer ejercicio\",\n  \"descripcion\": \"30 minutos de ejercicio\",\n  \"categoria_id\": 3,\n  \"frecuencia\": \"diario\",\n  \"hora_recordatorio\": \"07:00\",\n  \"objetivo_dias\": 30,\n  \"fecha_inicio\": \"2025-10-22\",\n  \"activo\": true\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/habitos",
              "host": ["{{base_url}}"],
              "path": ["api", "habitos"]
            }
          }
        }
      ]
    }
  ]
}
```

---

## 🎯 Pasos Rápidos para Empezar

1. **Asegúrate que Laravel esté corriendo:**
   ```bash
   php artisan serve
   ```

2. **Haz login en Postman:**
   - POST `http://localhost:8000/api/login`
   - Copia el token de la respuesta

3. **Guarda el token en una variable:**
   - Crea entorno "KudosApp Local"
   - Variable `api_token` = tu token

4. **Prueba listar hábitos:**
   - GET `http://localhost:8000/api/habitos`
   - Header: `Authorization: Bearer {{api_token}}`

¡Listo! 🚀
