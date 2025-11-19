# 📝 API de Registro Diario - Marcar Hábitos como Completados

## 📋 Tabla de Contenidos

1. [Marcar hábito como completado](#1-marcar-hábito-como-completado)
2. [Desmarcar hábito](#2-desmarcar-hábito)
3. [Obtener historial de registros](#3-obtener-historial-de-registros)
4. [Obtener registro por fecha](#4-obtener-registro-por-fecha)
5. [Obtener estadísticas detalladas](#5-obtener-estadísticas-detalladas)

---

## 1. Marcar hábito como completado

Marca un hábito como completado para una fecha específica. Actualiza automáticamente la racha del hábito.

**Endpoint:**
```
POST /api/habitos/{habito_id}/completar
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Parámetros de URL:**
- `habito_id` (integer, requerido): ID del hábito

**Body (JSON):**
```json
{
  "fecha": "2025-10-29",
  "veces": 1,
  "notas": "Completado en la mañana, me sentí muy bien"
}
```

**Parámetros del Body:**
- `fecha` (date, opcional): Fecha del registro (formato: YYYY-MM-DD). Por defecto es hoy.
- `veces` (integer, opcional): Número de veces que se completó. Por defecto es 1.
- `notas` (string, opcional): Notas adicionales del registro (máx. 500 caracteres).

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Hábito marcado como completado",
  "data": {
    "registro": {
      "id": 1,
      "habito_id": 5,
      "fecha": "2025-10-29",
      "completado": true,
      "veces_completado": 1,
      "notas": "Completado en la mañana, me sentí muy bien",
      "hora_completado": "2025-10-29T10:30:00.000000Z",
      "estado": "completado",
      "created_at": "2025-10-29T10:30:00.000000Z",
      "updated_at": "2025-10-29T10:30:00.000000Z"
    },
    "racha_actual": 5,
    "racha_maxima": 12
  }
}
```

**Ejemplo de uso:**

```javascript
// Marcar como completado hoy
fetch('http://localhost:8000/api/habitos/5/completar', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    veces: 1,
    notas: 'Hice ejercicio por 30 minutos'
  })
})

// Marcar como completado para una fecha específica
fetch('http://localhost:8000/api/habitos/5/completar', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    fecha: '2025-10-28',
    veces: 2,
    notas: 'Hice ejercicio 2 veces'
  })
})
```

**Notas importantes:**
- Si ya existe un registro para la fecha, se incrementan las `veces_completado`
- El `estado` se calcula automáticamente:
  - `completado`: Si `veces_completado >= objetivo_diario`
  - `parcial`: Si `veces_completado < objetivo_diario`
  - Si no hay objetivo_diario, siempre es `completado`
- La racha se actualiza automáticamente

---

## 2. Desmarcar hábito

Resta veces completadas o elimina el estado completado de un hábito.

**Endpoint:**
```
POST /api/habitos/{habito_id}/descompletar
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "fecha": "2025-10-29",
  "veces": 1
}
```

**Parámetros del Body:**
- `fecha` (date, opcional): Fecha del registro. Por defecto es hoy.
- `veces` (integer, opcional): Número de veces a restar. Por defecto es 1.

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Registro actualizado",
  "data": {
    "registro": {
      "id": 1,
      "habito_id": 5,
      "fecha": "2025-10-29",
      "completado": false,
      "veces_completado": 0,
      "notas": null,
      "hora_completado": null,
      "estado": "pendiente",
      "created_at": "2025-10-29T10:30:00.000000Z",
      "updated_at": "2025-10-29T10:35:00.000000Z"
    },
    "racha_actual": 4,
    "racha_maxima": 12
  }
}
```

**Respuesta Error (404):**
```json
{
  "success": false,
  "message": "No hay registro para esta fecha"
}
```

---

## 3. Obtener historial de registros

Obtiene el historial de registros de un hábito con filtros opcionales.

**Endpoint:**
```
GET /api/habitos/{habito_id}/registros
```

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `fecha_inicio` (date, opcional): Fecha inicio del rango (YYYY-MM-DD)
- `fecha_fin` (date, opcional): Fecha fin del rango (YYYY-MM-DD)
- `limit` (integer, opcional): Límite de resultados (1-100). Por defecto: 30

**Ejemplos de URL:**
```
GET /api/habitos/5/registros
GET /api/habitos/5/registros?limit=50
GET /api/habitos/5/registros?fecha_inicio=2025-10-01&fecha_fin=2025-10-31
GET /api/habitos/5/registros?fecha_inicio=2025-10-01&limit=100
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "habito_id": 5,
      "fecha": "2025-10-29",
      "completado": true,
      "veces_completado": 1,
      "notas": "Completado en la mañana",
      "hora_completado": "2025-10-29T10:30:00.000000Z",
      "estado": "completado",
      "created_at": "2025-10-29T10:30:00.000000Z",
      "updated_at": "2025-10-29T10:30:00.000000Z"
    },
    {
      "id": 2,
      "habito_id": 5,
      "fecha": "2025-10-28",
      "completado": true,
      "veces_completado": 2,
      "notas": null,
      "hora_completado": "2025-10-28T15:45:00.000000Z",
      "estado": "completado",
      "created_at": "2025-10-28T15:45:00.000000Z",
      "updated_at": "2025-10-28T15:45:00.000000Z"
    },
    {
      "id": 1,
      "habito_id": 5,
      "fecha": "2025-10-27",
      "completado": true,
      "veces_completado": 1,
      "notas": "Buen día",
      "hora_completado": "2025-10-27T09:00:00.000000Z",
      "estado": "completado",
      "created_at": "2025-10-27T09:00:00.000000Z",
      "updated_at": "2025-10-27T09:00:00.000000Z"
    }
  ]
}
```

---

## 4. Obtener registro por fecha

Obtiene el registro de un hábito para una fecha específica.

**Endpoint:**
```
GET /api/habitos/{habito_id}/registro/{fecha}
```

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parámetros de URL:**
- `habito_id` (integer, requerido): ID del hábito
- `fecha` (date, requerido): Fecha del registro (YYYY-MM-DD)

**Ejemplo:**
```
GET /api/habitos/5/registro/2025-10-29
```

**Respuesta Exitosa (200) - Con registro:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "habito_id": 5,
    "fecha": "2025-10-29",
    "completado": true,
    "veces_completado": 1,
    "notas": "Completado en la mañana",
    "hora_completado": "2025-10-29T10:30:00.000000Z",
    "estado": "completado",
    "created_at": "2025-10-29T10:30:00.000000Z",
    "updated_at": "2025-10-29T10:30:00.000000Z"
  }
}
```

**Respuesta Exitosa (200) - Sin registro:**
```json
{
  "success": true,
  "data": null,
  "message": "No hay registro para esta fecha"
}
```

---

## 5. Obtener estadísticas detalladas

Obtiene estadísticas detalladas de un hábito por periodo.

**Endpoint:**
```
GET /api/habitos/{habito_id}/estadisticas-detalladas
```

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `periodo` (string, opcional): Periodo de análisis. Valores: `semana`, `mes`, `trimestre`, `año`, `total`. Por defecto: `mes`

**Ejemplos de URL:**
```
GET /api/habitos/5/estadisticas-detalladas
GET /api/habitos/5/estadisticas-detalladas?periodo=semana
GET /api/habitos/5/estadisticas-detalladas?periodo=año
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "total_dias": 25,
    "dias_completados": 20,
    "dias_parciales": 3,
    "dias_perdidos": 0,
    "veces_total": 23,
    "promedio_veces_por_dia": 1.15,
    "racha_actual": 5,
    "racha_maxima": 12,
    "tasa_completitud": 80.0,
    "periodo": "mes",
    "fecha_inicio": "2025-10-01",
    "fecha_fin": "2025-10-29"
  }
}
```

**Descripción de campos:**
- `total_dias`: Total de días con registros
- `dias_completados`: Días con estado "completado"
- `dias_parciales`: Días con estado "parcial"
- `dias_perdidos`: Días con estado "perdido"
- `veces_total`: Suma total de veces completado
- `promedio_veces_por_dia`: Promedio de veces por día
- `racha_actual`: Racha actual de días consecutivos
- `racha_maxima`: Racha máxima histórica
- `tasa_completitud`: Porcentaje de días completados (%)

---

## 📊 Estados del Registro

| Estado | Descripción |
|--------|-------------|
| `pendiente` | No se ha completado aún |
| `parcial` | Completado parcialmente (veces < objetivo) |
| `completado` | Completado exitosamente (veces >= objetivo) |
| `perdido` | Día perdido (no completado) |

---

## 🔥 Cálculo de Rachas

La racha se calcula automáticamente al marcar o desmarcar un hábito:

1. **Racha Actual**: Cuenta días consecutivos completados hasta hoy o ayer
2. **Racha Máxima**: Registro histórico de la mayor racha alcanzada

**Reglas:**
- Solo se cuentan registros con estado `completado`
- Debe haber continuidad: hoy → ayer → anteayer, etc.
- Si hay un día sin registro completado, la racha se reinicia
- La racha máxima se actualiza automáticamente si se supera

---

## 💡 Ejemplos de Uso Completos

### Marcar hábito hoy con notas
```javascript
const marcarCompletado = async (habitoId) => {
  const response = await fetch(`/api/habitos/${habitoId}/completar`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      notas: '¡Me sentí genial hoy!'
    })
  });
  
  const data = await response.json();
  console.log(`Racha actual: ${data.data.racha_actual} días 🔥`);
};
```

### Obtener calendario del mes
```javascript
const obtenerCalendarioMes = async (habitoId) => {
  const fechaInicio = '2025-10-01';
  const fechaFin = '2025-10-31';
  
  const response = await fetch(
    `/api/habitos/${habitoId}/registros?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&limit=31`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }
  );
  
  const data = await response.json();
  return data.data; // Array de registros del mes
};
```

### Verificar si completó hoy
```javascript
const completoHoy = async (habitoId) => {
  const hoy = new Date().toISOString().split('T')[0];
  
  const response = await fetch(`/api/habitos/${habitoId}/registro/${hoy}`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  return data.data?.completado === true;
};
```

---

## 🧪 Pruebas con Postman

1. **Obtén tu token** haciendo login en `/api/login`
2. **Crea un hábito** con POST `/api/habitos`
3. **Márcalo como completado** con POST `/api/habitos/{id}/completar`
4. **Verifica las estadísticas** con GET `/api/habitos/{id}/estadisticas-detalladas`
5. **Consulta el historial** con GET `/api/habitos/{id}/registros`

---

## ⚠️ Errores Comunes

### Error 404: Hábito no encontrado
```json
{
  "message": "No query results for model [App\\Models\\Habito] {id}"
}
```
**Solución**: Verifica que el `habito_id` sea correcto y pertenezca al usuario autenticado.

### Error 422: Validación fallida
```json
{
  "message": "The fecha field must be a valid date.",
  "errors": {
    "fecha": ["The fecha field must be a valid date."]
  }
}
```
**Solución**: Asegúrate de que la fecha esté en formato `YYYY-MM-DD`.

---

¿Listo para empezar a marcar tus hábitos como completados? 🎯
