<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use App\Support\DashboardDataProvider;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __construct(private DashboardDataProvider $dashboardDataProvider) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $selectedMentorId = (int) $request->query('mentor', 0);
        $latestCard = $user->latestStudentCard();
        $currentCard = $user->currentStudentCard();

        $card = $currentCard ?? $latestCard;

        $approvedAt = $user->approved_at instanceof CarbonInterface
            ? $user->approved_at
            : ($user->approved_at ? Carbon::parse($user->approved_at) : null);

        $registrationYear = (int) ($approvedAt?->year ?? now()->year);
        $academicYear = $this->getAcademicYearString($registrationYear);
        $validFrom = $approvedAt
            ? $approvedAt->copy()->startOfDay()
            : ($card?->valid_from ? Carbon::parse($card->valid_from)->startOfDay() : null);
        $validTo = $validFrom
            ? $validFrom->copy()->addMonths(6)
            : ($card?->valid_to ? Carbon::parse($card->valid_to)->startOfDay() : null);
        $enrollmentDate = ($approvedAt ?? now())->format('F Y');

        $studentBirthday = $user->date_of_birth
            ? Carbon::parse($user->date_of_birth)->format('F d, Y')
            : 'Not set';

        $studentAge = $user->student_age;

        $today = now()->startOfDay();
        $isWithinValidityRange = $validFrom
            && $validTo
            && $today->betweenIncluded($validFrom, $validTo);

        $isSuspended = $card?->status === 'suspended';
        $cardStatus = ($isWithinValidityRange && ! $isSuspended) ? 'valid' : 'invalid';
        $studentStatus = ($isWithinValidityRange && ! $isSuspended) ? 'active' : 'inactive';

        // Initialize with placeholder data (safe fallbacks)
        $enrolledCoursesCount = 4;
        $nextClass = null;
        $nextClassMinutes = 42;
        $nextClassCountdown = '42Min';
        $nextClassStartsAt = null;
        $nextClassRoomName = 'Lecture Hall 4';
        $enrolledClasses = collect();
        $assignedCourseNames = [];
        $mentors = collect();
        $selectedMentor = null;
        $popularCourses = collect();

        try {
            $enrolledCoursesCount = CourseClass::whereHas('students', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count() ?: 4;

            $enrolledClasses = CourseClass::whereHas('students', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['course', 'teacher', 'schedules.room'])->get();

            $assignedCourseNames = $enrolledClasses
                ->map(fn (CourseClass $courseClass): string => (string) ($courseClass->course?->name ?? ''))
                ->filter(fn (string $name): bool => $name !== '')
                ->unique()
                ->values()
                ->all();

            $nextClassData = $this->resolveNextClassData($enrolledClasses);

            if ($nextClassData !== null) {
                $nextClass = $nextClassData['class'];
                $nextClassMinutes = $nextClassData['minutes'];
                $nextClassCountdown = $nextClassData['countdown'];
                $nextClassStartsAt = $nextClassData['startsAt'];
                $nextClassRoomName = $nextClassData['room'];
            }
        } catch (QueryException $e) {
            // Classes table doesn't exist yet
        }

        try {
            $mentors = $enrolledClasses
                ->map(fn (CourseClass $courseClass): ?User => $courseClass->teacher)
                ->filter()
                ->unique('id')
                ->values()
                ->map(function (User $teacher) use ($enrolledClasses): array {
                    $teacherCourses = $enrolledClasses
                        ->where('teacher_id', $teacher->id)
                        ->map(fn (CourseClass $courseClass): string => (string) ($courseClass->course?->name ?? 'Course'))
                        ->unique()
                        ->values()
                        ->all();

                    return [
                        'id' => (int) $teacher->id,
                        'name' => (string) $teacher->name,
                        'avatar' => $teacher->avatar ?? null,
                        'phone' => (string) ($teacher->phone ?? ''),
                        'email' => (string) ($teacher->email ?? ''),
                        'preferredLanguage' => (string) ($teacher->preferred_language ?? ''),
                        'bio' => trim((string) ($teacher->bio ?? '')),
                        'courses' => $teacherCourses,
                        'specialty' => $teacherCourses[0] ?? 'Specialist',
                        'messageUrl' => route('role.messages.conversation', ['role' => 'student', 'conversation' => $teacher->id]),
                    ];
                });

            $selectedMentor = $mentors->firstWhere('id', $selectedMentorId) ?: $mentors->first();
        } catch (\Exception $e) {
        }

        try {
            $popularCourses = $this->popularCourses();
        } catch (QueryException $e) {
        }

        $proficiencyData = $this->buildLanguageProficiencyData($user, $enrolledClasses);
        $defaultProficiency = $proficiencyData['default'];

        // Merge base dashboard data with student-specific data
        $baseData = $this->dashboardDataProvider->forUser($user);

        return view('student.dashboard', array_merge($baseData, [
            'user' => $user,
            'studentId' => $card?->card_number,
            'cardNumber' => $card?->card_number,
            'cardValidFrom' => $validFrom?->format('M Y'),
            'cardValidUntil' => $validTo?->format('M Y'),
            'cardStatus' => $cardStatus,
            'studentStatus' => $studentStatus,
            'academicYear' => $academicYear,
            'registrationYear' => (string) $registrationYear,
            'studentAge' => $studentAge,
            'studentBirthday' => $studentBirthday,
            'enrollmentDate' => $enrollmentDate,
            'enrolledCoursesCount' => $enrolledCoursesCount,
            'assignedCourseNames' => $assignedCourseNames,
            'completedLessonsCount' => 24,
            'courseProgressPercent' => 75,
            'lessonProgressPercent' => 60,
            'nextClass' => $nextClass,
            'nextClassMinutes' => $nextClassMinutes,
            'nextClassCountdown' => $nextClassCountdown,
            'nextClassStartsAt' => $nextClassStartsAt,
            'nextClassRoomName' => $nextClassRoomName,
            'proficiencyLevel' => $defaultProficiency['targetLevel'],
            'proficiencyPercent' => $defaultProficiency['percent'],
            'proficiencyStatus' => $defaultProficiency['status'],
            'proficiencyInsight' => $defaultProficiency['insight'],
            'proficiencyGroups' => $proficiencyData['groups'],
            'selectedProficiencyGroup' => $defaultProficiency['key'],
            'academicStatus' => 'Academic Excellence',
            'mentors' => $mentors,
            'selectedMentor' => $selectedMentor,
            'popularCourses' => $popularCourses,
        ]));
    }

    /**
     * @param  Collection<int, CourseClass>  $enrolledClasses
     * @return array{class:CourseClass,minutes:int,countdown:string,startsAt:string,room:string}|null
     */
    private function resolveNextClassData(Collection $enrolledClasses): ?array
    {
        $now = now();
        $next = null;

        foreach ($enrolledClasses as $courseClass) {
            foreach ($courseClass->schedules as $schedule) {
                $startAt = $this->nextOccurrenceForSchedule((string) $schedule->day_of_week, $schedule->start_time, $now);

                if ($startAt === null) {
                    continue;
                }

                if ($next === null || $startAt->lt($next['startAt'])) {
                    $next = [
                        'class' => $courseClass,
                        'startAt' => $startAt,
                        'room' => (string) ($schedule->room?->name ?? 'TBA'),
                    ];
                }
            }
        }

        if ($next === null) {
            return null;
        }

        return [
            'class' => $next['class'],
            'minutes' => (int) max(0, floor($now->diffInMinutes($next['startAt']))),
            'countdown' => $this->formatCountdown($now, $next['startAt']),
            'startsAt' => $next['startAt']->toIso8601String(),
            'room' => $next['room'],
        ];
    }

    private function formatCountdown(CarbonInterface $now, CarbonInterface $target): string
    {
        $totalMinutes = (int) max(0, floor($now->diffInMinutes($target)));

        if ($totalMinutes === 0) {
            return 'Now';
        }

        $days = intdiv($totalMinutes, 1440);
        $remainingAfterDays = $totalMinutes % 1440;
        $hours = intdiv($remainingAfterDays, 60);
        $minutes = $remainingAfterDays % 60;

        if ($days > 0) {
            return $days.'D:'.$hours.'H:'.$minutes.'Min';
        }

        if ($hours > 0) {
            return $hours.'H:'.$minutes.'Min';
        }

        return $minutes.'Min';
    }

    private function nextOccurrenceForSchedule(string $dayOfWeek, mixed $startTime, CarbonInterface $now): ?CarbonInterface
    {
        $dayMap = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        $dayKey = strtolower(trim($dayOfWeek));

        if (! isset($dayMap[$dayKey])) {
            return null;
        }

        if ($startTime instanceof \DateTimeInterface) {
            $hour = (int) $startTime->format('H');
            $minute = (int) $startTime->format('i');
            $second = (int) $startTime->format('s');
        } else {
            $timeRaw = trim((string) $startTime);

            if ($timeRaw === '') {
                return null;
            }

            if (! preg_match('/(\d{1,2}):(\d{2})(?::(\d{2}))?/', $timeRaw, $matches)) {
                return null;
            }

            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            $second = (int) ($matches[3] ?? 0);
        }

        $daysUntil = ($dayMap[$dayKey] - $now->dayOfWeek + 7) % 7;

        $candidate = $now->copy()
            ->startOfDay()
            ->addDays($daysUntil)
            ->setTime($hour, $minute, $second);

        if ($candidate->lte($now)) {
            $candidate = $candidate->addWeek();
        }

        return $candidate;
    }

    private function getAcademicYearString(int $registrationYear): string
    {
        return $registrationYear.'/'.($registrationYear + 1);
    }

    /**
     * @return Collection<int, Course>
     */
    private function popularCourses(int $limit = 4): Collection
    {
        return Course::query()
            ->select('courses.*')
            ->selectRaw('COUNT(DISTINCT class_student.user_id) as assigned_students_count')
            ->join('classes', 'classes.course_id', '=', 'courses.id')
            ->join('class_student', 'class_student.class_id', '=', 'classes.id')
            ->groupBy([
                'courses.id',
                'courses.name',
                'courses.code',
                'courses.description',
                'courses.created_at',
                'courses.updated_at',
            ])
            ->when($this->courseColumnExists('price'), fn ($query) => $query->groupBy('courses.price'))
            ->when($this->courseColumnExists('program_id'), fn ($query) => $query->groupBy('courses.program_id'))
            ->orderByDesc(DB::raw('assigned_students_count'))
            ->orderBy('courses.name')
            ->limit($limit)
            ->get();
    }

    private function courseColumnExists(string $column): bool
    {
        static $columns = null;

        $columns ??= (new Course)->getConnection()->getSchemaBuilder()->getColumnListing('courses');

        return in_array($column, $columns, true);
    }

    /**
     * @param  Collection<int, CourseClass>  $enrolledClasses
     * @return array{groups:list<array{key:string,label:string,displayName:string,percent:int,targetLevel:string,status:string,insight:string,count:int}>,default:array{key:string,label:string,displayName:string,percent:int,targetLevel:string,status:string,insight:string,count:int}}
     */
    private function buildLanguageProficiencyData(User $student, Collection $enrolledClasses): array
    {
        $groupMeta = collect([
            'all' => ['key' => 'all', 'label' => 'All Groups'],
        ]);

        foreach ($enrolledClasses as $courseClass) {
            $meta = $this->resolveGroupMetaFromClass($courseClass);
            $groupMeta->put($meta['key'], $meta);
        }

        $records = AttendanceRecord::query()
            ->where('student_id', $student->id)
            ->whereNotNull('grade')
            ->with(['courseClass.course'])
            ->get();

        $scoresByGroup = collect([
            'all' => collect(),
        ]);

        foreach ($records as $record) {
            $normalized = $this->normalizeGradeToPercent($record->grade);

            if ($normalized === null) {
                continue;
            }

            $scoresByGroup['all']->push($normalized);

            $meta = $this->resolveGroupMetaFromClass($record->courseClass);
            $groupMeta->put($meta['key'], $meta);

            if (! $scoresByGroup->has($meta['key'])) {
                $scoresByGroup->put($meta['key'], collect());
            }

            $scoresByGroup[$meta['key']]->push($normalized);
        }

        $groups = $groupMeta
            ->values()
            ->map(function (array $meta) use ($scoresByGroup): array {
                $scores = $scoresByGroup->get($meta['key'], collect());
                $count = (int) $scores->count();
                $percent = $count > 0
                    ? (int) round((float) $scores->avg())
                    : 0;

                $band = $this->resolveProficiencyBand($percent, $count > 0);
                $expectedLevel = $this->extractCefrLevelFromText($meta['label']);
                $targetLevel = $expectedLevel ?? $band['targetLevel'];

                return [
                    'key' => $meta['key'],
                    'label' => $meta['label'],
                    'displayName' => $meta['label'],
                    'percent' => $percent,
                    'targetLevel' => $targetLevel,
                    'status' => $band['status'],
                    'insight' => $band['insight'],
                    'count' => $count,
                ];
            })
            ->sortBy(fn (array $group): int => $group['key'] === 'all' ? 0 : 1)
            ->values()
            ->all();

        $default = collect($groups)->firstWhere('key', 'all') ?? ($groups[0] ?? [
            'key' => 'all',
            'label' => 'All Groups',
            'displayName' => 'All Groups',
            'percent' => 0,
            'targetLevel' => 'A1',
            'status' => 'No Data',
            'insight' => 'No graded evaluations yet. Complete class evaluations to track progress.',
            'count' => 0,
        ]);

        return [
            'groups' => $groups,
            'default' => $default,
        ];
    }

    private function normalizeGradeToPercent(mixed $grade): ?int
    {
        if ($grade === null || $grade === '') {
            return null;
        }

        $value = (float) $grade;

        if (! is_finite($value)) {
            return null;
        }

        if ($value <= 0) {
            return 0;
        }

        if ($value <= 10) {
            return (int) round(min(100, $value * 10));
        }

        if ($value <= 20) {
            return (int) round(min(100, $value * 5));
        }

        return (int) round(min(100, $value));
    }

    /**
     * @return array{key:string,label:string}
     */
    private function resolveGroupMetaFromClass(?CourseClass $courseClass): array
    {
        $courseName = strtolower(trim((string) ($courseClass?->course?->name ?? '')));
        $courseCode = strtolower(trim((string) ($courseClass?->course?->code ?? '')));
        $composite = trim($courseName.' '.$courseCode);

        if (str_contains($composite, 'ielts')) {
            return ['key' => 'ielts-preparation', 'label' => 'IELTS Preparation'];
        }

        if (str_contains($composite, 'vip') || str_contains($composite, 'v.i.p')) {
            return ['key' => 'vip', 'label' => 'VIP'];
        }

        if (preg_match('/group\s*([a-z0-9]+)/i', $composite, $matches) === 1) {
            $suffix = strtoupper((string) $matches[1]);

            return [
                'key' => 'group-'.$suffix,
                'label' => 'Group '.$suffix,
            ];
        }

        $fallback = trim((string) ($courseClass?->course?->name ?? 'General'));

        return [
            'key' => Str::slug($fallback) ?: 'general',
            'label' => $fallback !== '' ? $fallback : 'General',
        ];
    }

    /**
     * @return array{targetLevel:string,status:string,insight:string}
     */
    private function resolveProficiencyBand(int $percent, bool $hasData): array
    {
        if (! $hasData) {
            return [
                'targetLevel' => 'A1',
                'status' => 'No Data',
                'insight' => 'No graded evaluations yet. Complete class evaluations to track progress.',
            ];
        }

        if ($percent < 20) {
            return [
                'targetLevel' => 'A2',
                'status' => 'Starter',
                'insight' => 'Build vocabulary and attendance consistency to raise early momentum.',
            ];
        }

        if ($percent < 40) {
            return [
                'targetLevel' => 'B1',
                'status' => 'Developing',
                'insight' => 'Solid base achieved. Focus on grammar accuracy and writing structure.',
            ];
        }

        if ($percent < 60) {
            return [
                'targetLevel' => 'B2',
                'status' => 'Intermediate',
                'insight' => 'Keep strengthening comprehension and spontaneous speaking practice.',
            ];
        }

        if ($percent < 80) {
            return [
                'targetLevel' => 'C1',
                'status' => 'Advanced',
                'insight' => 'Focus on specialized terminology and argument precision.',
            ];
        }

        return [
            'targetLevel' => 'C1',
            'status' => 'Advanced+',
            'insight' => 'Excellent performance. Maintain consistency to fully close the proficiency circle.',
        ];
    }

    private function extractCefrLevelFromText(string $text): ?string
    {
        if (preg_match('/\b(A1|A2|B1|B2|C1|C2)\b/i', $text, $matches) !== 1) {
            return null;
        }

        return strtoupper((string) $matches[1]);
    }
}
