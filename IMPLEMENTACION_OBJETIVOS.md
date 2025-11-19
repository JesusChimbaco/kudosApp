# ✅ Sistema de Objetivos y Recordatorios de Seguimiento - IMPLEMENTADO

## 🎯 FUNCIONALIDADES IMPLEMENTADAS:

### 1. Sistema de Objetivos/Metas
Los usuarios ahora pueden crear objetivos (ej: "Bajar de peso", "Mejorar mi salud") y asociar múltiples hábitos a cada objetivo. Esto permite tener un propósito claro para cada hábito.

### 2. Recordatorios de Seguimiento
Si el usuario no marca su hábito como completado después del tiempo configurado (por defecto 5 minutos), se envía automáticamente un segundo recordatorio que:
- Hace énfasis en el objetivo asociado al hábito
- Muestra un mensaje de urgencia
- Recuerda la racha actual para motivar al usuario

---

## ✅ ARCHIVOS CREADOS Y MODIFICADOS:

### Migraciones (Ejecutadas):
1. ✅ `2025_11_18_095715_create_objetivos_table.php`
   - Tabla de objetivos con campos: nombre, descripción, tipo, fechas, emoji, color
   
2. ✅ `2025_11_18_095750_add_objetivo_id_to_habitos_table.php`
   - Agrega relación objetivo_id a habitos (nullable)
   
3. ✅ `2025_11_18_095801_add_recordatorio_seguimiento_to_recordatorios_table.php`
   - Agrega campos: enviar_seguimiento (bool), minutos_seguimiento (int)
   
4. ✅ `2025_11_18_095925_create_recordatorios_enviados_table.php`
   - Tabla para rastrear recordatorios enviados y su estado de completado

### Modelos:
1. ✅ `app/Models/Objetivo.php` - Modelo completo con:
   - Relaciones: user, habitos
   - Scopes: activos, completados, pendientes
   
2. ✅ `app/Models/RecordatorioEnviado.php` - Rastreo de envíos con:
   - Relaciones: recordatorio, habito
   - Scope: necesitaSeguimiento
   
3. ✅ `app/Models/Habito.php` - Actualizado:
   - Agregado objetivo_id al fillable
   - Agregada relación objetivo()
   
4. ✅ `app/Models/Recordatorio.php` - Actualizado:
   - Agregados campos enviar_seguimiento, minutos_seguimiento
   - Agregada relación recordatoriosEnviados()

### Controladores:
1. ✅ `app/Http/Controllers/Api/ObjetivoController.php` - CRUD completo:
   - index() - Listar objetivos con filtros
   - store() - Crear objetivo
   - show() - Ver objetivo con hábitos
   - update() - Actualizar objetivo
   - destroy() - Eliminar (con validación de hábitos asociados)
   - marcarCompletado() - Marcar objetivo como completado
   
2. ✅ `app/Http/Controllers/Api/HabitoController.php` - Actualizado:
   - Validación de objetivo_id en store()
   - Incluye relación 'objetivo' en todas las consultas

3. ✅ `app/Http/Controllers/Api/RegistroDiarioController.php` - Actualizado:
   - Al completar hábito, marca recordatorios_enviados como completados

### Jobs:
1. ✅ `app/Jobs/EnviarRecordatorioHabito.php` - Actualizado:
   - Registra cada envío en recordatorios_enviados
   - Programa automáticamente el job de seguimiento con delay
   
2. ✅ `app/Jobs/EnviarRecordatorioSeguimiento.php` - Nuevo:
   - Verifica que el hábito no esté completado
   - Envía email con énfasis en el objetivo
   - Marca seguimiento_enviado como true

### Mailables:
1. ✅ `app/Mail/RecordatorioSeguimientoHabito.php` - Nuevo:
   - Email con diseño urgente
   - Incluye información del objetivo
   - Muestra racha actual

2. ✅ `resources/views/emails/recordatorio-seguimiento-habito.blade.php` - Nuevo:
   - Vista HTML con diseño atractivo
   - Sección destacada del objetivo
   - Mensaje de urgencia
   - Botón CTA para completar

### Comandos:
1. ✅ `app/Console/Commands/VerificarRecordatoriosSeguimiento.php` - Nuevo:
   - Comando: `php artisan recordatorios:verificar-seguimiento`
   - Busca recordatorios pendientes de seguimiento
   - Verifica tiempo transcurrido
   - Despacha jobs de seguimiento

### Rutas:
1. ✅ `routes/api.php` - Agregadas rutas API:
   ```php
   GET    /api/objetivos
   POST   /api/objetivos
   GET    /api/objetivos/{id}
   PUT    /api/objetivos/{id}
   DELETE /api/objetivos/{id}
   POST   /api/objetivos/{id}/completar
   ```

2. ✅ `routes/web.php` - Agregadas rutas Web (sesión):
   ```php
   GET    /api/web/objetivos
   POST   /api/web/objetivos
   GET    /api/web/objetivos/{id}
   PUT    /api/web/objetivos/{id}
   DELETE /api/web/objetivos/{id}
   POST   /api/web/objetivos/{id}/completar
   ```

3. ✅ `routes/console.php` - Programado comando:
   ```php
   Schedule::command('recordatorios:verificar-seguimiento')
       ->everyMinute()
       ->withoutOverlapping()
       ->runInBackground();
   ```

---

## 🔄 FLUJO DE FUNCIONAMIENTO:

### 1. Creación de Objetivo:
```
Usuario crea objetivo → POST /api/web/objetivos
{
  nombre: "Bajar de peso",
  descripcion: "Quiero perder 10kg",
  tipo: "salud",
  emoji: "🎯"
}
```

### 2. Asociar Hábito a Objetivo:
```
Usuario crea hábito → POST /api/web/habitos
{
  nombre: "Correr 30 minutos",
  objetivo_id: 1,  ← Asociado al objetivo
  ...
}
```

### 3. Recordatorio Inicial:
```
Scheduler ejecuta → recordatorios:enviar
  ↓
EnviarRecordatorioHabito Job
  ↓
1. Envía email inicial
2. Crea registro en recordatorios_enviados
3. Programa EnviarRecordatorioSeguimiento con delay de 5 minutos
```

### 4. Verificación de Seguimiento:
```
Scheduler ejecuta cada minuto → recordatorios:verificar-seguimiento
  ↓
VerificarRecordatoriosSeguimiento Command
  ↓
Busca recordatorios_enviados donde:
- seguimiento_enviado = false
- completado = false
- created_at >= 5 minutos atrás
  ↓
Despacha EnviarRecordatorioSeguimiento Job
```

### 5. Envío de Seguimiento:
```
EnviarRecordatorioSeguimiento Job
  ↓
1. Verifica que no esté completado
2. Carga habito + objetivo + usuario
3. Envía email con énfasis en objetivo
4. Marca seguimiento_enviado = true
```

### 6. Usuario Completa Hábito:
```
Usuario marca completado → POST /api/web/habitos/{id}/completar
  ↓
RegistroDiarioController::completar()
  ↓
1. Crea/actualiza registro_diario
2. Actualiza racha
3. Marca recordatorios_enviados como completados ← Cancela seguimientos
```

---

## 🧪 COMANDOS PARA PROBAR:

```bash
# Ver rutas de objetivos
php artisan route:list --path=objetivos

# Ejecutar verificación de seguimientos manualmente
php artisan recordatorios:verificar-seguimiento

# Ver programación de tareas
php artisan schedule:list

# Ejecutar scheduler (en desarrollo)
php artisan schedule:work

# Ver logs
tail -f storage/logs/laravel.log
```

---

## 📊 ESTRUCTURA DE BASE DE DATOS:

```
objetivos
├── id
├── user_id (FK → users)
├── nombre
├── descripcion
├── tipo (enum: salud, fitness, etc.)
├── emoji
├── color
├── fecha_inicio
├── fecha_objetivo
├── completado (bool)
├── fecha_completado
├── activo (bool)
└── timestamps

habitos
├── ... campos existentes ...
└── objetivo_id (FK → objetivos, nullable)

recordatorios
├── ... campos existentes ...
├── enviar_seguimiento (bool, default: true)
└── minutos_seguimiento (int, default: 5)

recordatorios_enviados
├── id
├── recordatorio_id (FK → recordatorios)
├── habito_id (FK → habitos)
├── fecha_envio
├── hora_envio
├── seguimiento_enviado (bool)
├── seguimiento_enviado_at
├── completado (bool)
├── completado_at
└── timestamps
```

---

## ✨ CARACTERÍSTICAS ADICIONALES:

1. **Validación de Objetivo en Hábitos**: Cuando se asocia un objetivo_id, se valida que exista
2. **Prevención de Envíos Duplicados**: Si el hábito se completa, no se envía seguimiento
3. **Configuración Flexible**: Cada recordatorio puede tener diferente tiempo de seguimiento
4. **Deshabilitación de Seguimiento**: Campo `enviar_seguimiento` permite desactivarlo por recordatorio
5. **Logs Detallados**: Todos los jobs y comandos registran información en logs

---

## 🎨 FRONTEND (PENDIENTE):

Para completar la implementación del frontend, necesitas crear:

1. **Vista de Objetivos** (`resources/js/pages/Objetivos/Index.vue`):
   - Listar objetivos
   - Crear/editar/eliminar objetivos
   - Ver hábitos asociados a cada objetivo
   - Marcar objetivo como completado

2. **Selector de Objetivo en Formulario de Hábito**:
   - Dropdown para seleccionar objetivo (opcional)
   - Mostrar objetivo asociado en lista de hábitos

3. **Configuración de Seguimiento en Recordatorios**:
   - Checkbox "Enviar recordatorio de seguimiento"
   - Input para minutos de espera

---

## 🚀 PRÓXIMOS PASOS RECOMENDADOS:

1. Implementar frontend para gestión de objetivos
2. Agregar estadísticas de objetivos en dashboard
3. Crear notificaciones push además de emails
4. Implementar sistema de logros por objetivos completados
5. Agregar gráficas de progreso por objetivo

---

✅ **IMPLEMENTACIÓN BACKEND COMPLETA Y FUNCIONAL**
