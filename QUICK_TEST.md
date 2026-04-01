# ⚡ PRUEBA RÁPIDA: Verifica que Todo Funcione

Sigue estos 3 pasos para confirmar que la implementación está correcta.

---

## 🔴 Paso 1: Prueba el Borrado (2 minutos)

### 1.1 Crear datos de prueba
Abre la burbuja de IA (botón morado flotante) y escribe:

```
Planifica el mes de julio para Inglés Primero con temas de vocabulario
```

Confirma → deberías ver ~8 actividades creadas.

### 1.2 Borrar con confirmación
En la burbuja, escribe:

```
Borra las clases de julio de Inglés Primero
```

**✅ Verifica:**
- Aparece panel de confirmación con warning rojo
- Muestra cuántas actividades se van a borrar
- Hay botones "Sí, ejecutar" y "Cancelar"

### 1.3 Confirmar
Click en "Sí, ejecutar"

**✅ Verifica:**
- Toast verde aparece arriba-derecha: "✅ Actividades eliminadas correctamente"
- Nova responde: "✅ ¡Listo! Eliminé X actividades... ¿En qué más te ayudo?"
- Las actividades desaparecen del calendario

### 1.4 Revisar logs
Abre `storage/logs/laravel.log` y busca las últimas líneas:

```
[timestamp] local.INFO: doDeleteActivities.before {"teacher_id":1,"course_id":1,...}
[timestamp] local.INFO: doDeleteActivities.count {"count":8,...}
[timestamp] local.INFO: doDeleteActivities.after {"deleted":8,...}
```

**✅ Si ves estos 3 logs → el borrado funciona correctamente.**

---

## 🟡 Paso 2: Prueba la Consulta Semanal (1 minuto)

### 2.1 Crear actividades para esta semana
Si no tienes actividades esta semana, crea 2-3:

```
Crea una clase de verbos para el lunes en Inglés Primero
```

### 2.2 Consultar semana
Escribe en la burbuja:

```
¿Qué tengo esta semana?
```

**✅ Verifica:**
- Aparecen **activity cards visuales** (no texto plano)
- Cada card tiene:
  - Franja de color a la izquierda
  - Título de la actividad
  - Badge CLASE o ACTIVIDAD
  - Nombre del curso + fecha
- Aparecen **quick action buttons** debajo:
  - "📝 Planificar semana siguiente"
  - "🗑️ Borrar toda la semana"

### 2.3 Probar quick action
Click en "📝 Planificar semana siguiente"

**✅ Verifica:**
- El input se llena automáticamente
- Se envía el comando inmediatamente
- Nova responde

---

## 🟢 Paso 3: Prueba los Componentes Visuales (1 minuto)

### 3.1 Skeleton Loaders
Escribe cualquier comando en la burbuja.

**✅ Verifica durante `loading=true`:**
- Aparecen 2 cards skeleton con animación pulse
- NO aparecen los "typing dots" antiguos
- La animación es fluida (gradiente deslizándose)

### 3.2 Toast Notifications
Borra actividades (cualquier rango).

**✅ Verifica:**
- Toast verde aparece: "✅ Actividades eliminadas correctamente"
- Está posicionado arriba del botón flotante
- Desaparece después de 3 segundos
- Animación slide-in desde la derecha

### 3.3 Conversación Multi-Turno
Escribe:

```
Hazme una clase de ciencia
```

**✅ Verifica:**
- Nova NO crea nada
- Responde con pregunta: "¿Para qué grado es y qué tema específico quieres tocar?"
- La burbuja es `.ai-bubble-assistant` (fondo violeta claro)

Responde:

```
Tercer grado, fotosíntesis
```

**✅ Verifica:**
- Nova confirma: "Perfecto, tengo todo: clase de fotosíntesis para Tercer Grado. ¿Procedo a crearlo?"

Responde:

```
Adelante
```

**✅ Verifica:**
- Nova ejecuta `createActivity`
- La actividad se crea con descripción Markdown estructurada
- Tiene las 3 secciones: **INICIO**, **DESARROLLO**, **CIERRE**

---

## 🚨 Si Algo Falla

### Problema: Borrado no funciona
```bash
# 1. Limpiar sesión
php artisan cache:clear

# 2. Verificar logs
cat storage/logs/laravel.log | grep doDeleteActivities

# 3. Si no hay logs, el DELETE no se está ejecutando
# → Revisar que confirmed=true llegue al backend
```

### Problema: Cards no se renderizan
```bash
# 1. Rebuild
npm run build

# 2. Hard refresh (Ctrl+Shift+R)

# 3. Verificar consola del browser (F12)
# → Buscar errores de JavaScript
```

### Problema: Timezone incorrecto
```bash
# 1. Verificar .env
cat .env | grep TIMEZONE

# 2. Limpiar config
php artisan config:clear

# 3. Verificar en tinker
php artisan tinker
>>> Carbon::now()->timezone
>>> Carbon::now()->startOfWeek()
```

---

## ✅ Checklist Rápido (5 minutos total)

- [ ] Borrado con confirmación funciona y muestra toast verde
- [ ] Logs `doDeleteActivities.before/count/after` aparecen en laravel.log
- [ ] `getCurrentWeek` devuelve activity cards visuales
- [ ] Quick actions envían comandos al hacer click
- [ ] Skeleton loaders reemplazan typing dots
- [ ] Toasts aparecen y desaparecen automáticamente
- [ ] Nova hace preguntas antes de crear (no asume)
- [ ] Conversación multi-turno mantiene contexto
- [ ] Descripciones tienen **INICIO**, **DESARROLLO**, **CIERRE**

**Si todos ✅ → implementación exitosa.**

---

**Tiempo de prueba:** ~5 minutos  
**Prioridad:** Ejecutar AHORA antes de continuar desarrollando  
**Objetivo:** Confirmar que las 3 áreas están 100% funcionales
