<x-app-layout>
    <div class="onboarding-shell py-5">
        <div class="container">
            <div class="onboarding-card mx-auto animate__animated animate__fadeIn" x-data="onboardingWizard()">
                <div class="text-center mb-4">
                    <p class="onboarding-kicker mb-2">Nova Academy</p>
                    <h1 class="onboarding-title mb-2">Configura tu asistente inteligente</h1>
                    <p class="text-muted mb-0">Una experiencia personalizada según tu rol y contexto.</p>
                </div>

                <div class="onboarding-progress mb-4">
                    <div class="onboarding-progress-bar" :style="{ width: progress + '%' }"></div>
                </div>

                <form x-ref="wizardForm" method="POST" action="{{ route('onboarding.save') }}">
                    @csrf
                    {{-- role siempre en el DOM, valor reactivo de Alpine --}}
                    <input type="hidden" name="role" x-model="role">

                    @if($errors->any())
                        <div class="alert alert-danger py-3 mb-4">
                            <strong>Revisa los datos:</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- PASO 1: x-show mantiene el DOM, solo oculta visualmente --}}
                    <div x-show="step === 1" x-cloak>
                        <div class="animate__animated animate__fadeIn">
                            <h2 class="h5 fw-bold mb-3 text-center">Paso 1 · Identidad</h2>
                            <p class="text-muted text-center mb-4">¿Cómo quieres que configuremos tu panel?</p>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <button type="button" class="role-card w-100" :class="{ 'active': role === 'profesor' }" @click="selectRole('profesor')">
                                        <span class="role-icon">👩‍🏫</span>
                                        <span class="role-title">Soy Docente</span>
                                        <span class="role-subtitle">Planificación de clases y actividades</span>
                                    </button>
                                </div>
                                <div class="col-12 col-md-6">
                                    <button type="button" class="role-card w-100" :class="{ 'active': role === 'director' }" @click="selectRole('director')">
                                        <span class="role-icon">🏫</span>
                                        <span class="role-title">Soy Director</span>
                                        <span class="role-subtitle">Visión institucional y liderazgo pedagógico</span>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary rounded-3 px-4" :disabled="!role" @click="nextStep">
                                    Continuar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- PASO 2: x-show → inputs permanecen en el DOM al enviar --}}
                    <div x-show="step === 2" x-cloak>
                        <div class="animate__animated animate__fadeIn">
                            <h2 class="h5 fw-bold mb-3 text-center">Paso 2 · Contexto</h2>

                            {{-- Sección Profesor --}}
                            <div x-show="role === 'profesor'">
                                <p class="text-muted text-center mb-4">Cuéntanos sobre tus clases para personalizar la IA.</p>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Nivel educativo</label>
                                        <select class="form-select rounded-3" name="nivel_educativo">
                                            <option value="">Selecciona...</option>
                                            <option value="primaria">Primaria</option>
                                            <option value="secundaria">Secundaria</option>
                                            <option value="bachillerato">Bachillerato</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Clases por semana</label>
                                        <input type="number" min="1" max="20" class="form-control rounded-3" name="clases_semana" placeholder="5">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Materias asignadas</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <label class="chip"><input type="checkbox" name="materias[]" value="matematicas"> Matemáticas</label>
                                            <label class="chip"><input type="checkbox" name="materias[]" value="ciencias"> Ciencias</label>
                                            <label class="chip"><input type="checkbox" name="materias[]" value="lenguaje"> Lenguaje</label>
                                            <label class="chip"><input type="checkbox" name="materias[]" value="historia"> Historia</label>
                                            <label class="chip"><input type="checkbox" name="materias[]" value="ingles"> Inglés</label>
                                            <label class="chip"><input type="checkbox" name="materias[]" value="arte"> Arte</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Grados / cursos</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <label class="chip"><input type="checkbox" name="cursos[]" value="1ro"> 1ro</label>
                                            <label class="chip"><input type="checkbox" name="cursos[]" value="2do"> 2do</label>
                                            <label class="chip"><input type="checkbox" name="cursos[]" value="3ro"> 3ro</label>
                                            <label class="chip"><input type="checkbox" name="cursos[]" value="4to"> 4to</label>
                                            <label class="chip"><input type="checkbox" name="cursos[]" value="5to"> 5to</label>
                                            <label class="chip"><input type="checkbox" name="cursos[]" value="6to"> 6to</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Días de clase</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <label class="chip"><input type="checkbox" name="dias[]" value="lunes"> Lunes</label>
                                            <label class="chip"><input type="checkbox" name="dias[]" value="martes"> Martes</label>
                                            <label class="chip"><input type="checkbox" name="dias[]" value="miercoles"> Miércoles</label>
                                            <label class="chip"><input type="checkbox" name="dias[]" value="jueves"> Jueves</label>
                                            <label class="chip"><input type="checkbox" name="dias[]" value="viernes"> Viernes</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Sección Director --}}
                            <div x-show="role === 'director'">
                                <p class="text-muted text-center mb-4">Configura el contexto institucional para una IA estratégica.</p>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Nombre de la institución</label>
                                        <input type="text" class="form-control rounded-3" name="nombre_institucion" placeholder="Ej. Colegio San Martín">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Cantidad de docentes</label>
                                        <input type="number" min="1" max="5000" class="form-control rounded-3" name="cantidad_docentes" placeholder="35">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Modelo pedagógico</label>
                                        <select class="form-select rounded-3" name="modelo_pedagogico">
                                            <option value="">Selecciona...</option>
                                            <option value="constructivista">Constructivista</option>
                                            <option value="tradicional">Tradicional</option>
                                            <option value="aprendizaje_basado_proyectos">ABP (Proyectos)</option>
                                            <option value="competencias">Por competencias</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Visión pedagógica</label>
                                        <textarea class="form-control rounded-3" rows="4" name="vision_pedagogica" placeholder="Describe tus objetivos institucionales..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-light rounded-3 px-4" @click="prevStep">Atrás</button>
                                <button type="button" class="btn btn-primary rounded-3 px-4" @click="handleSubmit">
                                    Finalizar configuración
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- PASO 3: Pantalla de carga mientras el form se envía --}}
                    <div x-show="step === 3" x-cloak>
                        <div class="animate__animated animate__fadeIn text-center py-5">
                            <div class="onboarding-spinner mx-auto mb-4"></div>
                            <h2 class="h5 fw-bold mb-2">Sincronizando tu espacio</h2>
                            <p class="text-muted mb-0" x-text="syncMessage"></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .onboarding-shell {
            min-height: calc(100vh - 140px);
            background: radial-gradient(1200px 600px at 20% -10%, rgba(139, 92, 246, 0.18), transparent 60%),
                        radial-gradient(1000px 500px at 100% 10%, rgba(59, 130, 246, 0.12), transparent 60%),
                        #f8fafc;
        }
        .onboarding-card {
            max-width: 860px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #eef2ff;
            border-radius: 28px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            padding: 2rem;
        }
        .onboarding-kicker {
            font-size: 0.78rem;
            letter-spacing: 0.11em;
            text-transform: uppercase;
            color: #6366f1;
            font-weight: 700;
        }
        .onboarding-title {
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #0f172a;
        }
        .onboarding-progress {
            height: 8px;
            background: #eef2ff;
            border-radius: 999px;
            overflow: hidden;
        }
        .onboarding-progress-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #8b5cf6, #3b82f6);
            transition: width 0.35s ease;
        }
        .role-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #fff;
            padding: 1.25rem;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            transition: all 0.25s ease;
        }
        .role-card:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(79, 70, 229, 0.12); }
        .role-card.active {
            border-color: #8b5cf6;
            box-shadow: 0 12px 26px rgba(79, 70, 229, 0.18);
            background: linear-gradient(180deg, #ffffff, #f8f5ff);
        }
        .role-icon { font-size: 1.6rem; }
        .role-title { font-weight: 700; color: #111827; }
        .role-subtitle { font-size: 0.9rem; color: #64748b; }
        .chip {
            border: 1px solid #dbeafe;
            background: #f8fbff;
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }
        .chip input { accent-color: #6366f1; }
        .onboarding-spinner {
            width: 64px;
            height: 64px;
            border: 4px solid #e2e8f0;
            border-top-color: #8b5cf6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
    @endpush

    @push('scripts')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function onboardingWizard() {
            return {
                step: 1,
                role: '',
                syncMessage: 'Configurando tu asistente...',
                syncMessages: [
                    'Configurando tu asistente...',
                    'Analizando currículo institucional...',
                    '¡Todo listo!'
                ],
                syncTimer: null,
                get progress() {
                    return this.step === 1 ? 33 : this.step === 2 ? 66 : 100;
                },
                selectRole(value) {
                    this.role = value;
                },
                nextStep() {
                    if (!this.role) return;
                    this.step = 2;
                },
                prevStep() {
                    this.step = 1;
                },
                handleSubmit() {
                    if (!this.role) {
                        alert('Selecciona tu rol (Docente o Director) antes de continuar.');
                        return;
                    }
                    // Primero mostramos el paso de carga, luego enviamos.
                    // Con x-show los inputs siguen en el DOM, el POST lleva todos los valores.
                    this.step = 3;
                    var index = 0;
                    this.syncMessage = this.syncMessages[index];
                    if (this.syncTimer) clearInterval(this.syncTimer);
                    this.syncTimer = setInterval(() => {
                        index = Math.min(index + 1, this.syncMessages.length - 1);
                        this.syncMessage = this.syncMessages[index];
                    }, 800);
                    setTimeout(() => {
                        if (this.syncTimer) clearInterval(this.syncTimer);
                        this.$refs.wizardForm.submit();
                    }, 2400);
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
