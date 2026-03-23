<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Planificacion;
use App\Models\Student;
use App\Models\Tarea;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AICommandHandlerController extends Controller
{
private const DESTRUCTIVE = ['destroyCourse', 'destroyAllStudentsFromCourse', 'deleteResource', 'deleteActivities'];

    /**
     * Definiciones de herramientas para OpenAI
     */
    private function toolDefinitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'createCourse',
                    'description' => 'Crea un nuevo curso/sección para el profesor.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'subject_name' => ['type' => 'string',  'description' => 'Nombre de la materia, ej: Matemáticas'],
                            'grade'        => ['type' => 'string',  'description' => 'Grado, ej: 3ro Primaria'],
                            'section'      => ['type' => 'string',  'description' => 'Sección opcional, ej: A, B'],
                            'school_year'  => ['type' => 'string',  'description' => 'Año escolar, ej: 2025-2026'],
                        ],
                        'required' => ['subject_name', 'grade'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'createActivity',
                    'description' => 'Crea una clase (teórica), actividad evaluativa o tarea (homework) en un curso. Puede incluir adaptación NEE.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'course_id'         => ['type' => 'integer'],
                            'course_name_hint'  => ['type' => 'string'],
                            'type'              => ['type' => 'string', 'enum' => ['clase', 'actividad', 'tarea']],
                            'is_homework'       => ['type' => 'boolean'],
                            'nee_type'          => ['type' => 'string', 'description' => 'Tipo de necesidad (TDAH, TEA/Autismo, Dislexia, Discalculia, Otro)'],
                            'title'             => ['type' => 'string'],
                            'description'       => ['type' => 'string'],
                            'max_score'         => ['type' => 'integer'],
                            'weight_percentage' => ['type' => 'number'],
                            'due_date'          => ['type' => 'string'],
                        ],
                        'required' => ['course_id', 'type', 'title', 'description', 'weight_percentage'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'modifyActivity',
                    'description' => 'Modifica una actividad o clase existente.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'activity_id'       => ['type' => 'integer'],
                            'title'             => ['type' => 'string'],
                            'description'       => ['type' => 'string'],
                            'due_date'          => ['type' => 'string'],
                            'weight_percentage' => ['type' => 'number'],
                        ],
                        'required' => ['activity_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'registerStudent',
                    'description' => 'Inscribe alumnos en un curso.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'names'            => ['type' => 'array', 'items' => ['type' => 'string']],
                            'course_id'        => ['type' => 'integer'],
                            'course_name_hint' => ['type' => 'string'],
                            'grade'            => ['type' => 'string'],
                        ],
                        'required' => ['course_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'bulkPlan',
                    'description' => 'Genera planificación mensual completa (Lunes/Jueves).',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'course_id'    => ['type' => 'integer'],
                            'topic'        => ['type' => 'string'],
                            'target_month' => ['type' => 'string'],
                            'units'        => ['type' => 'array', 'items' => ['type' => 'object']],
                            'topics'       => ['type' => 'array', 'items' => ['type' => 'string']],
                            'calendar_preferences' => [
                                'type' => 'object',
                                'properties' => [
                                    'start_date' => ['type' => 'string'],
                                    'end_date' => ['type' => 'string'],
                                    'repeat_days' => ['type' => 'array', 'items' => ['type' => 'string']],
                                    'allow_past' => ['type' => 'boolean'],
                                    'require_confirmation' => ['type' => 'boolean'],
                                    'override_conflicts' => ['type' => 'boolean'],
                                ],
                            ],
                        ],
                        'required' => ['course_id', 'topic', 'target_month', 'units'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'deleteActivities',
                    'description' => 'Elimina actividades dentro de un rango de fechas para un curso.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'course_id' => ['type' => 'integer'],
                            'start_date' => ['type' => 'string'],
                            'end_date' => ['type' => 'string'],
                            'override_conflicts' => ['type' => 'boolean'],
                        ],
                        'required' => ['course_id', 'start_date', 'end_date'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'deleteResource',
                    'description' => 'Elimina recursos (actividades, alumnos, cursos).',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'resource_type' => ['type' => 'string', 'enum' => ['activity', 'student', 'course', 'all_in_course']],
                            'resource_id'   => ['type' => 'integer'],
                        ],
                        'required' => ['resource_type', 'resource_id'],
                    ],
                ],
            ]
        ];
    }

    /**
     * Punto de entrada principal
     */
    public function handle(Request $request): JsonResponse
    {
        // Intercepción local ANTES de validar o llamar a OpenAI
        $payload = $request->all();
        $rawMessage = (string) (
            data_get($payload, 'message')
            ?? data_get($payload, 'prompt')
            ?? data_get($payload, 'payload.mensaje_usuario')
            ?? data_get($payload, 'payload.prompt')
            ?? ''
        );
        $teacher = auth()->user();
        if (! $teacher) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }
        $prompt = (string) (
            $request->input('prompt')
            ?? data_get($payload, 'payload.mensaje_usuario')
            ?? $rawMessage
        );
        $hasDeleteIntent = $this->hasDeleteIntent($prompt ?: $rawMessage);

        if (preg_match('/(borrar todo|limpiar todo|eliminar todo|vaciar todo|pizarra limpia)/i', $rawMessage)) {
            $teacherId = auth()->id();

            DB::table('tareas')
                ->whereIn('actividad_id', function ($query) use ($teacherId) {
                    $query->select('id')->from('activities')->where('teacher_id', $teacherId);
                })
                ->delete();

            DB::table('activities')->where('teacher_id', $teacherId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pizarra limpia.',
                'action' => 'refresh',
                'action_type' => 'delete',
                'icon' => '🗑️',
            ]);
        }

        $intentDeleteDates = $this->parseDatesFromText($rawMessage);
        $intentCourseId = $this->detectCourseContext($prompt ?: $rawMessage, $teacher);

        if ($hasDeleteIntent && !empty($intentDeleteDates)) {
            $courseId = $intentCourseId;
            $result = $this->doDeleteActivities([
                'course_id' => $courseId ?? 0,
                'start_date' => $intentDeleteDates[0]->format('Y-m-d'),
                'end_date' => end($intentDeleteDates)->format('Y-m-d'),
            ], $teacher->id);
            return response()->json($result);
        }

        $request->validate([
            'prompt' => ['sometimes', 'string', 'max:1000'],
            'message' => ['nullable', 'string'],
            'confirmed' => ['sometimes', 'boolean'],
            'screen_context' => ['sometimes', 'nullable', 'array'],
            'payload' => ['sometimes', 'array'],
            'payload.mensaje_usuario' => ['sometimes', 'string', 'max:1000'],
            'payload.contexto' => ['sometimes', 'nullable', 'array'],
        ]);

        $wrappedPayload = $request->input('payload', []);
        $messageText = $request->input('message', '');
        
        $prompt = $request->input('prompt', $wrappedPayload['mensaje_usuario'] ?? $messageText);
        $confirmed = (bool) $request->input('confirmed', false);
        $screenContext = $request->input('screen_context', $wrappedPayload['contexto'] ?? null);
        $hasDeleteIntent = $this->hasDeleteIntent($prompt ?: $rawMessage);
        $hasPlanningIntent = $this->hasPlanningIntent($prompt ?: $rawMessage);

        $today = now()->format('Y-m-d');

        // Contexto de cursos para la IA
        $courses = Course::where('teacher_id', $teacher->id)->get(['id', 'subject_name', 'grade']);
        $coursesContext = "Cursos del profesor:\n" . $courses->map(fn($c) => "ID:{$c->id} - {$c->subject_name} ({$c->grade})")->join("\n");

        $contextJson = $screenContext ? json_encode($screenContext, JSON_UNESCAPED_UNICODE) : '{}';

        $systemPrompt = implode("\n", [
            "Eres Nova, motor central académico. SIEMPRE debes responder con una llamada a herramienta; NUNCA respondas con texto plano.",
            "",
            "MAPA DE INTENCIONES → HERRAMIENTA:",
            "- crear curso / sección → createCourse",
            "- crear clase / actividad / evaluación / tarea → createActivity  (type: clase|actividad|tarea)",
            "- adaptación NEE / TDAH / TEA / dislexia / discalculia → createActivity con nee_type relleno",
            "- modificar / cambiar / editar actividad existente → modifyActivity",
            "- inscribir / agregar alumnos → registerStudent",
            "- planificar mes / cronograma / calendario → bulkPlan",
            "- borrar / eliminar / quitar actividades en un rango de fechas → deleteActivities",
            "- borrar / eliminar / quitar una actividad, curso o alumno específico → deleteResource",
            "",
            "REGLA DE BORRADO: usa deleteResource o deleteActivities SOLO si el usuario pide explícitamente borrar/eliminar/limpiar/vaciar. Para crear/planificar NUNCA uses herramientas destructivas.",
            "Si faltan datos, asume valores coherentes y menciona lo asumido.",
            "",
            "Fecha actual: $today.",
            "Cursos del profesor:",
            $coursesContext,
            "Contexto vivo actual:",
            $contextJson,
        ]);

        $response = Http::timeout(120)
            ->withToken(env('OPENAI_API_KEY'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => 'gpt-4.1',
                'temperature' => 0,
                'tool_choice' => 'required',
                'tools'       => $this->toolDefinitions(),
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Error de conexión con IA'], 500);
        }

        $message = $response->json('choices.0.message');
        $toolCalls = $message['tool_calls'] ?? [];

        if (! $hasDeleteIntent) {
            $toolCalls = array_values(array_filter($toolCalls, function ($tc) {
                $fn = $tc['function']['name'] ?? '';
                return ! in_array($fn, self::DESTRUCTIVE, true);
            }));
        }

        if ($hasPlanningIntent) {
            $bulkCalls = array_values(array_filter($toolCalls, function ($tc) {
                return ($tc['function']['name'] ?? '') === 'bulkPlan';
            }));
            if (! empty($bulkCalls)) {
                $toolCalls = $bulkCalls;
            }
        }

        if (empty($toolCalls)) {
            $fallback = $message['content'];
            if (empty($fallback)) {
                $fallback = $hasDeleteIntent
                    ? 'Para borrar, indica qué quieres eliminar (actividad, curso o rango de fechas).'
                    : 'No entendí el comando. Intenta ser más específico, por ejemplo: "crear actividad sobre fotosíntesis en Ciencias 3ro".';
            }
            return response()->json(['message' => $fallback]);
        }

        // Check de confirmación para acciones destructivas
        $destructiveFound = collect($toolCalls)->filter(fn($tc) => in_array($tc['function']['name'], self::DESTRUCTIVE));
        if ($destructiveFound->isNotEmpty() && !$confirmed) {
            $destructiveActions = $destructiveFound->map(function ($tc) {
                $args = json_decode($tc['function']['arguments'] ?? '{}', true) ?? [];
                return ['function' => $tc['function']['name'], 'args' => $args];
            })->values()->toArray();
            return response()->json([
                'requires_confirmation' => true,
                'destructive_actions'   => $destructiveActions,
                'warning'               => 'Esta acción eliminará datos de forma permanente y no se puede deshacer.',
            ]);
        }

        $createdCourseMap = [];
        $results = [];

        foreach ($toolCalls as $tc) {
            $fn = $tc['function']['name'];
            $args = json_decode($tc['function']['arguments'], true);

            // Forward confirmed flag into bulkPlan so it skips the preview step
            if ($confirmed && $fn === 'bulkPlan') {
                $args['confirmed'] = true;
            }

            // Resolver ID de curso si es 0 usando el nombre o el contexto de pantalla
            if (isset($args['course_id']) && $args['course_id'] <= 0) {
                if (!empty($screenContext['id'])) {
                    $args['course_id'] = $screenContext['id'];
                }
            }

            $results[] = $this->executeAction($fn, $args, $teacher->id, $createdCourseMap);
        }

        $planConfirmation = collect($results)->first(fn ($result) => ($result['requires_confirmation'] ?? false));
        if ($planConfirmation) {
            return response()->json([
                'requires_confirmation' => true,
                'message' => $planConfirmation['message'] ?? 'Confirma la planificación propuesta.',
                'plan_preview' => $planConfirmation['plan_preview'] ?? [],
                'conflicts' => $planConfirmation['conflicts'] ?? [],
                'actions' => [$planConfirmation],
            ]);
        }

        $actions = collect($results)->map(fn ($result) => [
            'success'     => (bool) ($result['success'] ?? false),
            'message'     => $result['message'] ?? '',
            'action_type' => $result['action_type'] ?? 'info',
            'icon'        => $result['icon'] ?? (($result['success'] ?? false) ? '✅' : 'ℹ️'),
            'data'        => $result['data'] ?? [],
        ])->toArray();

        $anySuccess = collect($actions)->contains(fn ($action) => $action['success']);

        return response()->json([
            'success'     => true,
            'results'     => $results,
            'actions'     => $actions,
            'any_success' => $anySuccess,
        ]);
    }

    /**
     * Dispatcher de acciones
     */
    private function executeAction(string $fn, array $args, int $teacherId, array &$createdCourseMap): array
    {
        try {
            return match ($fn) {
                'createCourse'    => $this->doCreateCourse($args, $teacherId, $createdCourseMap),
                'createActivity'  => $this->doCreateActivity($args, $teacherId),
                'modifyActivity'  => $this->doModifyActivity($args, $teacherId),
                'registerStudent' => $this->doRegisterStudent($args, $teacherId),
                'bulkPlan'        => $this->doBulkPlan($args, $teacherId),
                'deleteActivities'=> $this->doDeleteActivities($args, $teacherId),
                'deleteResource'  => $this->doDeleteResource($args, $teacherId),
                default           => ['success' => false, 'message' => "Acción $fn no definida."],
            };
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── IMPLEMENTACIONES DE LOGICA ──────────────────────────────────────────

    private function doCreateCourse($args, $teacherId, &$createdCourseMap)
    {
        $course = Course::create([
            'teacher_id'   => $teacherId,
            'subject_name' => $args['subject_name'],
            'grade'        => $args['grade'],
            'section'      => $args['section'] ?? null,
        ]);
        $createdCourseMap[strtolower($args['subject_name'])] = $course->id;
        return [
            'success'     => true,
            'message'     => "Curso creado: {$course->subject_name}",
            'action_type' => 'course',
            'icon'        => '🏫',
            'data'        => ['course_id' => $course->id],
        ];
    }

    private function doCreateActivity($args, $teacherId)
    {
        $requestedType = strtolower($args['type'] ?? 'actividad');
        $isHomework = filter_var($args['is_homework'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($requestedType === 'tarea') {
            $isHomework = true;
            $resolvedType = 'tarea';
        } elseif ($requestedType === 'clase') {
            $resolvedType = 'clase';
        } else {
            $resolvedType = 'actividad';
            if ($isHomework) {
                $resolvedType = 'tarea';
            }
        }

        $neeType = $args['nee_type'] ?? null;
        $neeAdaptation = $neeType ? $this->buildNeeAdaptation($neeType) : null;

        $activity = Activity::create([
            'teacher_id'        => $teacherId,
            'course_id'         => $args['course_id'],
            'type'              => $resolvedType,
            'title'             => $args['title'],
            'description'       => $args['description'],
            'weight_percentage' => $args['weight_percentage'],
            'max_score'         => $args['max_score'] ?? 20,
            'due_date'          => $args['due_date'] ?? null,
            'is_homework'       => $isHomework,
            'nee_type'          => $neeType,
            'nee_adaptation'    => $neeAdaptation,
        ]);
        return [
            'success'     => true,
            'message'     => "Actividad '{$activity->title}' creada.",
            'action_type' => 'activity',
            'icon'        => '📝',
            'data'        => ['activity_id' => $activity->id],
        ];
    }

    private function doModifyActivity($args, $teacherId)
    {
        $activity = Activity::where('id', $args['activity_id'])->first();
        if ($activity) {
            $activity->update(array_filter($args));
            return [
                'success'     => true,
                'message'     => "Actividad '{$activity->title}' actualizada.",
                'action_type' => 'activity',
                'icon'        => '📝',
                'data'        => ['activity_id' => $activity->id],
            ];
        }
        return [
            'success'     => false,
            'message'     => "No se encontró la actividad.",
            'action_type' => 'activity',
            'icon'        => '⚠️',
        ];
    }

    private function doRegisterStudent($args, $teacherId)
    {
        $course = Course::where('id', $args['course_id'])->first();
        if (! $course) {
            return [
                'success'     => false,
                'message'     => 'No se pudo identificar el curso. Indica el curso completo o su ID.',
                'action_type' => 'student',
                'icon'        => '⚠️',
            ];
        }

        $grade = $args['grade'] ?? $course->grade;
        if (empty($grade)) {
            return [
                'success'     => false,
                'message'     => '¿A qué grado quieres inscribir a ese estudiante? Por ejemplo: Primera sección.',
                'action_type' => 'student',
                'icon'        => '⚠️',
            ];
        }

        $results = [];
        foreach ($args['names'] as $name) {
            $student = Student::firstOrCreate(
                ['name' => $name, 'teacher_id' => $teacherId],
                ['grade' => $grade]
            );
            $student->courses()->syncWithoutDetaching([$args['course_id']]);
            $results[] = $name;
        }
        return [
            'success'     => true,
            'message'     => "Alumnos inscritos: " . implode(', ', $results),
            'action_type' => 'student',
            'icon'        => '👩‍🎓',
            'data'        => ['names' => $results, 'course_id' => $args['course_id'], 'grade' => $grade],
        ];
    }

    private function doBulkPlan($args, $teacherId)
    {
        $preferences = $args['calendar_preferences'] ?? [];
        $startDate = $this->parseDate($preferences['start_date'] ?? now()->format('Y-m-d'));
        $endDate = $this->parseDate($preferences['end_date'] ?? $startDate->copy()->endOfMonth());
        if (! ($preferences['allow_past'] ?? false) && $startDate->lt(now()->startOfDay())) {
            $startDate = now()->startOfDay();
        }
        $repeatDays = $this->normalizeRepeatDays($preferences['repeat_days'] ?? ['monday', 'thursday']);
        $topics = array_filter($args['topics'] ?? [$args['topic'] ?? 'Plan mensual']);
        if (empty($topics)) {
            $topics = ['Plan mensual'];
        }

        $plan = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            if (in_array($cursor->dayOfWeek, $repeatDays, true)) {
                $isThursday = $cursor->dayOfWeek === Carbon::THURSDAY;
                $topic = $topics[count($plan) % count($topics)];
                $plan[] = [
                    'date' => $cursor->format('Y-m-d'),
                    'title' => $isThursday ? "Jueves práctico · {$topic}" : "Lunes teórico · {$topic}",
                    'type' => $isThursday ? 'actividad' : 'clase',
                    'description' => $isThursday
                        ? "Dinámica práctica y juegos sobre {$topic}."
                        : "Clase teórica para copiar en cuaderno sobre {$topic}.",
                    'weight_percentage' => $isThursday ? 15 : 0,
                    'max_score' => $isThursday ? 20 : 0,
                ];
            }
            $cursor->addDay();
        }

        $conflicts = Activity::where('teacher_id', $teacherId)
            ->whereBetween('due_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->pluck('due_date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();

        $planPreview = array_map(fn ($entry) => [
            'date' => $entry['date'],
            'title' => $entry['title'],
            'type' => $entry['type'],
        ], $plan);
        $conflictPreview = array_values(array_filter($planPreview, fn ($entry) => in_array($entry['date'], $conflicts, true)));

        if (empty($plan)) {
            return [
                'success' => false,
                'message' => 'No se encontraron días válidos para planificar.',
                'action_type' => 'bulk_plan',
                'icon' => '⚠️',
            ];
        }

        if (! ($args['confirmed'] ?? false)) {
            return [
                'requires_confirmation' => true,
                'message' => "Voy a generar " . count($plan) . " sesiones (Lunes teorías y Jueves prácticas) del {$startDate->format('d/m')} al {$endDate->format('d/m')}. ¿Procedo?",
                'plan_preview' => $planPreview,
                'conflicts' => $conflictPreview,
                'action_type' => 'bulk_plan',
                'icon' => '📅',
                'data' => ['course_id' => $args['course_id']],
            ];
        }

        $created = [];
        foreach ($plan as $entry) {
            if (in_array($entry['date'], $conflicts, true) && ! ($args['override_conflicts'] ?? false)) {
                continue;
            }
            $created[] = Activity::create([
                'teacher_id'        => $teacherId,
                'course_id'         => $args['course_id'],
                'title'             => $entry['title'],
                'description'       => $entry['description'],
                'type'              => $entry['type'],
                'weight_percentage' => $entry['weight_percentage'],
                'max_score'         => $entry['max_score'],
                'due_date'          => $entry['date'],
            ]);
        }

        return [
            'success'     => true,
            'message'     => "Plan generado para " . ($args['topic'] ?? $startDate->format('F')),
            'action_type' => 'bulk_plan',
            'icon'        => '📅',
            'data'        => [
                'course_id' => $args['course_id'],
                'created' => count($created),
            ],
            'plan_preview' => $planPreview,
            'conflicts' => $conflictPreview,
        ];
    }

    private function doDeleteActivities(array $args, int $teacherId): array
    {
        $start = $this->parseDate($args['start_date']);
        $end = $this->parseDate($args['end_date']);
        if ($end->lt($start)) {
            $end = $start->copy();
        }

        $query = Activity::where('teacher_id', $teacherId)
            ->whereBetween('due_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
        if (! empty($args['course_id'])) {
            $query->where('course_id', $args['course_id']);
        }

        try {
            $count = $query->count();
            $query->delete();
        } catch (\Throwable $e) {
            Log::error('doDeleteActivities error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Ocurrió un error al eliminar actividades: ' . $e->getMessage(),
                'action_type' => 'delete',
                'icon' => '⚠️',
            ];
        }

        return [
            'success' => true,
            'message' => "Se eliminaron {$count} actividades entre {$start->format('d/m/Y')} y {$end->format('d/m/Y')}.",
            'action_type' => 'delete',
            'icon' => '🗑️',
            'data' => ['deleted' => $count, 'course_id' => $args['course_id'] ?? null],
        ];
    }

    private function parseDate(string $value): Carbon
    {
        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return now();
        }
    }

    private function parseDatesFromText(string $text): array
    {
        $months = [
            'enero' => 1, 'febrero' => 2, 'marzo' => 3,
            'abril' => 4, 'mayo' => 5, 'junio' => 6,
            'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'setiembre' => 9,
            'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12,
        ];
        $found = [];
        if (preg_match_all('/(\d{1,2})\s*(de\s*)?(' . implode('|', array_keys($months)) . ')/iu', $text, $matches, PREG_SET_ORDER)) {
            $year = now()->format('Y');
            if (preg_match('/\b(20\d{2})\b/', $text, $yearMatch)) {
                $year = (int) $yearMatch[1];
            }
            foreach ($matches as $match) {
                $day = (int) $match[1];
                $monthName = mb_strtolower($match[3]);
                $month = $months[$monthName] ?? now()->month;
                try {
                    $found[] = Carbon::createFromDate($year, $month, $day);
                } catch (\Throwable) {
                }
            }
        }
        return $found;
    }

    private function detectCourseContext(string $text, $teacher): ?int
    {
        $gradeKeywords = [
            'primer grado' => 'Primer Grado',
            'segundo grado' => 'Segundo Grado',
            'tercer grado' => 'Tercer Grado',
        ];
        foreach ($gradeKeywords as $keyword => $grade) {
            if (stripos($text, $keyword) !== false) {
                $course = Course::where('teacher_id', $teacher->id)
                    ->where(function ($q) use ($grade) {
                        $q->where('grade', $grade)
                          ->orWhere('subject_name', 'like', '%' . $grade . '%');
                    })
                    ->first();
                if ($course) {
                    return $course->id;
                }
            }
        }
        return null;
    }

    private function normalizeRepeatDays(array $days): array
    {
        $map = [
            'monday' => Carbon::MONDAY,
            'martes' => Carbon::TUESDAY,
            'tuesday' => Carbon::TUESDAY,
            'miércoles' => Carbon::WEDNESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'jueves' => Carbon::THURSDAY,
            'thursday' => Carbon::THURSDAY,
            'viernes' => Carbon::FRIDAY,
            'friday' => Carbon::FRIDAY,
            'sábado' => Carbon::SATURDAY,
            'saturday' => Carbon::SATURDAY,
            'domingo' => Carbon::SUNDAY,
            'sunday' => Carbon::SUNDAY,
        ];
        $normalized = [];
        foreach ($days as $day) {
            $key = strtolower($day);
            $normalized[] = $map[$key] ?? Carbon::MONDAY;
        }
        return array_values(array_unique($normalized));
    }

    private function doDeleteResource($args, $teacherId)
    {
        if ($args['resource_type'] === 'activity') {
            Activity::where('id', $args['resource_id'])->delete();
        } elseif ($args['resource_type'] === 'course') {
            Course::where('id', $args['resource_id'])->delete();
        }
        return [
            'success'     => true,
            'message'     => "Recurso eliminado.",
            'action_type' => 'delete',
            'icon'        => '🗑️',
            'data'        => $args,
        ];
    }

    private function buildNeeAdaptation(string $neeType): string
    {
        return match (mb_strtolower($neeType)) {
            'tdah' => "Para este alumno con TDAH, segmenta la actividad en pasos de 10 minutos, usa recordatorios visuales y alterna momentos de movimiento breve. Evita instrucciones largas y confirma comprensión con preguntas cortas.",
            'tea', 'autismo', 'tea/autismo', 'tea - autismo' => "Para este alumno con TEA, anticipa la secuencia con un mini-guion visual, reduce estímulos distractores y ofrece ejemplos concretos. Permite tiempos de respuesta más amplios y valida con apoyos visuales.",
            'dislexia' => "Para este alumno con dislexia, prioriza instrucciones orales claras, textos con tipografía legible y apoyos visuales. Permite responder de forma oral o con opciones guiadas y evita copiado extenso.",
            'discalculia' => "Para este alumno con discalculia, utiliza material manipulativo, ejemplos paso a paso y apoyos visuales. Evita sobrecarga numérica y ofrece tiempo adicional para resolver.",
            default => "Para este alumno con {$neeType}, adapta la actividad con instrucciones breves, apoyos visuales y tiempo extra según necesidad. Prioriza la comprensión del objetivo sobre la cantidad de ejercicios.",
        };
    }

    private function hasDeleteIntent(?string $text): bool
    {
        $value = mb_strtolower((string) $text);
        return (bool) preg_match('/\b(borrar|eliminar|limpiar|vaciar|quitar)\b/u', $value);
    }

    private function hasPlanningIntent(?string $text): bool
    {
        $value = mb_strtolower((string) $text);
        return (bool) preg_match('/\b(planifica|planificar|planificación|planificacion|cronograma|calendario|genera.*mes|organiza.*mes|mes de)\b/u', $value);
    }
}