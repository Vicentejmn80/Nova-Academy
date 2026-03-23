<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoursesController extends Controller
{
    public function index(): View
    {
        $courses = Course::withCount('students')
            ->with(['activities', 'students' => fn ($q) => $q->orderBy('name')])
            ->where('teacher_id', auth()->id())
            ->latest()
            ->get();

        return view('teacher.courses.index', compact('courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_name' => ['required', 'string', 'max:120'],
            'grade'        => ['required', 'string', 'max:60'],
            'section'      => ['nullable', 'string', 'max:10'],
            'school_year'  => ['nullable', 'string', 'max:9'],
        ]);

        Course::create(array_merge($data, [
            'teacher_id'  => auth()->id(),
            'school_year' => $data['school_year'] ?? date('Y') . '-' . (date('Y') + 1),
        ]));

        return redirect()->route('teacher.courses.index')
                         ->with('success', 'Curso creado correctamente.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        abort_unless($course->teacher_id === auth()->id(), 403);
        $course->delete();

        return redirect()->route('teacher.courses.index')
                         ->with('success', 'Curso eliminado.');
    }

    /**
     * Bulk-import students from a newline-separated list of names.
     * Uses firstOrCreate so existing students are simply re-enrolled, not duplicated.
     */
    public function importStudents(Request $request, Course $course): JsonResponse
    {
        abort_unless($course->teacher_id === auth()->id(), 403);

        $request->validate([
            'names' => ['required', 'string'],
        ]);

        $lines = preg_split('/\r\n|\r|\n/', trim($request->input('names')));
        $lines = array_filter(array_map('trim', $lines));

        if (empty($lines)) {
            return response()->json(['error' => 'La lista de nombres está vacía.'], 422);
        }

        $created  = 0;
        $enrolled = 0;
        $ids      = [];

        foreach ($lines as $name) {
            if (mb_strlen($name) < 2) continue;

            [$student, $wasCreated] = [
                Student::firstOrCreate(
                    ['teacher_id' => auth()->id(), 'name' => $name],
                    [
                        'grade'   => $course->grade,
                        'section' => $course->section,
                    ]
                ),
                false,
            ];

            // firstOrCreate doesn't return whether it was created via boolean easily;
            // check wasRecentlyCreated flag instead
            if ($student->wasRecentlyCreated) $created++;

            $alreadyEnrolled = $course->students()->where('student_id', $student->id)->exists();
            if (! $alreadyEnrolled) {
                $course->students()->attach($student->id);
                $enrolled++;
            }

            $ids[] = $student->id;
        }

        return response()->json([
            'created'  => $created,
            'enrolled' => $enrolled,
            'skipped'  => count($ids) - $enrolled,
            'total'    => count($ids),
            'message'  => "{$created} alumnos nuevos creados, {$enrolled} inscritos en este curso.",
        ]);
    }

    /**
     * Remove a student from this course (does NOT delete the student globally).
     */
    public function removeStudent(Course $course, Student $student): JsonResponse
    {
        abort_unless($course->teacher_id === auth()->id(), 403);

        $course->students()->detach($student->id);

        return response()->json(['ok' => true]);
    }
}
