# TESTING: Sistema de Borrado y UI Mejorada de Nova

Este documento describe cómo probar las tres áreas críticas implementadas en Nova Academy.

---

## 🔴 ÁREA 1: Sistema de Borrado (CORREGIDO)

### Problema Original
El borrado fallaba porque cuando el usuario confirmaba con "sí", el backend no recordaba QUÉ debía borrar. Los `tool_calls` originales se perdían entre el primer intento y la confirmación.

### Solución Implementada
**Persistencia en sesión Laravel**: los `tool_calls` se guardan en `session('nova_pending_actions')` cuando se requiere confirmación, y se recuperan cuando llega `confirmed=true`.

### Cómo Probar

1. **Test básico de borrado:**
   ```
   Usuario: "Borra las clases de abril del curso Inglés Primero"
   Nova: [detecta deleteActivities, muestra confirmación con detalles]
   Usuario: "Sí"
   Nova: "✅ ¡Listo! Eliminé X actividades entre 01/04/2026 y 30/04/2026 para Inglés Primero. ¿En qué más te ayudo?"
   ```

2. **Verificar logs:**
   - Abre `storage/logs/laravel.log`
   - Busca `doDeleteActivities.before`, `doDeleteActivities.count`, `doDeleteActivities.after`
   - Confirma que los parámetros (teacher_id, course_id, fechas) son correctos
   - Verifica que `deleted` coincide con `count`

3. **Test de borrado sin registros:**
   ```
   Usuario: "Borra las clases de diciembre 2099"
   Nova: "⚠️ No encontré actividades para borrar con esos filtros. ¿Quieres que revisemos juntos qué hay en ese período?"
   ```

4. **Verificar la query DELETE en DB:**
   - El script de diagnóstico `tests_borrado_nova.php` confirma que:
     - La query DELETE ejecuta correctamente
     - La sesión persiste y recupera argumentos
     - El count antes/después es correcto

### Archivos Modificados
- `app/Http/Controllers/AICommandHandlerController.php`:
  - Líneas ~330-380: Persistencia de sesión en `requires_confirmation`
  - Líneas ~1000-1056: `doDeleteActivities()` con logs extensivos
  - Líneas ~1140-1160: `doDeleteResource()` con logs

---

## 🟡 ÁREA 2: Lectura Correcta del Calendario

### Mejoras Implementadas

1. **Nueva herramienta `getCurrentWeek`:**
   - Usa `Carbon::now()->startOfWeek()` y `endOfWeek()`
   - Timezone configurado en `.env` → `APP_TIMEZONE=America/Bogota`
   - Query ordena por `due_date ASC` con `orderBy('due_date')`

2. **Respuesta JSON Estructurada:**
   ```json
   {
     "type": "activity_list",
     "items": [
       {
         "id": 42,
         "title": "Matemáticas - Fracciones",
         "course": "Primer Grado",
         "date": "2026-03-24",
         "type": "clase",
         "weight": 15,
         "color": "#6366f1"
       }
     ],
     "quick_actions": [
       { "label": "📝 Planificar semana siguiente", "action": "..." },
       { "label": "🗑️ Borrar toda la semana", "action": "..." }
     ]
   }
   ```

3. **Inyección Automática de Calendario:**
   - **Siempre**: próximas 2 semanas en el system prompt
   - **Borrar/Modificar**: calendario extendido (6 meses atrás → 12 meses adelante)
   - Nova ya "ve" las actividades antes de responder

### Cómo Probar

```
Usuario: "¿Qué tengo esta semana?"
Nova: [llama getCurrentWeek → responde con cards visuales]
```

Si la semana está vacía:
```
Nova: "📭 Tu semana está libre, sin actividades registradas. ¿Quieres que planifiquemos algo?"
```

### Archivos Modificados
- `app/Http/Controllers/AICommandHandlerController.php`:
  - Líneas ~1085-1130: `getCurrentWeek()` con respuesta JSON
  - Líneas ~1132-1152: `calendarActivitiesBetween()` (reutilizable)
  - Líneas ~1154-1170: `buildCalendarSnapshotLines()` (inyección en system prompt)
  - Líneas ~344-356: Inyección automática de calendario en cada mensaje

---

## 🟢 ÁREA 3: UI Mejorada con Componentes Visuales

### Componentes Implementados

#### 1. **Activity Card Component**
**Archivo:** `resources/views/components/nova-activity-card.blade.php`

Características:
- Franja de color lateral según curso (hash consistente)
- Título en negrita + badges (CLASE/ACTIVIDAD/TAREA, peso%)
- Metadata: curso + fecha formateada en español
- Botón "Ver detalle →" con hover effect
- Soporte dark mode

Uso en Blade:
```blade
<x-nova-activity-card :activity="[
    'id' => 42,
    'title' => 'Fracciones',
    'course' => 'Matemáticas 3ro',
    'date' => '2026-03-24',
    'type' => 'clase',
    'weight' => 15,
    'color' => '#6366f1'
]" />
```

#### 2. **Inline Activity Cards (dentro de la burbuja)**
Renderizadas automáticamente cuando Nova devuelve `type: 'activity_list'`.

CSS clases: `.nova-activity-card-inline`, `.card-content-inline`, `.card-title-inline`, etc.

#### 3. **Quick Action Buttons**
Botones pill con hover effect que envían el `action` como si el usuario lo hubiera escrito:

```html
<button @click="input = qa.action; sendCommand()">
  📝 Planificar semana siguiente
</button>
```

#### 4. **Toast Notifications**
Sistema de notificaciones flotantes (3 segundos, auto-dismiss):

```js
this.showToast('✅ Actividades eliminadas correctamente', 'success', '✅');
this.showToast('❌ No se pudo completar la acción', 'error', '❌');
```

CSS clases: `.nova-toast`, tipos: `success`, `error`, `warning`

#### 5. **Skeleton Loaders**
Reemplazaron los "typing dots" con 2 cards animadas (pulse gradient):

```html
<div class="skeleton-card">
  <div class="skeleton-strip"></div>
  <div class="skeleton-line" style="width: 75%;"></div>
  ...
</div>
```

### Cómo Probar

1. **Activity Cards:**
   ```
   Usuario: "¿Qué tengo esta semana?"
   → Verás cards visuales en lugar de texto plano
   ```

2. **Quick Actions:**
   - Haz click en "📝 Planificar semana siguiente"
   - El input se llena automáticamente y se envía

3. **Toasts:**
   - Borra actividades → toast verde "✅ Actividades eliminadas"
   - Si hay error → toast rojo "❌ No se pudo completar"

4. **Skeleton Loaders:**
   - Envía cualquier comando
   - Durante `loading=true` verás 2 cards animadas en lugar de puntos

### Archivos Modificados
- `resources/views/components/nova-activity-card.blade.php`: **NUEVO componente Blade**
- `resources/views/components/ai-assistant-bubble.blade.php`:
  - Líneas ~30-36: Clase `.ai-bubble-assistant` para respuestas conversacionales
  - Líneas ~64-115: Estilos para skeleton loaders
  - Líneas ~116-158: Estilos para toast notifications
  - Líneas ~159-256: Estilos para activity cards inline + quick action buttons
  - Líneas ~368-420: Template `x-if="msg.role === 'activity_list'"` con loop de items
  - Líneas ~423-438: Skeleton loaders en lugar de typing dots
  - Líneas ~500-510: Objeto `toast` en el data de Alpine
  - Líneas ~789-828: `handleResponse()` detecta `activity_list` y renderiza cards
  - Líneas ~867-872: Función `showToast()`

---

## Checklist Final de Verificación

- [✅] DELETE ejecuta correctamente en DB (verificado con test)
- [✅] Sesión persiste tool_calls entre confirmación
- [✅] Logs detallados en cada paso del borrado
- [✅] Respuestas apropiadas (éxito / sin registros / error)
- [✅] `getCurrentWeek` con startOfWeek/endOfWeek
- [✅] Timezone configurado en `.env` y `config/app.php`
- [✅] Respuestas JSON estructuradas con `type: activity_list`
- [✅] Componente `nova-activity-card.blade.php` creado
- [✅] Renderer JSON en burbuja (detecta `msg.role === 'activity_list'`)
- [✅] Toast system con 3 tipos (success, error, warning)
- [✅] Skeleton loaders con animación pulse
- [✅] Quick action buttons funcionan con `@click`

---

## Comandos de Prueba Recomendados

```bash
# 1. Limpiar cache
php artisan config:clear && php artisan cache:clear

# 2. Ver logs en tiempo real (Windows PowerShell)
Get-Content storage/logs/laravel.log -Wait -Tail 50

# 3. Verificar sintaxis PHP
php -l app/Http/Controllers/AICommandHandlerController.php

# 4. Rebuild assets
npm run build
```

---

## Próximos Tests Recomendados

1. **Test de borrado completo:**
   - Crear actividades para un mes específico
   - Borrar por rango de fechas sin course_id (debería borrar todo el profesor)
   - Borrar por rango + course_id (debería filtrar)

2. **Test de conversación multi-turno:**
   ```
   Usuario: "Hazme una clase de ciencia"
   Nova: "¿Para qué grado es y qué tema específico quieres tocar?"
   Usuario: "Primer grado, sobre volcanes"
   Nova: "¿Prefieres que la clase sea teórica o con un experimento?"
   Usuario: "Teórica"
   Nova: "Perfecto, tengo todo: clase teórica de volcanes para Primer Grado. ¿Procedo a crearlo?"
   Usuario: "Sí"
   Nova: [ejecuta createActivity con los datos acumulados]
   ```

3. **Test de inyección de calendario:**
   ```
   Usuario: "Borra las clases de abril"
   → Nova debe identificar las actividades del calendario inyectado antes de pedir confirmación
   → NO debe decir "no veo el calendario" o "necesito que me digas cuáles"
   ```
