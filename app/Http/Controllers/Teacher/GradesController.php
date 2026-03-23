<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Grade;
use App\Services\GradeProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class GradesController extends Controller
{
    public function __construct(private GradeProcessingService $gradeService) {}

    /**
     * List activities for the authenticated teacher.
     */
    public function index(): View|RedirectResponse
    {
        $activity = Activity::where('teacher_id', auth()->id())
            ->where('type', '!=', 'clase')
            ->latest()
            ->first();

        if (! $activity) {
            return redirect()->route('teacher.activities.index')
                ->with('info', 'No hay actividades calificables para cargar notas aún.');
        }

        return $this->create($activity);
    }

    /**
     * Show the grade entry form for a specific activity.
     */
    public function create(Activity $activity): View
    {
        abort_unless($activity->teacher_id === auth()->id(), 403);

        $students = $activity->course
            ->students()
            ->get()
            ->map(fn ($student) => [
                'id'             => $student->id,
                'name'           => $student->name,
                'existing_score' => Grade::where('activity_id', $activity->id)
                                    ->where('student_id', $student->id)
                                    ->first()?->score,
            ]);

        return view('teacher.grades.create', compact('activity', 'students'));
    }

    /**
     * Persist grades submitted from the table form (Supports AJAX).
     */
    public function store(Request $request, Activity $activity): JsonResponse|RedirectResponse
    {
        abort_unless($activity->teacher_id === auth()->id(), 403);

        $data = $request->validate([
            'grades'                => ['required', 'array'],
            'grades.*.student_id'   => ['required', 'exists:students,id'],
            'grades.*.score'        => ['required', 'numeric', 'min:0', "max:{$activity->max_score}"],
            'grades.*.feedback'     => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($data['grades'] as $entry) {
            Grade::updateOrCreate(
                ['activity_id' => $activity->id, 'student_id' => $entry['student_id']],
                ['score' => $entry['score'], 'feedback_text' => $entry['feedback'] ?? null]
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Nota guardada.']);
        }

        return redirect()->back()->with('success', 'Notas guardadas correctamente.');
    }

    /**
     * Parse a free-text / voice prompt and return structured grade suggestions via AJAX.
     */
    public function parseWithAI(Request $request, Activity $activity): JsonResponse
    {
        abort_unless($activity->teacher_id === auth()->id(), 403);

        $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
        ]);

        try {
            // Llamada al servicio de procesamiento de IA
            $suggestions = $this->gradeService->parseGradesFromText(
                $request->input('prompt'),
                $activity->max_score
            );

            // Verificación de resultados
            if (empty($suggestions)) {
                return response()->json([
                    'success' => false, 
                    'error' => 'La IA no pudo identificar nombres o notas en el texto proporcionado.'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);

        } catch (\Exception $e) {
            Log::error('Error en parseWithAI: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Hubo un problema al procesar la solicitud con IA. Intente de nuevo.'
            ], 500);
        }
    }
}