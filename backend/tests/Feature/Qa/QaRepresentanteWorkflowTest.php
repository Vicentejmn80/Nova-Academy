<?php

namespace Tests\Feature\Qa;

use App\Models\Activity;
use App\Models\Grade;
use App\Models\Student;
use App\Services\RepresentanteDashboardService;
use App\Support\Qa\QaSchool;

class QaRepresentanteWorkflowTest extends QaTestCase
{
    public function test_parent_login_reaches_family_hub(): void
    {
        $this->httpLogin(QaSchool::parentEmail(1))
            ->assertRedirect();

        $this->get(route('dashboard'))->assertRedirect(route('representante.dashboard'));
        $this->get(route('representante.dashboard'))
            ->assertOk()
            ->assertSee('Alumno QA 01');
    }

    public function test_parent_sees_only_authorized_children_calendar_and_progress(): void
    {
        $parent = $this->parent(1);
        $students = $this->loginAs($parent)
            ->getJson(route('representante.api.estudiantes'))
            ->assertOk()
            ->json('students');

        $names = collect($students)->pluck('name');
        $this->assertTrue($names->contains('Alumno QA 01'));
        $this->assertTrue($names->contains('Alumno QA 02'));
        $this->assertFalse($names->contains('Alumno QA 03'));
        $this->assertFalse($names->contains('Alumno QA Other'));

        $studentId = (int) collect($students)->firstWhere('name', 'Alumno QA 01')['id'];

        $summary = $this->loginAs($parent)
            ->getJson(route('representante.api.resumen', $studentId))
            ->assertOk()
            ->json();
        $this->assertTrue((bool) ($summary['ok'] ?? false));
        $this->assertNotEmpty(data_get($summary, 'summary'));

        $calendar = $this->loginAs($parent)
            ->getJson(route('representante.api.calendario', ['estudiante' => $studentId, 'month' => now()->format('Y-m')]))
            ->assertOk()
            ->json();
        $this->assertNotEmpty(data_get($calendar, 'calendar') ?? $calendar);

        $subjects = $this->loginAs($parent)
            ->getJson(route('representante.api.materias', $studentId))
            ->assertOk()
            ->json();
        $this->assertNotEmpty(data_get($subjects, 'materias') ?? data_get($subjects, 'subjects') ?? $subjects);
    }

    public function test_parent_calendar_lists_seeded_activity_for_enrolled_child(): void
    {
        $parent = $this->parent(1);
        $student = Student::query()->where('name', 'Alumno QA 01')->firstOrFail();
        $this->assertGreaterThan(0, $student->courses()->count());
        $this->assertTrue(
            Activity::query()
                ->where('title', 'Tarea QA Matemática 1ro')
                ->whereIn('course_id', $student->courses()->pluck('courses.id'))
                ->exists()
        );

        $calendar = $this->loginAs($parent)
            ->getJson(route('representante.api.calendario', [
                'estudiante' => $student->id,
                'month' => now()->format('Y-m'),
            ]))
            ->assertOk()
            ->json('calendar');

        $this->assertGreaterThan(0, (int) ($calendar['total_events'] ?? 0));
        $titles = collect($calendar['events'] ?? [])->flatten(1)->pluck('title');
        $this->assertTrue(
            $titles->contains(fn ($title) => str_contains((string) $title, 'Tarea QA Matemática 1ro')),
            json_encode($titles, JSON_UNESCAPED_UNICODE)
        );

        $hub = $this->loginAs($parent)
            ->get(route('representante.dashboard'))
            ->assertOk();
        $this->assertMatchesRegularExpression('/"total_events"\s*:\s*[1-9]/', $hub->getContent());
        $hub->assertSee('Tarea QA', false);
    }

    public function test_parent_summary_counts_enrolled_active_courses(): void
    {
        $parent = $this->parent(1);
        $student = Student::query()->where('name', 'Alumno QA 01')->firstOrFail();
        $enrolled = (int) $student->courses()->count();
        $this->assertGreaterThan(0, $enrolled);

        $summary = $this->loginAs($parent)
            ->getJson(route('representante.api.resumen', $student->id))
            ->assertOk()
            ->json('summary');

        $this->assertSame($enrolled, (int) ($summary['courses_count'] ?? 0));

        $listed = $this->loginAs($parent)
            ->getJson(route('representante.api.estudiantes'))
            ->assertOk()
            ->json('students');
        $row = collect($listed)->firstWhere('name', 'Alumno QA 01');
        $this->assertNotNull($row);
        $this->assertSame($enrolled, (int) ($row['courses_count'] ?? 0));

        $hub = $this->loginAs($parent)
            ->get(route('representante.dashboard'))
            ->assertOk();
        $this->assertMatchesRegularExpression(
            '/courses_count["\']?\s*:\s*'.$enrolled.'/',
            $hub->getContent()
        );
    }

    public function test_parent_hub_shows_average_and_pending_when_sibling_fetch_empty(): void
    {
        $parent = $this->parent(1);
        $student = Student::query()->where('name', 'Alumno QA 01')->firstOrFail();
        $this->assertGreaterThan(0, $student->courses()->count());
        $this->assertGreaterThan(0, Grade::query()->where('student_id', $student->id)->whereNotNull('score')->count());

        $courseId = (int) $student->courses()->value('courses.id');
        Activity::create([
            'teacher_id' => $student->teacher_id,
            'course_id' => $courseId,
            'colegio_id' => $student->colegio_id,
            'title' => 'Tarea QA Pendiente Hub',
            'description' => 'Pendiente real para el KPI de entregas.',
            'due_date' => now()->addDays(5)->toDateString(),
            'type' => Activity::TYPE_TAREA,
            'is_homework' => true,
            'max_score' => 20,
            'weight_percentage' => 10,
        ]);

        $emptyEager = Student::query()->findOrFail($student->id);
        $emptyEager->setRelation('courses', collect());
        $this->assertTrue($emptyEager->relationLoaded('courses'));
        $this->assertCount(0, $emptyEager->courses);

        $service = app(RepresentanteDashboardService::class);
        $summary = $service->summary($emptyEager);
        $subjects = $service->subjects($emptyEager);

        $this->assertNotNull($summary['average']['value']);
        $this->assertGreaterThan(0, (float) $summary['average']['value']);
        $this->assertGreaterThan(0, (int) ($summary['pending_tasks']['count'] ?? 0));
        $this->assertTrue(
            collect($summary['pending_tasks']['items'] ?? [])->contains(
                fn ($item) => str_contains((string) ($item['title'] ?? ''), 'Tarea QA Pendiente Hub')
            ),
            json_encode($summary['pending_tasks'], JSON_UNESCAPED_UNICODE)
        );
        $this->assertNotEmpty($subjects);
        $this->assertNotNull($subjects[0]['average'] ?? null);

        $hub = $this->loginAs($parent)
            ->get(route('representante.dashboard'))
            ->assertOk();
        $html = $hub->getContent();
        $this->assertDoesNotMatchRegularExpression('/summary:\s*\{\s*courses_count:/', $html);
        $this->assertMatchesRegularExpression('/"value"\s*:\s*[1-9]/', $html);
        $hub->assertSee('Tarea QA Pendiente Hub', false);
        $this->assertMatchesRegularExpression('/subjects:\s*\[/', $html);
        $this->assertStringContainsString('"name":', $html);

        $resumen = $this->loginAs($parent)
            ->getJson(route('representante.api.resumen', $student->id))
            ->assertOk()
            ->json('summary');
        $this->assertNotNull($resumen['average']['value'] ?? null);
        $this->assertGreaterThan(0, (int) ($resumen['pending_tasks']['count'] ?? 0));
    }

    public function test_parent_hub_lists_subjects_when_sibling_fetch_empty(): void
    {
        $parent = $this->parent(1);
        $student = Student::query()->where('name', 'Alumno QA 01')->firstOrFail();
        $course = $student->courses()->with('teacher:id,name')->firstOrFail();
        $empty = Student::query()->findOrFail($student->id);
        $empty->setRelation('courses', collect());

        $detail = app(RepresentanteDashboardService::class)->subjectDetail($empty, $course);
        $this->assertSame($course->id, $detail['id']);
        $this->assertSame($course->subject_name, $detail['name']);
        $this->assertNotNull($detail['average']);
        $this->assertNotEmpty($detail['items']);

        $hub = $this->loginAs($parent)
            ->get(route('representante.dashboard'))
            ->assertOk();
        $html = $hub->getContent();
        $this->assertMatchesRegularExpression('/subjects:\s*\[\{/', $html);
        $this->assertTrue(
            str_contains($html, json_encode($course->subject_name))
            || str_contains($html, $course->subject_name),
            'Hub SSR should include enrolled subject names'
        );

        $this->loginAs($parent)
            ->getJson(route('representante.api.materias', $student->id))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonFragment(['name' => $course->subject_name]);

        $this->loginAs($parent)
            ->getJson(route('representante.api.materia', ['estudiante' => $student->id, 'materia' => $course->id]))
            ->assertOk()
            ->assertJsonPath('subject.id', $course->id)
            ->assertJsonPath('subject.name', $course->subject_name);
    }

    public function test_parent_contextual_ai_uses_real_activity_without_open_chatbot(): void
    {
        config()->set('services.openai.key', null);
        $parent = $this->parent(1);
        $student = Student::query()->where('name', 'Alumno QA 01')->firstOrFail();
        $activity = Activity::query()
            ->where('colegio_id', $student->colegio_id)
            ->whereIn('course_id', $student->courses()->pluck('courses.id'))
            ->where('type', Activity::TYPE_TAREA)
            ->firstOrFail();

        $explain = $this->loginAs($parent)
            ->postJson(route('representante.api.ia.actividad', $activity->id), [
                'estudiante_id' => $student->id,
            ])
            ->assertOk()
            ->json();

        $this->assertTrue((bool) ($explain['success'] ?? false), json_encode($explain));
        $this->assertSame('activity_explanation', $explain['action'] ?? null);
        $this->assertNotEmpty($explain['content'] ?? null);

        $this->loginAs($parent)
            ->postJson(route('representante.api.ia.calendario'), ['estudiante_id' => $student->id])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->loginAs($parent)
            ->postJson(route('representante.api.ia.calificaciones'), ['estudiante_id' => $student->id])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->loginAs($parent)
            ->postJson(route('representante.api.ia.asistencia'), ['estudiante_id' => $student->id])
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
