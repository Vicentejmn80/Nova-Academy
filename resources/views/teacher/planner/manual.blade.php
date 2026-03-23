<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planificador Visual · Nova Academy</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        input[type="date"]::-webkit-calendar-picker-indicator {
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .gradient-border {
            position: relative;
            border-radius: 2rem;
            background: linear-gradient(60deg, #f79533, #f37055, #ef4e7b, #a166ab, #5073b8, #1098ad, #07b39b, #6fba82);
            background-size: 300% 300%;
            animation: animated-gradient 12s ease alternate infinite;
        }
        
        @keyframes animated-gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .gradient-border::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 2rem;
            padding: 3px;
            background: inherit;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px -15px rgba(124, 58, 237, 0.4);
        }
    </style>
</head>
<body class="bg-[#0B1120] text-slate-100 min-h-screen font-sans relative overflow-x-hidden">

    <!-- Fondo animado estilo screenshot -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-violet-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-fuchsia-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/10 rounded-full blur-3xl"></div>
        
        <!-- Patrón de grid sutil -->
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.1) 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>

<div class="max-w-7xl mx-auto p-6 relative z-10" x-data="manualPlanner()">
    <!-- Header espectacular -->
    <header class="relative mb-12">
        <!-- Tarjetas flotantes decorativas -->
        <div class="absolute -top-6 -left-6 w-24 h-24 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-2xl rotate-12 opacity-30 blur-sm float-animation"></div>
        <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl -rotate-12 opacity-30 blur-sm float-animation delay-1000"></div>
        
        <div class="relative bg-gradient-to-r from-[#1E1B4B] via-[#312E81] to-[#5B21B6] rounded-[2.5rem] p-[2px] shadow-2xl">
            <div class="relative bg-[#0F0B1F] rounded-[2.5rem] p-8 overflow-hidden">
                <!-- Efectos de luz -->
                <div class="absolute top-0 -left-20 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 -right-20 w-60 h-60 bg-fuchsia-500/20 rounded-full blur-3xl"></div>
                
                <div class="flex flex-col md:flex-row justify-between items-center gap-6 relative">
                    <div class="text-center md:text-left">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-violet-400 to-fuchsia-400 rounded-2xl flex items-center justify-center shadow-lg shadow-violet-500/30">
                                <i class="ph-fill ph-notebook text-4xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-4xl font-black bg-gradient-to-r from-white via-violet-200 to-fuchsia-200 bg-clip-text text-transparent">
                                    Planificación Manual
                                </h1>
                                <p class="text-slate-400 mt-1 font-medium flex items-center gap-2">
                                    <i class="ph-fill ph-star text-amber-400 text-sm"></i>
                                    Gestiona, visualiza y mejora tus clases con estilo
                                    <i class="ph-fill ph-star text-amber-400 text-sm"></i>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Stats rápidos estilo screenshot -->
                        <div class="flex gap-6 mt-6">
                            <div class="flex items-center gap-3 bg-white/5 rounded-2xl px-4 py-2 backdrop-blur-sm">
                                <div class="w-8 h-8 rounded-xl bg-violet-500/20 flex items-center justify-center">
                                    <i class="ph-fill ph-book-open text-violet-400"></i>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500">Sesiones</span>
                                    <span class="block text-lg font-bold text-white" x-text="sessions.length"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-white/5 rounded-2xl px-4 py-2 backdrop-blur-sm">
                                <div class="w-8 h-8 rounded-xl bg-fuchsia-500/20 flex items-center justify-center">
                                    <i class="ph-fill ph-calendar-check text-fuchsia-400"></i>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500">Próxima</span>
                                    <span class="block text-sm font-bold text-white" x-text="sessions[0]?.date ? formatDisplayDate(sessions[0].date) : 'Sin fecha'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <a href="{{ route('teacher.hub') }}" class="group relative px-6 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all overflow-hidden">
                            <span class="relative z-10 text-white font-bold flex items-center gap-2">
                                <i class="ph-bold ph-arrow-left"></i>
                                Volver al Hub
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-violet-600 to-fuchsia-600 opacity-0 group-hover:opacity-20 transition-opacity"></div>
                        </a>
                        <button @click="addSession()" class="group relative px-8 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 font-black text-white shadow-lg shadow-violet-600/30 hover:shadow-violet-600/50 transition-all hover:scale-105 active:scale-95 overflow-hidden">
                            <span class="relative z-10 flex items-center gap-2">
                                <i class="ph-bold ph-plus-circle"></i>
                                Nueva Sesión
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-fuchsia-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Frase del día (como en el screenshot) -->
        <div class="mt-6 flex justify-center">
            <div class="inline-flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-full px-6 py-3 border border-white/10">
                <i class="ph-fill ph-quotes text-amber-400 text-xl"></i>
                <span class="text-slate-300 italic">La mejor manera de predecir el futuro es crearlo. — Peter Drucker</span>
                <i class="ph-fill ph-quotes text-amber-400 text-xl rotate-180"></i>
            </div>
        </div>
    </header>

    <!-- Grid de sesiones -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <template x-for="(session, index) in sessions" :key="session.id || index">
            <div class="group/session relative" x-data="{ showOptions: false }">
                <!-- Número de sesión flotante -->
                <div class="absolute -top-3 -left-3 z-20">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-violet-500/50" x-text="index + 1"></div>
                </div>
                
                <!-- Botón eliminar con efecto -->
                <button @click="removeSession(index)" x-show="sessions.length > 1" 
                        @mouseenter="showOptions = true" @mouseleave="showOptions = false"
                        class="absolute -top-3 -right-3 z-20 w-8 h-8 rounded-full bg-red-500/90 backdrop-blur-sm flex items-center justify-center text-white opacity-0 group-hover/session:opacity-100 transition-all hover:scale-110 hover:bg-red-600 shadow-lg">
                    <i class="ph-bold ph-trash text-sm"></i>
                </button>

                <!-- Tarjeta principal -->
                <div class="relative bg-[#1A1A2E] rounded-[2rem] overflow-hidden border border-white/10 card-hover h-full">
                    <!-- Efecto de brillo en hover -->
                    <div class="absolute inset-0 opacity-0 group-hover/session:opacity-100 transition-opacity bg-gradient-to-r from-violet-600/10 via-transparent to-fuchsia-600/10"></div>
                    
                    <!-- Barra superior con badge -->
                    <div class="relative bg-gradient-to-r from-violet-600/20 to-fuchsia-600/20 p-4 border-b border-white/5">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                                    <i class="ph-fill ph-hand-waving text-violet-400"></i>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-400">
                                    Plan Manual · Sesión <span x-text="index + 1"></span>
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                                <span class="text-xs text-slate-500">Activa</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="p-8 space-y-6">
                        <!-- Selector de fecha innovador -->
                        <div class="relative group/date">
                            <label class="text-[10px] uppercase font-black tracking-wider text-slate-500 mb-3 block ml-1">
                                <i class="ph-fill ph-calendar text-amber-400 mr-1"></i>
                                Fecha de la clase
                            </label>
                            <div class="relative">
                                <input type="date" x-model="session.date" @change="updateDayName(index)"
                                       class="absolute inset-0 opacity-0 z-20 cursor-pointer">
                                <div class="relative bg-gradient-to-br from-[#0F0B1F] to-[#1A1A2E] rounded-2xl border-2 border-white/5 group-hover/date:border-violet-500/30 transition-all p-5 overflow-hidden">
                                    <!-- Efecto de fondo -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-violet-600/5 to-fuchsia-600/5 opacity-0 group-hover/date:opacity-100 transition-opacity"></div>
                                    
                                    <div class="relative flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-500/20 to-fuchsia-500/20 flex items-center justify-center">
                                                <i class="ph-fill ph-calendar-blank text-2xl text-violet-400"></i>
                                            </div>
                                            <div>
                                                <span class="block text-xl font-bold text-white" x-text="formatDisplayDate(session.date)"></span>
                                                <span class="block text-sm text-slate-400 capitalize flex items-center gap-1">
                                                    <i class="ph-fill ph-clock"></i>
                                                    <span x-text="session.day"></span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-slate-500">Cambiar</span>
                                            <i class="ph-bold ph-caret-down text-slate-500 group-hover/date:text-violet-400 transition-colors"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos de texto con estilo -->
                        <div class="space-y-4">
                            <div class="group/field">
                                <label class="text-[10px] uppercase font-black tracking-wider text-slate-500 mb-2 block ml-1 flex items-center gap-1">
                                    <i class="ph-fill ph-play-circle text-emerald-400"></i>
                                    Inicio de sesión
                                </label>
                                <div class="relative">
                                    <textarea x-model="session.inicio" rows="2" 
                                        class="w-full bg-[#0F0B1F] border-2 border-white/5 rounded-xl px-5 py-4 text-slate-300 focus:border-violet-500 focus:bg-[#0F0B1F] focus:ring-2 focus:ring-violet-500/20 outline-none transition-all resize-none placeholder-slate-600" 
                                        placeholder="Escribe el inicio..."></textarea>
                                    <div class="absolute bottom-2 right-2 text-xs text-slate-600">
                                        <span x-text="session.inicio.length || 0"></span>/500
                                    </div>
                                </div>
                            </div>
                            
                            <div class="group/field">
                                <label class="text-[10px] uppercase font-black tracking-wider text-slate-500 mb-2 block ml-1 flex items-center gap-1">
                                    <i class="ph-fill ph-chart-line-up text-blue-400"></i>
                                    Desarrollo pedagógico
                                </label>
                                <div class="relative">
                                    <textarea x-model="session.desarrollo" rows="4" 
                                        class="w-full bg-[#0F0B1F] border-2 border-white/5 rounded-xl px-5 py-4 text-slate-300 focus:border-fuchsia-500 focus:bg-[#0F0B1F] focus:ring-2 focus:ring-fuchsia-500/20 outline-none transition-all resize-none placeholder-slate-600" 
                                        placeholder="Describe las actividades..."></textarea>
                                    <div class="absolute bottom-2 right-2 text-xs text-slate-600">
                                        <span x-text="session.desarrollo.length || 0"></span>/1000
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mini vista previa -->
                        <div class="relative mt-6">
                            <div class="absolute inset-0 bg-gradient-to-r from-violet-600/10 to-fuchsia-600/10 rounded-xl blur-xl opacity-50"></div>
                            <div class="relative bg-white/5 rounded-xl p-4 border border-white/5">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="ph-fill ph-eye text-amber-400 text-sm"></i>
                                    <span class="text-xs font-bold text-slate-400">VISTA PREVIA RÁPIDA</span>
                                </div>
                                <div class="text-xs text-slate-500 line-clamp-2">
                                    <span x-text="session.inicio ? session.inicio.substring(0, 50) + '...' : 'Sin inicio definido'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer con acciones -->
                    <div class="px-8 pb-8 mt-auto">
                        <div class="flex gap-2">
                            <button class="flex-1 py-3 rounded-xl bg-gradient-to-r from-violet-600/10 to-fuchsia-600/10 hover:from-violet-600 hover:to-fuchsia-600 text-slate-400 hover:text-white font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2 group/btn">
                                <i class="ph ph-eye group-hover/btn:animate-pulse"></i>
                                Vista Previa
                            </button>
                            <button class="w-12 h-12 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center text-slate-400 hover:text-white transition-all">
                                <i class="ph-bold ph-dots-three-outline"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Botón guardar flotante mejorado -->
    <div class="fixed bottom-10 right-10 z-50">
        <button @click="save()" :disabled="isLoading"
                class="group relative px-8 py-5 bg-gradient-to-r from-violet-600 to-fuchsia-600 rounded-2xl font-black text-white shadow-2xl shadow-violet-600/30 hover:shadow-fuchsia-600/40 transition-all duration-300 hover:scale-110 active:scale-95 overflow-hidden">
            <!-- Efecto de brillo -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            
            <span class="relative z-10 flex items-center gap-3">
                <template x-if="!isLoading">
                    <>
                        <i class="ph-bold ph-floppy-disk text-2xl"></i>
                        <span>Guardar Planificación</span>
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                    </>
                </template>
                <template x-if="isLoading">
                    <>
                        <i class="ph-bold ph-circle-notch animate-spin text-2xl"></i>
                        <span>Guardando...</span>
                    </>
                </template>
            </span>
        </button>
    </div>
    
    <!-- Mensaje cuando no hay sesiones -->
    <div x-show="sessions.length === 0" x-cloak class="text-center py-20">
        <div class="inline-flex flex-col items-center gap-6">
            <div class="w-32 h-32 rounded-full bg-gradient-to-br from-violet-600/20 to-fuchsia-600/20 flex items-center justify-center">
                <i class="ph-fill ph-notebook text-6xl text-slate-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-slate-400">No hay sesiones planificadas</h3>
            <button @click="addSession()" class="px-6 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-bold">
                Crear primera sesión
            </button>
        </div>
    </div>
</div>

<script>
function manualPlanner() {
    return {
        sessions: @json($planning->sessions ?? []),
        isLoading: false,
        
        init() {
            if (this.sessions.length === 0) this.addSession();
            else this.sessions.forEach((s, i) => this.updateDayName(i));
        },

        addSession() {
            const lastDate = this.sessions.length > 0 
                ? new Date(this.sessions[this.sessions.length - 1].date + 'T00:00:00')
                : new Date();
            if(this.sessions.length > 0) lastDate.setDate(lastDate.getDate() + 1);

            this.sessions.push({
                id: Date.now(),
                date: lastDate.toISOString().split('T')[0],
                day: this.formatDayName(lastDate),
                inicio: '', desarrollo: '', cierre: ''
            });
        },

        removeSession(index) { 
            this.sessions.splice(index, 1); 
            Swal.fire({
                title: 'Sesión eliminada',
                text: 'La sesión ha sido removida',
                icon: 'info',
                timer: 1500,
                showConfirmButton: false,
                background: '#1A1A2E',
                color: '#fff'
            });
        },
        
        formatDayName(date) { return date.toLocaleDateString('es-ES', { weekday: 'long' }); },
        
        formatDisplayDate(dateStr) {
            if(!dateStr) return 'Seleccionar fecha';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },
        
        updateDayName(index) {
            if(this.sessions[index].date) {
                const d = new Date(this.sessions[index].date + 'T00:00:00');
                this.sessions[index].day = this.formatDayName(d);
            }
        },

        async save() {
            if(this.isLoading) return;
            this.isLoading = true;
            try {
                const response = await fetch("{{ route('teacher.planner.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ sessions: this.sessions }),
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire({ 
                        title: '¡Planificación Guardada!', 
                        text: "Tus sesiones han sido sincronizadas correctamente.", 
                        icon: 'success', 
                        background: '#1A1A2E',
                        color: '#fff',
                        confirmButtonColor: '#7c3aed',
                        timer: 2000,
                        timerProgressBar: true
                    });
                    setTimeout(() => window.location.href = data.redirect, 1500);
                } else throw new Error(data.error);
            } catch (error) {
                Swal.fire({ 
                    title: 'Error', 
                    text: error.message, 
                    icon: 'error',
                    background: '#1A1A2E',
                    color: '#fff',
                    confirmButtonColor: '#7c3aed'
                });
            } finally { this.isLoading = false; }
        }
    };
}
</script>

<style>
    [x-cloak] { display: none !important; }
    
    /* Scrollbar personalizada */
    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    
    ::-webkit-scrollbar-track {
        background: #0F0B1F;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #7c3aed, #c026d3);
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #8b5cf6, #d946ef);
    }
    
    /* Animaciones */
    @keyframes pulse-glow {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }
    
    .delay-1000 {
        animation-delay: 1s;
    }
    
    /* Línea de tiempo decorativa */
    .timeline-dot {
        width: 4px;
        height: 4px;
        background: linear-gradient(180deg, #7c3aed, #c026d3);
        border-radius: 50%;
    }
</style>
</body>
</html>