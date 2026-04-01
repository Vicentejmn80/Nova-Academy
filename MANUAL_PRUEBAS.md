# 🧪 MANUAL DE PRUEBAS: Nova Academy Review

**Ejecuta estos tests en orden para verificar las 3 áreas implementadas.**

---

## Preparación

```bash
# Terminal 1: Limpiar caches
php artisan config:clear && php artisan cache:clear && php artisan view:clear

# Terminal 2: Iniciar servidor (si no está corriendo)
php -S 127.0.0.1:8080 -t public

# Terminal 3: Watch logs (opcional)
# PowerShell: Get-Content storage/logs/laravel.log -Wait -Tail 50
```

**Accede a:** http://127.0.0.1:8080

---

## 🔴 TEST 1: Sistema de Borrado (CRÍTICO)

### Escenario A: Borrado Exitoso

1. **Crear actividades de prueba:**
   - Abre la burbuja de IA flotante (botón morado abajo-derecha)
   - Escribe: `"Planifica el mes de mayo para Inglés Primero con temas de gramática"`
   - Confirma → debería crear ~8 actividades en mayo

2. **Ejecutar borrado:**
   - En la burbuja, escribe: `"Borra las clases de mayo de Inglés Primero"`
   - **Verifica:**
     - ✅ Nova muestra panel de confirmación con warning rojo
     - ✅ El panel lista las acciones destructivas
     - ✅ Hay botones "Sí, ejecutar" y "Cancelar"

3. **Confirmar borrado:**
   - Click en "Sí, ejecutar"
   - **Verifica:**
     - ✅ Toast verde aparece: "✅ Actividades eliminadas correctamente"
     - ✅ Mensaje de Nova: "✅ ¡Listo! Eliminé X actividades entre 01/05/2026 y 31/05/2026 para Inglés Primero. ¿En qué más te ayudo?"
     - ✅ Las actividades desaparecen del calendario (click "Actualizar" si es necesario)

4. **Revisar logs:**
   - Abre `storage/logs/laravel.log`
   - Busca: `doDeleteActivities.before`, `doDeleteActivities.count`, `doDeleteActivities.after`
   - **Verifica:**
     - ✅ Los parámetros (teacher_id, course_id, fechas) son correctos
     - ✅ `count` y `deleted` coinciden

### Escenario B: Borrado Sin Registros

1. Escribe: `"Borra las clases de diciembre 2099"`
2. Confirma
3. **Verifica:**
   - ✅ Nova responde: "⚠️ No encontré actividades para borrar con esos filtros. ¿Quieres que revisemos juntos qué hay en ese período?"
   - ✅ NO muestra toast (porque no hubo error, simplemente no había nada)

### Escenario C: Cancelación

1. Escribe: `"Borra todas las clases de junio"`
2. **Verifica:**
   - ✅ Panel de confirmación aparece
3. Click en "Cancelar"
4. **Verifica:**
   - ✅ El panel desaparece
   - ✅ No se ejecuta ningún borrado
   - ✅ No hay toast

---

## 🟡 TEST 2: Lectura de Calendario

### Test 2A: Consulta de Semana Actual

1. Asegúrate de tener actividades para esta semana (crea algunas si es necesario)
2. En la burbuja, escribe: `"¿Qué tengo esta semana?"`
3. **Verifica:**
   - ✅ Nova llama `getCurrentWeek`
   - ✅ Aparecen **activity cards visuales** (no texto plano)
   - ✅ Cada card tiene:
     - Franja de color lateral
     - Título de la actividad
     - Badge: CLASE o ACTIVIDAD
     - Metadata: nombre del curso + fecha
   - ✅ Aparecen **quick action buttons** debajo:
     - "📝 Planificar semana siguiente"
     - "🗑️ Borrar toda la semana"

4. **Click en un quick action:**
   - Click en "📝 Planificar semana siguiente"
   - **Verifica:**
     - ✅ El input se llena automáticamente con el texto de la acción
     - ✅ Se envía el comando inmediatamente

### Test 2B: Semana Vacía

1. Borra todas las actividades de esta semana
2. Escribe: `"¿Qué tengo esta semana?"`
3. **Verifica:**
   - ✅ Nova responde: "📭 Tu semana está libre, sin actividades registradas. ¿Quieres que planifiquemos algo?"
   - ✅ NO renderiza cards (porque no hay items)

### Test 2C: Inyección Automática de Calendario

1. Escribe: `"Borra las clases de abril"`
2. **Verifica en el log:**
   - Nova NO debe decir "no veo el calendario"
   - Nova debe identificar las actividades del bloque inyectado automáticamente
   - El panel de confirmación debe mostrar cuántas actividades encontró

---

## 🟢 TEST 3: UI Mejorada

### Test 3A: Skeleton Loaders

1. Escribe cualquier comando que tome tiempo (ej: `"Planifica abril"`)
2. **Durante `loading=true`:**
   - ✅ Aparecen 2 cards skeleton con animación pulse
   - ✅ NO aparecen los "typing dots" antiguos
   - ✅ La animación es suave (gradiente deslizándose)

### Test 3B: Toasts

1. **Toast de éxito (borrado):**
   - Borra actividades → verifica toast verde con "✅ Actividades eliminadas correctamente"
   - El toast desaparece después de 3 segundos

2. **Toast de error:**
   - Simula un error (ej: desconecta internet y envía comando)
   - Verifica toast rojo: "❌ No se pudo completar la acción"

3. **Posición del toast:**
   - ✅ Está sobre el botón flotante (no tapa el panel)
   - ✅ Animación slide-in desde la derecha

### Test 3C: Respuestas Conversacionales de Nova

1. Escribe: `"Hazme una clase de ciencia"`
2. **Verifica:**
   - ✅ Nova NO crea nada inmediatamente
   - ✅ Responde con pregunta corta: "¿Para qué grado es y qué tema específico quieres tocar?"
   - ✅ La burbuja con la pregunta es `.ai-bubble-assistant` (fondo violeta claro)

3. Responde: `"Volcanes"`
4. **Verifica:**
   - ✅ Nova insiste: "Entiendo lo de los volcanes, pero ¿para qué grado?"
   - ✅ Mantiene persistencia sin ejecutar herramienta

5. Responde: `"Tercer grado"`
6. **Verifica:**
   - ✅ Nova: "Perfecto, tengo todo: clase de volcanes para Tercer Grado. ¿Procedo a crearlo?"

7. Responde: `"Sí"`
8. **Verifica:**
   - ✅ Nova ejecuta `createActivity`
   - ✅ La actividad se crea con descripción Markdown (3 secciones: **INICIO**, **DESARROLLO**, **CIERRE**)

---

## 🔧 Debugging

### Si el borrado falla:

```bash
# 1. Verificar sesión activa
php artisan tinker
>>> session()->get('nova_pending_actions')

# 2. Verificar logs
cat storage/logs/laravel.log | grep doDeleteActivities

# 3. Query manual en SQLite
sqlite3 database/database.sqlite
> SELECT * FROM activities WHERE teacher_id=1 AND due_date BETWEEN '2026-04-01' AND '2026-04-30';
> DELETE FROM activities WHERE teacher_id=1 AND due_date BETWEEN '2026-04-01' AND '2026-04-30';
```

### Si las cards no se renderizan:

```bash
# 1. Verificar que el build incluyó marked/dompurify
npm list marked dompurify

# 2. Verificar assets
ls -l public/build/assets/

# 3. Rebuild
npm run build

# 4. Hard refresh en el browser (Ctrl+Shift+R)
```

### Si el timezone está mal:

```bash
# Verificar .env
cat .env | grep TIMEZONE

# Verificar config
php artisan tinker
>>> config('app.timezone')

# Debería mostrar: "America/Bogota"
```

---

## ✅ Checklist Final (marcar al completar)

- [ ] Borrado ejecuta correctamente con logs visibles
- [ ] Sesión persiste tool_calls entre confirmación
- [ ] Toast verde aparece al borrar exitosamente
- [ ] Toast rojo aparece en errores
- [ ] `getCurrentWeek` devuelve activity cards visuales
- [ ] Quick actions funcionan al hacer click
- [ ] Skeleton loaders reemplazan typing dots
- [ ] Nova hace preguntas antes de crear (no asume)
- [ ] Nova confirma con "Perfecto, tengo todo..." antes de ejecutar
- [ ] Descripciones tienen **INICIO**, **DESARROLLO**, **CIERRE**
- [ ] Markdown se renderiza correctamente (listas, negritas)

---

**Si todos los items están ✅, la implementación está 100% funcional.**
