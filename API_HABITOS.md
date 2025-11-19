# 📡 API REST - Hábitos

Documentación completa de los endpoints de la API REST para el módulo de Hábitos.

## 🔐 Autenticación

Todas las rutas requieren autenticación mediante Laravel Sanctum (session-based).

**Base URL:** `http://localhost:8000/api`

**Headers requeridos:**
```http
Accept: application/json
Content-Type: application/json
X-CSRF-TOKEN: {token}
```

---

## 📋 Endpoints Disponibles

### 1. **Listar Hábitos del Usuario**

Obtiene todos los hábitos del usuario autenticado.

```http
GET /api/habitos
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "nombre": "Hacer ejercicio",
      "descripcion": "30 minutos de ejercicio diario",
      "categoria_id": 3,
      "frecuencia": "diario",
      "dias_semana": null,
      "hora_recordatorio": "07:00",
      "objetivo_dias": 30,
      "fecha_inicio": "2025-10-20",
      "fecha_fin": null,
      "activo": true,
      "created_at": "2025-10-20T10:00:00.000000Z",
      "updated_at": "2025-10-20T10:00:00.000000Z",
      "categoria": {
        "id": 3,
        "nombre": "Ejercicio",
        "descripcion": "Hábitos relacionados con actividad física",
        "icono": "💪",
        "color": "#10b981",
        "activo": true
      },
      "recordatorios": []
    }
  ],
  "message": "Hábitos obtenidos correctamente"
}
```

---

### 2. **Crear Nuevo Hábito**

Crea un nuevo hábito para el usuario autenticado.

```http
POST /api/habitos
```

**Body (JSON):**
```json
{
  "nombre": "Leer 30 minutos",
  "descripcion": "Lectura diaria de libros de desarrollo personal",
  "categoria_id": 2,
  "frecuencia": "diario",
  "dias_semana": null,
  "hora_recordatorio": "20:00",
  "objetivo_dias": 21,
  "fecha_inicio": "2025-10-22",
  "fecha_fin": null,
  "activo": true
}
```

**Campos:**
- `nombre` (requerido, string, max:100) - Nombre del hábito
- `descripcion` (opcional, string, max:500) - Descripción detallada
- `categoria_id` (requerido, integer) - ID de la categoría (debe existir)
- `frecuencia` (requerido, enum) - Valores: `diario`, `semanal`, `mensual`, `personalizado`
- `dias_semana` (opcional, array) - Array de números 0-6 (0=Domingo, 6=Sábado). Solo para frecuencia semanal/personalizada
- `hora_recordatorio` (opcional, time:HH:mm) - Hora del recordatorio
- `objetivo_dias` (opcional, integer, min:1) - Días objetivo para completar
- `fecha_inicio` (opcional, date) - Fecha de inicio
- `fecha_fin` (opcional, date) - Fecha de finalización (debe ser posterior a fecha_inicio)
- `activo` (opcional, boolean) - Estado del hábito (default: true)

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "user_id": 1,
    "nombre": "Leer 30 minutos",
    "descripcion": "Lectura diaria de libros de desarrollo personal",
    "categoria_id": 2,
    "frecuencia": "diario",
    "dias_semana": null,
    "hora_recordatorio": "20:00",
    "objetivo_dias": 21,
    "fecha_inicio": "2025-10-22",
    "fecha_fin": null,
    "activo": true,
    "created_at": "2025-10-22T15:30:00.000000Z",
    "updated_at": "2025-10-22T15:30:00.000000Z",
    "categoria": {
      "id": 2,
      "nombre": "Productividad",
      "descripcion": "Hábitos que mejoran tu rendimiento",
      "icono": "⚡",
      "color": "#f59e0b",
      "activo": true
    },
    "recordatorios": []
  },
  "message": "Hábito creado exitosamente"
}
```

**Respuesta de error (422 - Validación):**
```json
{
  "message": "The nombre field is required. (and 1 more error)",
  "errors": {
    "nombre": ["The nombre field is required."],
    "categoria_id": ["The selected categoria_id is invalid."]
  }
}
```

---

### 3. **Obtener Hábito Específico**

Obtiene los detalles de un hábito específico (solo si pertenece al usuario autenticado).

```http
GET /api/habitos/{id}
```

**Parámetros:**
- `id` (integer) - ID del hábito

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "nombre": "Hacer ejercicio",
    "categoria": { ... },
    "recordatorios": [],
    "registros_diarios": []
  },
  "message": "Hábito obtenido correctamente"
}
```

**Respuesta de error (403 - Prohibido):**
```json
{
  "success": false,
  "message": "No tienes permiso para ver este hábito"
}
```

**Respuesta de error (404 - No encontrado):**
```json
{
  "message": "No query results for model [App\\Models\\Habito] {id}"
}
```

---

### 4. **Actualizar Hábito**

Actualiza un hábito existente (solo si pertenece al usuario autenticado).

```http
PUT /api/habitos/{id}
PATCH /api/habitos/{id}
```

**Parámetros:**
- `id` (integer) - ID del hábito

**Body (JSON):**
```json
{
  "nombre": "Hacer ejercicio actualizado",
  "activo": false
}
```

**Nota:** Todos los campos son opcionales. Solo envía los que quieres actualizar.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Hacer ejercicio actualizado",
    "activo": false,
    ...
  },
  "message": "Hábito actualizado exitosamente"
}
```

**Respuesta de error (403):**
```json
{
  "success": false,
  "message": "No tienes permiso para actualizar este hábito"
}
```

---

### 5. **Eliminar Hábito**

Elimina un hábito permanentemente (solo si pertenece al usuario autenticado).

```http
DELETE /api/habitos/{id}
```

**Parámetros:**
- `id` (integer) - ID del hábito

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Hábito eliminado exitosamente"
}
```

**Respuesta de error (403):**
```json
{
  "success": false,
  "message": "No tienes permiso para eliminar este hábito"
}
```

---

### 6. **Obtener Hábitos Activos**

Obtiene solo los hábitos activos del usuario autenticado.

```http
GET /api/habitos/activos
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Hacer ejercicio",
      "activo": true,
      ...
    }
  ],
  "message": "Hábitos activos obtenidos correctamente"
}
```

---

### 7. **Cambiar Estado Activo/Inactivo**

Alterna el estado de un hábito entre activo e inactivo.

```http
PATCH /api/habitos/{id}/toggle-activo
```

**Parámetros:**
- `id` (integer) - ID del hábito

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "activo": false,
    ...
  },
  "message": "Hábito desactivado"
}
```

---

### 8. **Obtener Estadísticas de Hábitos**

Obtiene estadísticas de los hábitos del usuario autenticado.

```http
GET /api/habitos/estadisticas
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "total_habitos": 10,
    "habitos_activos": 7,
    "habitos_inactivos": 3,
    "por_frecuencia": {
      "diario": 5,
      "semanal": 3,
      "mensual": 2
    },
    "por_categoria": {
      "Salud": 4,
      "Productividad": 3,
      "Ejercicio": 2,
      "Mindfulness": 1
    }
  },
  "message": "Estadísticas obtenidas correctamente"
}
```

---

### 9. **Obtener Usuario Autenticado**

Obtiene los datos del usuario autenticado con sus hábitos y logros.

```http
GET /api/user
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Jesus Chimbaco",
    "nombre": "Jesus",
    "email": "jesus@example.com",
    "habitos": [...],
    "logros": [...]
  }
}
```

---

## 🔒 Códigos de Estado HTTP

| Código | Significado |
|--------|-------------|
| `200` | OK - Solicitud exitosa |
| `201` | Created - Recurso creado exitosamente |
| `401` | Unauthorized - No autenticado |
| `403` | Forbidden - No tiene permisos para este recurso |
| `404` | Not Found - Recurso no encontrado |
| `422` | Unprocessable Entity - Errores de validación |
| `500` | Internal Server Error - Error del servidor |

---

## 📝 Ejemplo de Uso con Axios (Vue)

```javascript
// composables/useHabitos.js
import axios from 'axios';

export function useHabitos() {
  const API_URL = '/api/habitos';

  // Obtener todos los hábitos
  const getHabitos = async () => {
    const response = await axios.get(API_URL);
    return response.data;
  };

  // Crear hábito
  const createHabito = async (habitoData) => {
    const response = await axios.post(API_URL, habitoData);
    return response.data;
  };

  // Actualizar hábito
  const updateHabito = async (id, habitoData) => {
    const response = await axios.patch(`${API_URL}/${id}`, habitoData);
    return response.data;
  };

  // Eliminar hábito
  const deleteHabito = async (id) => {
    const response = await axios.delete(`${API_URL}/${id}`);
    return response.data;
  };

  // Toggle activo
  const toggleActivo = async (id) => {
    const response = await axios.patch(`${API_URL}/${id}/toggle-activo`);
    return response.data;
  };

  // Obtener estadísticas
  const getEstadisticas = async () => {
    const response = await axios.get(`${API_URL}/estadisticas`);
    return response.data;
  };

  return {
    getHabitos,
    createHabito,
    updateHabito,
    deleteHabito,
    toggleActivo,
    getEstadisticas,
  };
}
```

---

## 🧪 Pruebas con cURL

```bash
# Obtener todos los hábitos
curl -X GET http://localhost:8000/api/habitos \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE"

# Crear hábito
curl -X POST http://localhost:8000/api/habitos \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE" \
  -d '{
    "nombre": "Meditar 10 minutos",
    "categoria_id": 4,
    "frecuencia": "diario"
  }'

# Actualizar hábito
curl -X PATCH http://localhost:8000/api/habitos/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE" \
  -d '{"activo": false}'

# Eliminar hábito
curl -X DELETE http://localhost:8000/api/habitos/1 \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE"
```

---

## 🎯 Próximos Pasos

Para completar la funcionalidad de hábitos, también necesitarás:

1. **Registro Diario** - API para registrar completitud diaria
2. **Recordatorios** - API para gestionar recordatorios
3. **Categorías** - API para obtener categorías disponibles
4. **Logros** - API para gestionar logros desbloqueados

¿Quieres que continuemos con alguno de estos? 🚀
