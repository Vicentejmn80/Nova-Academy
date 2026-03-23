<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrar Notas · {{ $activity->title }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', system-ui, sans-serif; }
        .grade-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(139,92,246,0.25);
            border-color: #8b5cf6;
        }
        .score-badge { transition: background-color .2s; }
        .mic-listening { animation: mic-glow 1s ease-in-out infinite alternate; }
        @keyframes mic-glow {
            from { box-shadow: 0 0 0 0 rgba(239,68,68,.4); }
            to   { box-shadow: 0 0 0 14px rgba(239,68,68,0); }
        }
    </style>
</head>
<body class="min-h-screen text-slate-200" style="background:radial-gradient(circle at top right,rgba(192,38,211,.16),transparent 42%), radial-gradient(circle at bottom left,rgba(124,58,237,.14),transparent 40%), #0c0118;">

<div x-data="gradeManager()" class="max-w-5xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('teacher.hub') }}" class="text-xs text-violet-400 hover:text-white transition flex items-center gap-2 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Volver al Hub
            </a>
            <span class="text-xs font-semibold uppercase tracking-widest text-violet-500">{{ $activity->course->subject_name }}</span>
            <h1 class="text-2xl font-bold text-white mt-1">{{ $activity->title }}</h1>
        </div>
        <button @click="openAIModal()" class="bg-violet-600 hover:bg-violet-700 text-white font-semibold px-5 py-3 rounded-2xl shadow-lg transition-all flex items-center gap-2">
            <i class="fa-solid fa-microphone-lines"></i> Asistente de Voz
        </button>
    </div>

    {{-- Banner de Sugerencias IA --}}
    <div x-show="suggestions.length > 0" x-cloak class="mb-6 bg-violet-900/30 border border-violet-500/30 rounded-2xl p-4">
        <div class="flex justify-between items-center mb-3">
            <p class="text-sm font-semibold text-violet-200">
                <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> 
                La IA detectó <span x-text="suggestions.length"></span> notas. ¿Deseas aplicarlas?
            </p>
            <button @click="suggestions = []" class="text-xs text-white/50 hover:text-white">Descartar todas</button>
        </div>
        <div class="flex flex-wrap gap-2">
            <template x-for="(s, index) in suggestions" :key="index">
                <button @click="applySuggestion(s, index)" class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-full border border-white/10 transition flex items-center gap-2">
                    <span x-text="s.student_name"></span>: <span class="font-bold text-violet-400" x-text="s.score"></span>
                    <i class="fa-solid fa-plus-circle opacity-50"></i>
                </button>
            </template>
        </div>
    </div>

    {{-- Tabla de Notas --}}
    <div class="rounded-3xl border overflow-hidden backdrop-blur-xl bg-white/5 border-white/10">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white/5 text-violet-100 text-xs font-bold uppercase tracking-wider">
                    <th class="px-6 py-4 w-16">#</th>
                    <th class="px-6 py-4">Alumno</th>
                    <th class="px-6 py-4 text-center">Nota (0-20)</th>
                    <th class="px-6 py-4 text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                <tr class="border-b border-white/5 hover:bg-white/5 transition" x-bind:class="highlightedId == {{ $student['id'] }} ? 'bg-violet-500/20' : ''">
                    <td class="px-6 py-4 text-violet-400 font-mono text-sm">{{ $index + 1 }}</td>
                    <td class="px-6 py-4">
                        <span class="font-medium text-white">{{ $student['name'] }}</span>
                    </td>
                    <td class="px-6 py-4 flex justify-center">
                        <input 
                            type="number" 
                            min="0" max="20" step="0.1"
                            value="{{ $student['existing_score'] ?? '' }}"
                            id="score_{{ $student['id'] }}"
                            onblur="autoSaveGrade(this, {{ $student['id'] }})"
                            class="grade-input w-24 text-center bg-white/10 border border-white/10 rounded-xl py-2 px-3 text-white font-bold transition focus:ring-2 focus:ring-violet-500"
                            placeholder="0.0"
                        >
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span 
                            id="badge_{{ $student['id'] }}"
                            class="score-badge inline-flex items-center justify-center w-10 h-10 rounded-full text-xs font-black shadow-lg {{ isset($student['existing_score']) ? 'bg-violet-500 text-white' : 'bg-white/5 text-white/30' }}"
                        >
                            {{ $student['existing_score'] ?? '?' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal IA --}}
<div x-data="aiConsole()" x-show="$store.aiModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
    <div @click.outside="$store.aiModal.close()" class="bg-slate-900 border border-white/10 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-violet-600 to-indigo-600 text-white flex justify-between items-center">
            <h3 class="font-bold">Dictado por Voz / IA</h3>
            <button @click="$store.aiModal.close()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-6">
            <textarea x-model="prompt" rows="4" class="w-full bg-slate-800 border border-white/10 rounded-2xl p-4 text-white text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none" placeholder="Ej: Juan Pérez saco 18 y Maria Lopez 20..."></textarea>
            <div class="mt-4 flex justify-between gap-4">
                <button @click="toggleVoice()" :class="listening ? 'bg-red-500 mic-listening' : 'bg-slate-700'" class="flex-1 py-3 rounded-xl text-white font-medium transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-microphone"></i> <span x-text="listening ? 'Escuchando...' : 'Hablar'"></span>
                </button>
                <button @click="sendToAI()" :disabled="processing || !prompt" class="flex-1 bg-violet-600 hover:bg-violet-700 py-3 rounded-xl text-white font-bold flex items-center justify-center gap-2 disabled:opacity-50">
                    <i class="fa-solid" :class="processing ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles'"></i> Procesar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const ACTIVITY_ID = {{ $activity->id }};
const GRADE_STORE_URL = '{{ route("teacher.grades.store", $activity) }}';
const AI_PARSE_URL = '{{ route("teacher.grades.ai_parse", $activity) }}';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

async function autoSaveGrade(input, studentId) {
    let score = parseFloat(input.value);
    if (isNaN(score)) return;

    score = Math.max(0, Math.min(20, score));
    input.value = score;

    const badge = document.getElementById(`badge_${studentId}`);
    badge.textContent = score;
    badge.classList.remove('bg-white/5', 'text-white/30');
    badge.classList.add('bg-violet-500', 'text-white');

    try {
        await fetch(GRADE_STORE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                grades: [{
                    student_id: studentId,
                    score: score
                }]
            })
        });
        input.classList.add('border-emerald-500');
        setTimeout(() => input.classList.remove('border-emerald-500'), 1000);
    } catch (err) {
        console.error('Error al guardar:', err);
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.store('aiModal', {
        open: false,
        show() { this.open = true; },
        close() { this.open = false; }
    });
});

function gradeManager() {
    return {
        suggestions: [],
        highlightedId: null,
        openAIModal() { Alpine.store('aiModal').show(); },
        applySuggestion(s, index) {
            const studentId = this._findStudentIdByName(s.student_name);
            if (studentId) {
                const input = document.getElementById(`score_${studentId}`);
                input.value = s.score;
                autoSaveGrade(input, studentId);
                this.highlightedId = studentId;
                setTimeout(() => { if(this.highlightedId == studentId) this.highlightedId = null; }, 3000);
            }
            this.suggestions.splice(index, 1);
        },
        _findStudentIdByName(name) {
            const cells = document.querySelectorAll('td span.font-medium');
            for (let cell of cells) {
                if (cell.textContent.toLowerCase().trim() === name.toLowerCase().trim() || 
                    cell.textContent.toLowerCase().includes(name.toLowerCase())) {
                    return cell.closest('tr').querySelector('input[type="number"]').id.replace('score_', '');
                }
            }
            return null;
        }
    };
}

function aiConsole() {
    return {
        prompt: '',
        processing: false,
        listening: false,
        recognition: null,
        toggleVoice() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SR) return alert('Navegador no soportado');
            if (this.listening) { this.recognition.stop(); return; }
            this.recognition = new SR();
            this.recognition.lang = 'es-ES';
            this.recognition.onresult = (e) => { this.prompt = e.results[0][0].transcript; };
            this.recognition.onend = () => { this.listening = false; };
            this.recognition.start();
            this.listening = true;
        },
        async sendToAI() {
            this.processing = true;
            try {
                const response = await fetch(AI_PARSE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ prompt: this.prompt })
                });
                const data = await response.json();
                if (data.success && data.suggestions) {
                    // Acceder al componente gradeManager para pasarle las sugerencias
                    const mainComp = document.querySelector('[x-data="gradeManager()"]').__x.$data;
                    mainComp.suggestions = data.suggestions;
                    Alpine.store('aiModal').close();
                    this.prompt = '';
                } else {
                    alert(data.error || 'No se reconocieron notas.');
                }
            } catch (err) {
                console.error(err);
                alert('Error al conectar con la IA');
            } finally {
                this.processing = false;
            }
        }
    };
}
</script>

</body>
</html>