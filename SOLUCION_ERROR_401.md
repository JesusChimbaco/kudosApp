# 🔧 Solución: Error 401 Unauthenticated

## ❌ Problema

Al intentar crear un recordatorio desde la interfaz web, aparecía el error:
```
401 Unauthenticated
```

## 🔍 Causa

El problema era que estábamos usando **dos sistemas de autenticación diferentes**:

1. **Frontend (Inertia.js + Vue):** Usa **autenticación por sesión** (cookies)
2. **Rutas API originales:** Esperaban **autenticación con Sanctum tokens** (Bearer token)

Cuando la interfaz web intentaba hacer peticiones a `/api/habitos/{id}/recordatorios`, Laravel esperaba un Bearer token, pero solo recibía la cookie de sesión.

## ✅ Solución Implementada

Se crearon **rutas web dedicadas** que usan autenticación por **sesión** en lugar de tokens.

### 1. Nuevo Controlador: `RecordatorioWebController`

**Ubicación:** `app/Http/Controllers/Web/RecordatorioWebController.php`

- Usa `Auth::id()` en lugar de `$request->user()`
- Valida que el hábito pertenezca al usuario autenticado
- Misma funcionalidad que el API Controller pero para web

### 2. Nuevas Rutas Web

**Archivo:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas para recordatorios (autenticación por sesión)
    Route::get('/api/web/habitos/{habitoId}/recordatorios', [RecordatorioWebController::class, 'index']);
    Route::post('/api/web/habitos/{habitoId}/recordatorios', [RecordatorioWebController::class, 'store']);
    Route::patch('/api/web/habitos/{habitoId}/recordatorios/{recordatorioId}', [RecordatorioWebController::class, 'update']);
    Route::delete('/api/web/habitos/{habitoId}/recordatorios/{recordatorioId}', [RecordatorioWebController::class, 'destroy']);
    Route::post('/api/web/habitos/{habitoId}/recordatorios/{recordatorioId}/toggle', [RecordatorioWebController::class, 'toggle']);
});
```

**Características:**
- ✅ Middleware `auth` (sesión)
- ✅ Middleware `verified` (email verificado)
- ✅ Prefijo `/api/web/` para distinguirlas de las API REST
- ✅ NO requieren Bearer token

### 3. Vista Actualizada

**Archivo:** `resources/js/pages/Habitos/Recordatorios.vue`

Cambiamos todas las URLs de:
```javascript
// ❌ Antes (API con token)
/api/habitos/{id}/recordatorios

// ✅ Ahora (Web con sesión)
/api/web/habitos/{id}/recordatorios
```

## 🏗️ Arquitectura Actual

### API REST (Sanctum Token) - Para Apps Móviles / Externas
```
/api/habitos/{id}/recordatorios
- Requiere: Bearer Token
- Middleware: auth:sanctum
- Uso: Apps móviles, Postman, APIs externas
```

### API Web (Session) - Para Interfaz Web
```
/api/web/habitos/{id}/recordatorios
- Requiere: Cookie de sesión
- Middleware: auth, verified
- Uso: Interfaz web (Inertia.js + Vue)
```

## ✅ Resultado

Ahora la interfaz web funciona correctamente:
- ✅ Crear recordatorios
- ✅ Editar recordatorios
- ✅ Eliminar recordatorios
- ✅ Activar/Desactivar recordatorios
- ✅ Sin errores 401

## 📝 Rutas Registradas

```bash
GET    /api/web/habitos/{habitoId}/recordatorios          # Listar
POST   /api/web/habitos/{habitoId}/recordatorios          # Crear
PATCH  /api/web/habitos/{habitoId}/recordatorios/{id}     # Actualizar
DELETE /api/web/habitos/{habitoId}/recordatorios/{id}     # Eliminar
POST   /api/web/habitos/{habitoId}/recordatorios/{id}/toggle  # Toggle
```

## 🎯 Patrón Aplicado

Este es el mismo patrón que ya usabas para los hábitos:

```php
// Hábitos Web (sesión)
Route::get('/api/web/habitos', [HabitoController::class, 'index']);
Route::post('/api/web/habitos', [HabitoController::class, 'store']);

// Recordatorios Web (sesión) - NUEVO
Route::get('/api/web/habitos/{habitoId}/recordatorios', [RecordatorioWebController::class, 'index']);
Route::post('/api/web/habitos/{habitoId}/recordatorios', [RecordatorioWebController::class, 'store']);
```

## 🔒 Seguridad

Ambos sistemas son seguros:
- **API con tokens:** Stateless, ideal para móviles
- **Web con sesión:** Stateful, ideal para aplicaciones web

La validación de pertenencia del hábito se mantiene en ambos:
```php
if ($habito->user_id !== Auth::id()) {
    return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
}
```

## ✨ ¡Problema Resuelto!

Ahora puedes crear recordatorios desde la interfaz web sin errores de autenticación.
