{{--
  AI Assistant Bubble v2 — Typed action cards + screen context awareness.
  Requires: Alpine.js, Tailwind CSS, Font Awesome.
--}}
<style>
    #ai-bubble-btn {
        background: linear-gradient(135deg, #7c3aed 0%, #c026d3 100%);
        box-shadow: 0 8px 28px rgba(192,38,211,.5);
        transition: transform .2s, box-shadow .2s;
    }
    #ai-bubble-btn:hover { transform: scale(1.1); box-shadow: 0 12px 36px rgba(192,38,211,.65); }
    #ai-bubble-btn.listening {
        animation: mic-pulse 1.2s ease-in-out infinite;
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    }
    @keyframes mic-pulse {
        0%,100% { box-shadow: 0 8px 24px rgba(239,68,68,.4); }
        50%      { box-shadow: 0 8px 32px rgba(239,68,68,.8), 0 0 0 14px rgba(239,68,68,.1); }
    }
    #ai-panel {
        box-shadow: 0 24px 64px rgba(0,0,0,.18), 0 0 0 1px rgba(124,58,237,.08);
        transform-origin: bottom right;
        transition: opacity .2s, transform .2s;
    }
    #ai-panel.ai-entering { opacity: 0; transform: scale(.93) translateY(14px); }
    #ai-panel.ai-visible  { opacity: 1; transform: scale(1)   translateY(0); }

    /* Message bubbles */
    .ai-bubble-user { background:#ede9fe; color:#3b0764; border-radius:1rem 1rem .25rem 1rem; }
    .ai-bubble-info { background:#f8fafc; color:#475569; border-radius:.25rem 1rem 1rem 1rem; border:1px solid #e2e8f0; }

    /* Typed action card */
    .ai-action-card {
        border-radius: .875rem;
        border: 1px solid;
        padding: .625rem .875rem;
        display: flex;
        align-items: flex-start;
        gap: .625rem;
        font-size: .78rem;
        line-height: 1.5;
        max-width: 95%;
    }
    .ai-action-card.success-course   { background:#f0fdf4; border-color:#bbf7d0; color:#14532d; }
    .ai-action-card.success-activity { background:#eff6ff; border-color:#bfdbfe; color:#1e3a5f; }
    .ai-action-card.success-student  { background:#faf5ff; border-color:#e9d5ff; color:#4c1d95; }
    .ai-action-card.success-default  { background:#f0fdf4; border-color:#bbf7d0; color:#14532d; }
    .ai-action-card.error-card       { background:#fef2f2; border-color:#fecaca; color:#7f1d1d; }
    .ai-action-card.warn-card        { background:#fffbeb; border-color:#fde68a; color:#78350f; }

    .ai-action-icon {
        font-size: 1.1rem;
        line-height: 1;
        flex-shrink: 0;
        margin-top: .1rem;
    }

    /* Typing dots */
    .ai-dot { display:inline-block;width:6px;height:6px;border-radius:50%;background:#7c3aed;animation:ai-dot .8s infinite; }
    .ai-dot:nth-child(2){animation-delay:.15s;}
    .ai-dot:nth-child(3){animation-delay:.30s;}
    @keyframes ai-dot{0%,80%,100%{transform:translateY(0);opacity:.4;}40%{transform:translateY(-5px);opacity:1;}}

    /* Context badge */
    .ai-ctx-badge { animation: ctx-slide .25s ease; }
    @keyframes ctx-slide { from{opacity:0;transform:translateY(-4px);}to{opacity:1;transform:none;} }
</style>

<div
    id="ai-assistant-root"
    x-data="aiAssistant()"
    x-init="init()"
    class="fixed bottom-6 right-6 z-[9999] flex flex-col items-end gap-3"
>
    {{-- OVERLAY PANEL --}}
    <div id="ai-panel"
         x-show="open"
         x-cloak
         :class="panelClass"
         class="bg-white rounded-3xl overflow-hidden flex flex-col"
         style="width:390px; max-height:540px;"
         @keydown.escape.window="open = false"
    >
        {{-- Header --}}
        <div class="px-5 py-3.5 flex items-center justify-between shrink-0"
             style="background: linear-gradient(135deg, #6d28d9 0%, #c026d3 100%)">
            <div class="flex items-center gap-2.5 text-white">
                <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-robot text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-bold leading-tight">Asistente IA</p>
                    <p class="text-[10px] text-violet-200">Habla o escribe en lenguaje natural</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Context chip con más info --}}
                <div x-show="pageContext"
                     x-cloak
                     class="ai-ctx-badge bg-white/15 text-white text-[10px] font-semibold
                            px-2.5 py-1 rounded-full flex items-center gap-1 max-w-[120px]">
                    <i class="fa-solid fa-location-dot text-violet-200 text-[9px]"></i>
                    <span x-text="pageContext?.name ?? pageContext?.subject_name ?? ''" class="truncate"></span>
                </div>
                <button @click="open = false"
                        class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30
                               flex items-center justify-center transition text-white text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        {{-- Message log --}}
        <div id="ai-log"
             class="flex-1 overflow-y-auto px-4 py-3 space-y-2 flex flex-col min-h-0">

            <template x-if="messages.length === 0">
                <div class="text-center text-slate-400 text-xs py-8">
                    <i class="fa-solid fa-wand-magic-sparkles text-2xl text-violet-300 mb-2 block"></i>
                    Escribe o dicta un comando para empezar.
                </div>
            </template>

            <template x-for="(msg, idx) in messages" :key="idx">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">

                    {{-- User bubble --}}
                    <template x-if="msg.role === 'user'">
                        <div class="ai-bubble-user px-3.5 py-2 text-xs font-medium max-w-[85%]">
                            <span x-text="msg.text"></span>
                        </div>
                    </template>

                    {{-- Typed action card --}}
                    <template x-if="msg.role === 'action'">
                        <div class="ai-action-card"
                             :class="getCardClass(msg)">
                            <span class="ai-action-icon" x-text="msg.icon || '✦'"></span>
                            <span x-html="renderMarkdown(msg.text)"></span>
                        </div>
                    </template>

                    {{-- Plain info/error --}}
                    <template x-if="msg.role === 'info'">
                        <div class="ai-bubble-info px-3.5 py-2 text-xs max-w-[90%]"
                             :class="{ 'error-card ai-action-card': msg.type === 'error' }">
                            <span x-html="renderMarkdown(msg.text)"></span>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="loading" class="flex justify-start">
                <div class="ai-bubble-info px-4 py-2.5 flex items-center gap-1">
                    <span class="ai-dot"></span>
                    <span class="ai-dot"></span>
                    <span class="ai-dot"></span>
                </div>
            </div>

            {{-- Confirmation panel --}}
            <div x-show="confirmation" x-cloak class="flex justify-start w-full">
                <div class="ai-action-card warn-card w-full">
                    <span class="ai-action-icon">⚠️</span>
                    <div class="flex-1">
                        <p class="font-bold mb-1">Acción destructiva detectada</p>
                        <p x-text="confirmation?.warning" class="mb-2 text-xs opacity-80"></p>
                        <div class="space-y-1 mb-3">
                            <template x-for="a in (confirmation?.destructive_actions ?? [])" :key="a.function">
                                <div class="flex items-center gap-1.5 text-xs">
                                    <i class="fa-solid fa-trash-alt text-red-400 text-[10px]"></i>
                                    <span class="font-mono" x-text="formatDestructive(a)"></span>
                                </div>
                            </template>
                        </div>
                        <div class="flex gap-2">
                            <button @click="executeConfirmed()"
                                    class="flex-1 bg-red-600 hover:bg-red-700 text-white text-xs
                                           font-bold py-2 rounded-xl transition">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Sí, ejecutar
                            </button>
                            <button @click="confirmation = null"
                                    class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600
                                           text-xs font-semibold py-2 rounded-xl transition">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reload banner --}}
        <div x-show="showReload" x-cloak
             class="px-4 py-2.5 bg-emerald-50 border-t border-emerald-100
                    flex items-center justify-between shrink-0">
            <span class="text-xs text-emerald-700 font-medium">
                <i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i>
                Cambios aplicados correctamente
            </span>
            <button @click="reload()"
                    class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1">
                <i class="fa-solid fa-rotate-right text-xs"></i> Actualizar
            </button>
        </div>

        {{-- Input area --}}
        <div class="px-4 py-3 border-t border-slate-100 shrink-0">
            <div class="flex items-end gap-2">
                <textarea
                    x-model="input"
                    @keydown.enter.prevent="if(!$event.shiftKey) sendCommand()"
                    :disabled="loading"
                    rows="2"
                    :placeholder="getInputPlaceholder()"
                    class="flex-1 border border-slate-700 bg-[#0f1228] text-white caret-white rounded-2xl py-2.5 px-3.5 text-xs
                           resize-none focus:outline-none focus:border-violet-400 focus:ring-2
                           focus:ring-violet-500/30 transition disabled:opacity-50 disabled:bg-slate-900 placeholder:text-slate-400"
                ></textarea>
                <div class="flex flex-col gap-1.5 shrink-0">
                    <button @click="toggleVoice()"
                            :class="listening ? 'bg-red-500 text-white' : 'bg-violet-100 text-violet-600'"
                            class="w-9 h-9 rounded-xl flex items-center justify-center transition hover:scale-105"
                            title="Dictado de voz">
                        <i class="fa-solid text-sm" :class="listening ? 'fa-stop' : 'fa-microphone'"></i>
                    </button>
                    <button @click="sendCommand()"
                            :disabled="loading || !input.trim()"
                            class="w-9 h-9 rounded-xl bg-violet-600 hover:bg-violet-700
                                   disabled:opacity-40 disabled:cursor-not-allowed
                                   text-white flex items-center justify-center transition hover:scale-105">
                        <i class="fa-solid text-sm" :class="loading ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                    </button>
                </div>
            </div>
            <p x-show="listening"
               class="text-[10px] text-red-500 font-medium mt-1.5 text-center animate-pulse">
                🔴 Escuchando…
            </p>
        </div>
    </div>

    {{-- FLOATING TRIGGER --}}
    <button id="ai-bubble-btn"
            @click="togglePanel()"
            :class="{ 'listening': listening }"
            class="w-14 h-14 rounded-full text-white flex items-center justify-center shrink-0"
            title="Asistente IA"
    >
        <i class="fa-solid text-xl transition-all"
           :class="open ? 'fa-xmark' : (listening ? 'fa-microphone' : 'fa-robot')"></i>
    </button>
</div>

<script>
function aiAssistant() {
    return {
        open:         false,
        loading:      false,
        listening:    false,
        showReload:   false,
        input:        '',
        messages:     [],
        confirmation: null,
        recognition:  null,
        panelClass:   'ai-entering',
        pageContext:  null, // Ahora tiene TODOS los datos de la actividad

        init() {
            this.refreshContext();
            window.novaContext = this.pageContext ?? window.novaContext ?? null;

            window.addEventListener('ai-context-changed', (e) => {
                this.pageContext = e.detail ?? null;
                window.novaContext = this.pageContext;
            });

            window.addEventListener('open-activity-ai', (e) => {
                this.openWithFullContext(e.detail.activity, e.detail.courseName, e.detail.fullContext);
            });

            this.$watch('open', (val) => {
                if (val) {
                    this.panelClass = 'ai-entering';
                    requestAnimationFrame(() => setTimeout(() => { this.panelClass = 'ai-visible'; }, 10));
                    this.$nextTick(() => this.scrollToBottom());
                }
            });
        },

        refreshContext() {
            this.pageContext = window.novaContext ?? window.AI_PAGE_CONTEXT ?? null;
        },

        getInputPlaceholder() {
            const ctx = window.novaContext ?? this.pageContext ?? null;
            const title = ctx?.title ?? ctx?.name ?? ctx?.subject_name ?? null;
            if (title) {
                return `Modifica la clase "${title}" para que...`;
            }
            return 'Escribe un comando... (Enter para enviar)';
        },

        togglePanel() {
            this.refreshContext();
            this.open = !this.open;
        },

        toggleVoice() {
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                this.pushMsg('Tu navegador no soporta dictado de voz. Usa Chrome.', 'info', '🎙️');
                return;
            }
            if (this.listening) { this.recognition?.stop(); return; }

            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SR();
            this.recognition.lang = 'es-ES';
            this.recognition.continuous = false;
            this.recognition.interimResults = false;

            this.recognition.onresult = (e) => {
                this.input = e.results[0][0].transcript;
                this.listening = false;
                this.sendCommand();
            };
            this.recognition.onerror = (e) => {
                this.pushMsg(`Error de micrófono: ${e.error}`, 'info', '❌');
                this.listening = false;
            };
            this.recognition.onend = () => { this.listening = false; };
            this.recognition.start();
            this.listening = true;
            this.open = true;
        },

        // VERSIÓN FINAL - Con TODOS los datos
        openWithFullContext(activity, courseName, fullContext) {
            this.open = true;
            
            // Guardar TODO el contexto completo
            this.pageContext = fullContext || activity;
            window.AI_PAGE_CONTEXT = this.pageContext;
            window.novaContext = this.pageContext;
            
            window.dispatchEvent(new CustomEvent('ai-context-changed', { detail: this.pageContext }));
            
            this.messages = [];
            
            // Mensaje con TODA la información
            this.messages.push({
                role: 'info',
                text: `📝 **Editando:** ${activity.title}\n**Curso:** ${courseName}\n**ID:** ${activity.id}\n**Fecha:** ${activity.due_date || 'No definida'}`,
                type: 'info'
            });
            
            // Precargar prompt inteligente
            if (activity.type === 'clase') {
                this.input = `Modifica la clase "${activity.title}" para que los alumnos copien en su cuaderno la teoría y al finalizar escuchen una canción relacionada`;
            } else {
                this.input = `Modifica la actividad "${activity.title}"`;
            }
            
            this.$nextTick(() => {
                document.querySelector('#ai-assistant-root textarea')?.focus();
                this.scrollToBottom();
            });
        },

        async sendCommand() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.messages.push({ role: 'user', text });
            this.input      = '';
            this.loading    = true;
            this.showReload = false;
            this.$nextTick(() => this.scrollToBottom());

            try {
                // Enviar SIEMPRE el contexto vivo
                const liveContext = window.novaContext ?? this.pageContext ?? window.AI_PAGE_CONTEXT ?? null;
                this.pageContext = liveContext;

                const handledTaskIntent = await this.handleCreateTaskIntent(text, liveContext);
                if (handledTaskIntent) {
                    return;
                }

                const res = await fetch('/ai/command', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        prompt:         text,
                        message:        text,
                        screen_context: liveContext,
                        payload: {
                            mensaje_usuario: text,
                            contexto: liveContext,
                        },
                    }),
                });
                const json = await res.json();
                if (res.status === 422) {
                    throw new Error(json.error || json.message || 'Mensaje inválido');
                }
                if (json.action === 'refresh') {
                    window.dispatchEvent(new CustomEvent('ai-canvas-refresh'));
                }
                json._original_prompt = text;
                this.handleResponse(json);
            } catch (err) {
                this.pushMsg('Nova está pensando... reintentando', 'info', '⏳');
                console.error(err);
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        parseCreateTaskIntent(text, ctx) {
            const normalized = (text || '').toLowerCase();
            if (!normalized.includes('crear tarea')) {
                return null;
            }

            const activityIdFromContext = ctx?.id && (ctx?.type === 'activity' || ctx?.activity_type) ? Number(ctx.id) : null;
            const activityIdFromText = normalized.match(/actividad\s*#?\s*(\d+)/i)?.[1] ?? null;
            const activityId = Number(activityIdFromText || activityIdFromContext || 0);
            if (!activityId) {
                return { error: 'Para crear tarea por chat, abre primero una actividad o indica "actividad 123".' };
            }

            const dateMatch = text.match(/\b(\d{4}-\d{2}-\d{2})\b/);
            const pointsMatch = text.match(/(\d+)\s*(puntos|pts)/i);
            const titleRaw = text.replace(/crear tarea/ig, '').trim();
            const title = titleRaw.length > 3 ? titleRaw : 'Tarea creada desde chat IA';

            return {
                activity_id: activityId,
                titulo: title,
                descripcion: text,
                fecha_entrega: dateMatch ? dateMatch[1] : new Date(Date.now() + 7 * 86400000).toISOString().slice(0, 10),
                puntos: pointsMatch ? Number(pointsMatch[1]) : 20,
            };
        },

        async handleCreateTaskIntent(text, ctx) {
            const parsed = this.parseCreateTaskIntent(text, ctx);
            if (!parsed) return false;

            if (parsed.error) {
                this.pushMsg(parsed.error, 'info', '⚠️');
                return true;
            }

            const res = await fetch('/teacher/tareas/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(parsed),
            });

            const json = await res.json();
            if (!res.ok || !json.success) {
                this.pushMsg(json.error || json.message || 'No se pudo crear la tarea.', 'error', '❌');
                return true;
            }

            this.messages.push({
                role: 'action',
                text: `Tarea creada: **${json.tarea?.titulo ?? parsed.titulo}**`,
                icon: '🧩',
                action_type: 'activity',
                success: true,
            });
            window.dispatchEvent(new CustomEvent('ai-canvas-refresh'));
            this.showReload = true;
            return true;
        },

        async executeConfirmed() {
            if (!this.confirmation) return;
            const prompt = this.confirmation.original_prompt;
            this.confirmation = null;

            this.pushMsg('Confirmado. Ejecutando...', 'info', '✔️');
            this.loading = true;

            try {
                const res = await fetch('/ai/command', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ 
                        prompt, 
                        confirmed: true, 
                        screen_context: this.pageContext 
                    }),
                });
                const json = await res.json();
                this.handleResponse(json);
            } catch {
                this.pushMsg('Error al ejecutar la acción confirmada.', 'info', '❌');
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        handleResponse(json) {
            if (json.message && !json.actions && !json.requires_confirmation) {
                this.pushMsg(json.message, 'info', 'ℹ️');
                return;
            }
            if (json.requires_confirmation) {
                this.confirmation = { ...json, original_prompt: json._original_prompt || json.original_prompt || '' };
                this.$nextTick(() => this.scrollToBottom());
                return;
            }
            if (Array.isArray(json.actions)) {
                json.actions.forEach(a => {
                    this.messages.push({
                        role:        'action',
                        text:        a.message,
                        icon:        a.icon,
                        action_type: a.action_type,
                        success:     a.success,
                    });

                    if (a.action_type === 'ui_pref' && a.data) {
                        window.dispatchEvent(new CustomEvent('ai-ui-pref', { detail: a.data }));
                    }
                    if (['bulk_plan', 'activity', 'course', 'student', 'delete', 'grade'].includes(a.action_type) && a.success) {
                        window.dispatchEvent(new CustomEvent('ai-canvas-refresh'));
                    }
                });
                if (json.any_success) this.showReload = true;
            }
            if (json.message) {
                this.pushMsg(json.message, 'info', json.all_success ? '✅' : 'ℹ️');
            }
            if (json.error) {
                this.pushMsg(json.error, 'error', '❌');
            }
        },

        pushMsg(text, type, icon) {
            this.messages.push({ role: 'info', text, type, icon });
            this.$nextTick(() => this.scrollToBottom());
        },

        getCardClass(msg) {
            if (!msg.success) return 'error-card';
            const t = msg.action_type;
            if (t === 'course')   return 'success-course';
            if (t === 'activity') return 'success-activity';
            if (t === 'student')  return 'success-student';
            return 'success-default';
        },

        formatDestructive(a) {
            const hints = a.args?.course_name_hint || a.args?.course_id || '';
            return `${a.function}(${hints})`;
        },

        scrollToBottom() {
            const log = document.getElementById('ai-log');
            if (log) log.scrollTop = log.scrollHeight;
        },

        reload() {
            window.dispatchEvent(new CustomEvent('ai-canvas-refresh'));
            setTimeout(() => {
                if (this.showReload) window.location.reload();
            }, 150);
        },

        renderMarkdown(text) {
            if (!text) return '';
            return text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        },
    };
}
</script>