<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Actividades · Nova Academy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --grad-primary: linear-gradient(135deg, #7c3aed 0%, #c026d3 100%);
            --grad-dark:    linear-gradient(160deg, #1e0f3c 0%, #2d1569 60%, #4a1072 100%);
        }
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', system-ui, sans-serif; background:#f5f3ff; }
        .weight-bar { transition: width .4s ease; }
        .btn-gradient {
            background: var(--grad-primary); color:#fff; font-weight:700;
            border-radius:.875rem; border:none; cursor:pointer;
            transition: opacity .15s, transform .15s;
        }
        .btn-gradient:hover { opacity:.9; transform:translateY(-1px); }
        /* Type badges */
        .badge-clase { background:linear-gradient(135deg,#ede9fe,#fce7f3); color:#6d28d9; }
        .badge-actividad { background:linear-gradient(135deg,#fce7f3,#ede9fe); color:#a21caf; }
        .badge-tarea { background:linear-gradient(135deg,#fdf4ff,#fecdd3); color:#c026d3; }
        .type-tab { border:none; border-radius:.875rem; font-size:.8rem; font-weight:600; padding:.45rem 1rem; cursor:pointer; transition:all .15s; }
        .type-tab.active-all { background:linear-gradient(135deg,#ede9fe,#fce7f3); color:#6d28d9; }
        .type-tab.active-clase { background:linear-gradient(135deg,#7c3aed,#a21caf); color:#fff; }
        .type-tab.active-actividad { background:linear-gradient(135deg,#c026d3,#db2777); color:#fff; }
        .type-tab:not([class*="active"]) { background:#fff; color:#6b21a8; border:1px solid #ddd6fe; }
    </style>
</head>
<body class="min-h-screen">

{{-- ── Top nav bar ─────────────────────────────────────── --}}
<nav class="sticky top-0 z-30" style="background:var(--grad-dark);">
    <div class="max-w-6xl mx-auto px-4 flex items-center gap-1 h-14">
        <a href="{{ route('teacher.hub') }}"
           class="text-purple-300 hover:text-white mr-3 transition text-sm flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-xs"></i> Hub
        </a>
        <span class="text-purple-700 mx-1">|</span>
        <a href="{{ route('teacher.courses.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium text-purple-300 hover:text-white hover:bg-white/10 transition">
            <i class="fa-solid fa-chalkboard mr-1.5"></i> Cursos
        </a>
        <a href="{{ route('teacher.activities.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-white/15">
            <i class="fa-solid fa-clipboard-list mr-1.5"></i> Actividades
        </a>
        <a href="{{ route('teacher.grades.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium text-purple-300 hover:text-white hover:bg-white/10 transition">
            <i class="fa-solid fa-star-half-stroke mr-1.5"></i> Notas
        </a>
        <div class="ml-auto">
            @include('components.ai-assistant-bubble')
        </div>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 py-8" x-data="activitiesPage()" x-init="init()">

    {{-- ── Header ─────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black" style="background:var(--grad-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                Clases &amp; Actividades
            </h1>
            <p class="text-sm text-purple-500 mt-1">
                Lecciones teóricas y evaluaciones de todos tus cursos.
            </p>
        </div>
        <button @click="openCreate = true"
                class="btn-gradient inline-flex items-center gap-2 px-5 py-3 shadow-lg shrink-0"
                style="box-shadow:0 4px 16px rgba(124,58,237,.3);">
            <i class="fa-solid fa-plus"></i> Nueva
        </button>
    </div>

    {{-- ── Type filter tabs ───────────────────────────────── --}}
    <div class="flex gap-2 mb-5">
        <button class="type-tab"
                :class="filterType === 'all' ? 'active-all' : ''"
                @click="filterType = 'all'">
            ✦ Todas
            <span class="ml-1.5 text-[10px] font-bold opacity-70"
                  x-text="countByType('all')"></span>
        </button>
        <button class="type-tab"
                :class="filterType === 'clase' ? 'active-clase' : ''"
                @click="filterType = 'clase'">
            🏫 Clases
            <span class="ml-1.5 text-[10px] font-bold opacity-70"
                  x-text="countByType('clase')"></span>
        </button>
        <button class="type-tab"
                :class="filterType === 'tarea' ? 'active-actividad' : ''"
                @click="filterType = 'tarea'">
            🧩 Tareas
            <span class="ml-1.5 text-[10px] font-bold opacity-70"
                  x-text="countByType('tarea')"></span>
        </button>
        <button class="type-tab"
                :class="filterType === 'evaluacion' ? 'active-actividad' : ''"
                @click="filterType = 'evaluacion'">
            📊 Evaluaciones
            <span class="ml-1.5 text-[10px] font-bold opacity-70"
                  x-text="countByType('evaluacion')"></span>
        </button>
    </div>

    {{-- ── Flash ──────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200
                    text-emerald-700 rounded-2xl px-5 py-4 text-sm font-medium">
            <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ── Course filter pills ──────────────────────────────── --}}
    @if($courses->isNotEmpty())
    <div class="flex flex-wrap gap-2 mb-6">
        <button
            @click="filterCourse = null"
            :class="filterCourse === null ? 'bg-violet-100 text-violet-700 font-semibold border-violet-200' : 'bg-white text-purple-500 hover:bg-purple-50 border-purple-100'"
            class="px-3 py-1.5 rounded-full text-xs border transition"
        >
            Todos los cursos
        </button>
        @foreach($courses as $course)
        <button
            @click="filterCourse = {{ $course->id }}"
            :class="filterCourse === {{ $course->id }} ? 'bg-violet-100 text-violet-700 font-semibold border-violet-200' : 'bg-white text-purple-500 hover:bg-purple-50 border-purple-100'"
            class="px-3 py-1.5 rounded-full text-xs border transition"
        >
            {{ $course->subject_name }} · {{ $course->grade }}
            @if($course->section) / {{ $course->section }}@endif
        </button>
        @endforeach
    </div>
    @endif

    {{-- ── Empty state ────────────────────────────────────── --}}
    @if($activities->isEmpty())
        <div class="text-center py-20">
            <div class="w-20 h-20 bg-violet-50 rounded-3xl flex items-center justify-center mx-auto mb-5">
                <i class="fa-solid fa-clipboard-list text-3xl text-violet-300"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-700 mb-2">Sin actividades aún</h3>
            <p class="text-slate-400 text-sm mb-6">
                @if($courses->isEmpty())
                    Primero <a href="{{ route('teacher.courses.index') }}" class="text-violet-600 underline">crea un curso</a>
                    para poder asignarle actividades.
                @else
                    Crea tu primera actividad y empieza a registrar notas.
                @endif
            </p>
            @if($courses->isNotEmpty())
            <button @click="openCreate = true"
                    class="inline-flex items-center gap-2 bg-violet-600 text-white
                           font-semibold px-5 py-3 rounded-2xl hover:bg-violet-700 transition">
                <i class="fa-solid fa-plus"></i> Crear actividad
            </button>
            @endif
        </div>
    @else

    {{-- ── Activities table ───────────────────────────────── --}}
    <div class="bg-white rounded-3xl border border-violet-100 overflow-hidden shadow-sm">

        {{-- Table header --}}
        <div class="grid grid-cols-12 gap-3 px-6 py-3 border-b border-violet-100
                    text-xs font-bold uppercase tracking-wider text-purple-400"
             style="background:linear-gradient(135deg,#f5f3ff,#fdf4ff)">
            <div class="col-span-4">Clase / Actividad</div>
            <div class="col-span-2">Curso</div>
            <div class="col-span-1 text-center">Máx.</div>
            <div class="col-span-2 text-center">Peso</div>
            <div class="col-span-2">Entrega</div>
            <div class="col-span-1 text-center">Acción</div>
        </div>

        {{-- Rows --}}
        <template x-for="activity in filteredActivities()" :key="activity.id">
            <div x-data="{ expanded: false }"
                class="grid grid-cols-12 gap-3 items-center px-6 py-4 border-b border-purple-50 last:border-0 hover:bg-purple-50/40 transition"
            >
                    <div class="col-span-4 flex items-start gap-3">
                        <button @click="expanded = !expanded"
                                class="w-8 h-8 rounded-xl border border-purple-200 bg-white flex items-center justify-center text-purple-600 hover:border-purple-400 transition transform duration-300"
                                :class="expanded ? 'rotate-180' : ''">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="flex-1">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full"
                                      :class="activity.type === 'clase'
                                              ? 'badge-clase'
                                              : ((activity.is_homework || activity.type === 'tarea') ? 'badge-tarea' : 'badge-actividad')">
                                    <span x-text="activity.type === 'clase'
                                                    ? '🏫 Clase'
                                                    : ((activity.is_homework || activity.type === 'tarea') ? '🧩 Tarea' : '📊 Evaluación')"></span>
                                </span>
                            </div>
                            <p class="font-semibold text-slate-800 text-sm truncate" x-text="activity.title"></p>
                        </div>
                    </div>

                <div class="col-span-2">
                    <span class="inline-block bg-violet-50 text-violet-600 text-xs font-semibold px-2.5 py-1 rounded-full truncate"
                          x-text="activity.course_name"></span>
                </div>

                <div class="col-span-1 text-center">
                    <span class="text-sm font-bold text-slate-700" x-text="activity.max_score"></span>
                    <span class="text-xs text-slate-400"> pts</span>
                </div>

                <div class="col-span-2">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-purple-100 rounded-full h-1.5 overflow-hidden">
                            <div class="weight-bar h-1.5 rounded-full"
                                 :style="`width:${Math.min(activity.weight_percentage,100)}%;background:${activity.type === 'clase' ? '#7c3aed' : '#c026d3'}`">
                            </div>
                        </div>
                        <span class="text-xs font-bold text-purple-600 w-9 text-right"
                              x-text="Math.round(activity.weight_percentage ?? 0) + '%'"></span>
                    </div>
                </div>

                <div class="col-span-2">
                    <span class="text-xs text-slate-600 font-medium" x-text="formatDate(activity.due_date)"></span>
                </div>

                <div class="col-span-1 flex items-center justify-center gap-1.5">
                    <template x-if="activity.type !== 'clase'">
                        <a :href="`/teacher/grades/${activity.id}`"
                           title="Cargar notas con IA"
                           class="btn-gradient inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 whitespace-nowrap">
                            <i class="fa-solid fa-microphone-lines"></i>
                            <span class="hidden sm:inline">Cargar Notas</span>
                        </a>
                        <button
                            type="button"
                            @click="editActivityWithAI(activity.id, activity.title)"
                            title="Editar con IA"
                            class="w-7 h-7 rounded-lg bg-violet-50 hover:bg-violet-100
                                   text-violet-600 flex items-center justify-center transition">
                            <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                        </button>
                    </template>
                    <template x-if="activity.type === 'clase'">
                        <span class="text-[10px] text-purple-300 italic px-2">Teórica</span>
                    </template>
                    <button
                        @click="deleteActivity(activity.id)"
                        title="Eliminar"
                        class="w-7 h-7 rounded-lg bg-fuchsia-50 hover:bg-fuchsia-100
                               text-fuchsia-500 flex items-center justify-center transition">
                        <i class="fa-solid fa-trash-alt text-xs"></i>
                    </button>
                </div>

                <div x-show="expanded" x-transition class="col-span-12 px-6 pb-4 border-t border-purple-50 bg-purple-50/30">
                    <div class="grid gap-4 grid-cols-1" :class="activity.tareas?.length ? 'md:grid-cols-2' : 'md:grid-cols-1'">
                        <div class="bg-slate-50/80 rounded-2xl p-4 text-sm text-slate-600 border border-slate-100 shadow-sm">
                            <p class="font-semibold text-slate-700 mb-1">Descripción</p>
                            <p x-text="activity.description || 'Sin descripción adicional.'"></p>
                        </div>
                        <div x-show="activity.nee_adaptation" class="bg-emerald-50/80 rounded-2xl p-4 text-sm text-emerald-700 border border-emerald-100 shadow-sm">
                            <p class="font-semibold text-emerald-800 mb-1">
                                <i class="fa-solid fa-book-open-reader mr-1"></i>
                                <span x-text="`📘 Guía de Adaptación para ${activity.nee_type || 'NEE'}`"></span>
                            </p>
                            <p x-text="activity.nee_adaptation"></p>
                        </div>
                        <div>
                            <template x-if="activity.tareas?.length">
                                <div class="space-y-3">
                                    <template x-for="tarea in activity.tareas" :key="tarea.id">
                                        <div class="bg-white/90 border border-violet-100 rounded-2xl px-4 py-3 shadow-sm">
                                            <div class="flex items-center justify-between gap-4">
                                                <div>
                                                    <p class="font-semibold text-sm" x-text="tarea.titulo"></p>
                                                    <p class="text-[11px] text-slate-500" x-text="tarea.descripcion || 'Sin descripción.'"></p>
                                                </div>
                                                <div class="text-right text-xs text-slate-400">
                                                    <p><strong class="text-slate-600" x-text="tarea.puntos + ' pts'"></strong></p>
                                                    <p x-text="tarea.fecha_entrega ? 'Entrega ' + formatDate(tarea.fecha_entrega) : 'Sin fecha'"></p>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex items-center justify-between text-[11px]">
                                                <span>Nota: <strong class="text-slate-700" x-text="tarea.calificacion !== null ? Number(tarea.calificacion).toFixed(2) : 'Pendiente'"></strong></span>
                                                <button class="text-violet-600 font-semibold" @click="openGradeModal(tarea)">
                                                    Calificar
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <div x-show="!activity.tareas?.length" class="text-xs text-slate-500 mt-1">
                                <p>No hay detalles adicionales para esta actividad.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Pagination --}}
        @if($activities->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- CREATE ACTIVITY MODAL                                  --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div x-show="openCreate" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(15,23,42,.55);backdrop-filter:blur(4px);"
         @keydown.escape.window="openCreate = false">
        <div @click.outside="openCreate = false"
             class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">

            <div class="px-6 py-4 text-white" style="background:linear-gradient(135deg,#6d28d9,#c026d3)">
                <h3 class="font-bold">Nueva clase / actividad</h3>
                <p class="text-xs text-purple-200 mt-0.5">Define el tipo, nombre, peso y fecha de entrega</p>
            </div>

            <form method="POST" action="{{ route('teacher.activities.store') }}"
                  class="px-6 py-5 space-y-4" x-data="activityForm()">
                @csrf

                {{-- Course selector --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Curso <span class="text-red-400">*</span>
                    </label>
                    @if($courses->isEmpty())
                        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-xs text-amber-700">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                            No tienes cursos. <a href="{{ route('teacher.courses.index') }}"
                            class="underline font-semibold">Crea uno primero</a>.
                        </div>
                    @else
                        <select name="course_id" required
                                class="w-full border border-slate-200 rounded-xl py-2.5 px-4 text-sm
                                       focus:outline-none focus:border-violet-400 focus:ring-2
                                       focus:ring-violet-100 transition bg-white">
                            <option value="">Selecciona un curso…</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}"
                                    {{ old('course_id', request()->query('course')) == $course->id ? 'selected' : '' }}>
                                    {{ $course->subject_name }} · {{ $course->grade }}
                                    {{ $course->section ? '/ '.$course->section : '' }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- Type selector --}}
                <div>
                    <label class="block text-xs font-semibold text-purple-600 mb-2">Tipo</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 border rounded-xl px-3 py-2 cursor-pointer transition"
                               :class="createType === 'clase' ? 'border-violet-400 bg-violet-50' : 'border-slate-200 hover:border-violet-200'">
                            <input type="radio" name="type" value="clase" x-model="createType" class="accent-violet-600">
                            <span class="text-sm font-semibold text-violet-700">🏫 Clase</span>
                            <span class="text-[10px] text-slate-400 ml-auto">Teórica</span>
                        </label>
                        <label class="flex items-center gap-2 border rounded-xl px-3 py-2 cursor-pointer transition"
                               :class="createType === 'actividad' ? 'border-fuchsia-400 bg-fuchsia-50' : 'border-slate-200 hover:border-fuchsia-200'">
                            <input type="radio" name="type" value="actividad" x-model="createType" class="accent-fuchsia-600">
                            <span class="text-sm font-semibold text-fuchsia-700">📊 Actividad</span>
                            <span class="text-[10px] text-slate-400 ml-auto">Evaluación</span>
                        </label>
                    </div>
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Nombre <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="title" required
                           x-model="title"
                           :placeholder="createType === 'clase' ? 'Ej: Introducción a la célula, Repaso Unidad 2…' : 'Ej: Parcial 1, Tarea 3, Proyecto grupal…'"
                           value="{{ old('title') }}"
                           class="w-full border border-slate-200 rounded-xl py-2.5 px-4 text-sm
                                  focus:outline-none focus:border-violet-400 focus:ring-2
                                  focus:ring-violet-100 transition">
                </div>

                {{-- Description --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Descripción</label>
                        <button type="button"
                                @click="generateDescription()"
                                :disabled="loadingDescription || !title.trim()"
                                class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-lg
                                       bg-violet-50 text-violet-700 hover:bg-violet-100 disabled:opacity-40 transition">
                            <i class="fa-solid" :class="loadingDescription ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles'"></i>
                            Varita Mágica
                        </button>
                    </div>
                    <textarea name="description" rows="3" x-model="description"
                           placeholder="Opcional: temas evaluados, instrucciones…"
                           class="w-full border border-slate-200 rounded-xl py-2.5 px-4 text-sm
                                  focus:outline-none focus:border-violet-400 transition"></textarea>
                    <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                        <input type="checkbox" name="is_homework" id="is_homework" class="h-4 w-4 accent-cyan-500">
                        <label for="is_homework" class="font-medium">Marcar como tarea (HomeWork)</label>
                    </div>
                </div>

            {{-- NEE adaptation selector --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Adaptación NEE (opcional)
                </label>
                <select name="nee_type"
                        class="w-full border border-slate-200 rounded-xl py-2.5 px-4 text-sm
                               focus:outline-none focus:border-emerald-400 focus:ring-2
                               focus:ring-emerald-100 transition bg-white">
                    <option value="">Sin adaptación</option>
                    <option value="TDAH" {{ old('nee_type') === 'TDAH' ? 'selected' : '' }}>TDAH</option>
                    <option value="TEA/Autismo" {{ old('nee_type') === 'TEA/Autismo' ? 'selected' : '' }}>TEA/Autismo</option>
                    <option value="Dislexia" {{ old('nee_type') === 'Dislexia' ? 'selected' : '' }}>Dislexia</option>
                    <option value="Discalculia" {{ old('nee_type') === 'Discalculia' ? 'selected' : '' }}>Discalculia</option>
                    <option value="Otro" {{ old('nee_type') === 'Otro' ? 'selected' : '' }}>Otro</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">
                    Nova generará una guía pedagógica automática para el tipo seleccionado.
                </p>
            </div>

                {{-- Max score + weight --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Puntaje máximo <span class="text-red-400">*</span>
                        </label>
                        <input type="number" name="max_score" min="1" max="100" required
                               value="{{ old('max_score', 20) }}"
                               class="w-full border border-slate-200 rounded-xl py-2.5 px-4 text-sm
                                      focus:outline-none focus:border-violet-400 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Peso en nota final (%)
                            <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="weight_percentage"
                                   min="0" max="100" step="0.5" required
                                   x-model="weight"
                                   value="{{ old('weight_percentage', 20) }}"
                                   class="w-full border border-slate-200 rounded-xl py-2.5 pl-4 pr-8 text-sm
                                          focus:outline-none focus:border-violet-400 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
                        </div>
                        {{-- Mini weight bar --}}
                        <div class="mt-2 bg-slate-100 rounded-full h-1 overflow-hidden">
                            <div class="bg-violet-500 h-1 rounded-full transition-all duration-300"
                                 :style="`width: ${Math.min(weight, 100)}%`"></div>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">
                            <span x-text="weight"></span>% de la nota final del período
                        </p>
                    </div>
                </div>

                {{-- Due date --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Fecha de entrega</label>
                    <input type="date" name="due_date"
                           value="{{ old('due_date') }}"
                           class="w-full border border-slate-200 rounded-xl py-2.5 px-4 text-sm
                                  focus:outline-none focus:border-violet-400 transition">
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" @click="openCreate = false"
                            class="text-sm text-slate-400 hover:text-slate-600">Cancelar</button>
                    <button type="submit"
                            :disabled="{{ $courses->isEmpty() ? 'true' : 'false' }}"
                            class="btn-gradient inline-flex items-center gap-2
                                   disabled:opacity-40 disabled:cursor-not-allowed
                                   font-semibold px-5 py-2.5">
                        <i class="fa-solid fa-plus"></i>
                        <span x-text="createType === 'clase' ? 'Crear Clase' : 'Crear Actividad'">Crear</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Grade task modal --}}
    <div x-show="gradeModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(15,23,42,.55);backdrop-filter:blur(4px);"
         @keydown.escape.window="gradeModalOpen = false">
        <div @click.outside="gradeModalOpen = false"
             class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 text-white" style="background:linear-gradient(135deg,#6d28d9,#c026d3)">
                <h3 class="font-bold">Calificar tarea</h3>
                <p class="text-xs text-purple-200 mt-0.5" x-text="selectedTask?.titulo || 'Tarea seleccionada'"></p>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Calificación</label>
                    <input type="number" min="0" step="0.01"
                           x-model.number="gradeForm.calificacion"
                           class="w-full border border-slate-200 rounded-xl py-2.5 px-4 text-sm
                                  focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Feedback</label>
                    <textarea rows="3" x-model="gradeForm.feedback"
                              class="w-full border border-slate-200 rounded-xl py-2.5 px-4 text-sm
                                     focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 transition"
                              placeholder="Comentario pedagógico opcional"></textarea>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" @click="gradeModalOpen = false"
                            class="text-sm text-slate-400 hover:text-slate-600">Cancelar</button>
                    <button type="button" @click="saveTaskGrade()"
                            :disabled="gradeSaving || !selectedTask?.id"
                            class="btn-gradient inline-flex items-center gap-2 font-semibold px-5 py-2.5
                                   disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fa-solid" :class="gradeSaving ? 'fa-spinner fa-spin' : 'fa-floppy-disk'"></i>
                        Guardar nota
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@php
    $activityPayload = collect($activities->items())->map(function ($a) {
        $course = $a->course;
        $tareas = $a->tareas->map(function ($t) {
            return [
                'id' => $t->id,
                'titulo' => $t->titulo,
                'descripcion' => $t->descripcion,
                'fecha_entrega' => optional($t->fecha_entrega)->format('Y-m-d'),
                'puntos' => $t->puntos,
                'calificacion' => $t->calificacion,
                'feedback' => $t->feedback,
            ];
        })->values()->toArray();
        return [
            'id' => $a->id,
            'title' => $a->title,
            'description' => $a->description,
            'type' => $a->type ?? 'actividad',
            'max_score' => $a->max_score,
            'weight_percentage' => $a->weight_percentage,
            'due_date' => optional($a->due_date)->format('Y-m-d'),
            'course_id' => $a->course_id,
            'course_name' => $course ? ($course->subject_name . ($course->grade ? ' · ' . $course->grade : '')) : '—',
            'is_homework' => (bool) $a->is_homework,
            'nee_type' => $a->nee_type,
            'nee_adaptation' => $a->nee_adaptation,
            'tareas_count' => $a->tareas_count ?? count($tareas),
            'tareas' => $tareas,
        ];
    })->values();
@endphp

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function activitiesPage() {
    return {
        openCreate:   {{ $errors->any() ? 'true' : 'false' }},
        filterCourse: null,
        filterType:   'all',
        gradeModalOpen: false,
        gradeSaving: false,
        selectedTask: null,
        activities: @json($activityPayload),
        gradeForm: {
            calificacion: null,
            feedback: '',
        },

        init() {
            window.addEventListener('ai-canvas-refresh', () => this.refreshData());
        },

        activityMatchesType(activity, type) {
            if (type === 'all') return true;
            if (type === 'clase') return activity.type === 'clase';
            if (type === 'tarea') {
                return activity.is_homework || activity.type === 'tarea';
            }
            if (type === 'evaluacion') return activity.type !== 'clase' && !activity.is_homework && activity.type !== 'tarea';
            return true;
        },

        filteredActivities() {
            return (this.activities || []).filter(activity => {
                const courseOk = this.filterCourse === null || activity.course_id === this.filterCourse;
                const typeOk = this.activityMatchesType(activity, this.filterType);
                return courseOk && typeOk;
            });
        },

        countByType(type) {
            return (this.activities || []).filter(activity => {
                const courseOk = this.filterCourse === null || activity.course_id === this.filterCourse;
                const typeOk = this.activityMatchesType(activity, type);
                return courseOk && typeOk;
            }).length;
        },

        async refreshData() {
            try {
                const res = await fetch('{{ route('teacher.activities.index') }}', {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();
                this.activities = Array.isArray(data) ? data : [];
            } catch (e) {
                console.error('refreshData', e);
            }
        },

        openGradeModal(task) {
            this.selectedTask = task;
            this.gradeForm.calificacion = task?.calificacion ?? null;
            this.gradeForm.feedback = task?.feedback ?? '';
            this.gradeModalOpen = true;
        },

        async saveTaskGrade() {
            if (!this.selectedTask?.id) return;

            this.gradeSaving = true;
            try {
                const res = await fetch(`/teacher/tareas/${this.selectedTask.id}/grade`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({
                        calificacion: this.gradeForm.calificacion,
                        feedback: this.gradeForm.feedback,
                    }),
                });
                const json = await res.json();
                if (!res.ok || !json.success) {
                    throw new Error(json.message || json.error || 'No se pudo guardar la calificación');
                }

                const badge = document.getElementById(`task-grade-${this.selectedTask.id}`);
                if (badge) {
                    badge.textContent = Number(json.tarea.calificacion).toFixed(2);
                }

                this.gradeModalOpen = false;
            } catch (e) {
                alert(e.message || 'Error al guardar calificación');
            } finally {
                this.gradeSaving = false;
            }
        },

        formatDate(date) {
            if (!date) return '—';
            const d = new Date(date);
            if (Number.isNaN(d)) return date;
            return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },
    };
}

function activityForm() {
    return {
        createType: '{{ old('type', 'actividad') }}',
        title: '{{ old('title', '') }}',
        description: '{{ old('description', '') }}',
        loadingDescription: false,
        weight: {{ old('weight_percentage', 20) }},
        async generateDescription() {
            if (!this.title.trim() || this.loadingDescription) return;
            this.loadingDescription = true;
            try {
                const res = await fetch('{{ route('teacher.activities.ai_description') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({
                        title: this.title,
                        type: this.createType || 'actividad',
                    }),
                });
                const json = await res.json();
                if (!res.ok || !json.success) throw new Error(json.error || 'No se pudo generar');
                this.description = json.description || '';
            } catch (e) {
                alert(e.message || 'Error al generar descripción');
            } finally {
                this.loadingDescription = false;
            }
        },
    };
}

// Funciones globales
window.deleteActivity = function(id) {
    if (!confirm('¿Eliminar esta actividad y todas sus notas?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/teacher/activities/${id}`;
    form.innerHTML = `<input name="_token" value="${CSRF}">
                      <input name="_method" value="DELETE">`;
    document.body.appendChild(form);
    form.submit();
};

window.editActivityWithAI = function(id, title) {
    const instruction = prompt(`¿Qué deseas mejorar en "${title}"?\nEj: "más formal", "más desafiante", "enfoque práctico"`);
    if (!instruction || !instruction.trim()) return;

    fetch(`/teacher/activities/${id}/ai-edit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({ instruction }),
    })
    .then(res => res.json())
    .then(json => {
        if (!json.success) throw new Error(json.error || 'No se pudo editar');
        alert('Descripción actualizada con IA. Se refrescará la vista.');
        window.location.reload();
    })
    .catch(e => {
        alert(e.message || 'Error al editar con IA');
    });
};
</script>
@include('components.ai-assistant-bubble')
</body>
</html>