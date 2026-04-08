<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use App\Models\TeacherResource;
use App\Models\User;
use App\Support\DashboardDataProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function __construct(private DashboardDataProvider $dashboardDataProvider) {}

    public function index(Request $request): View
    {
        $teacher = $request->user();
        $dashboardData = $this->dashboardDataProvider->forUser($teacher);

        $taughtClasses = $teacher->taughtClasses()
            ->with([
                'course:id,name',
                'schedules:id,class_id,day_of_week,start_time,end_time,room_id',
                'schedules.room:id,name',
            ])
            ->withCount('students')
            ->get();

        $classIds = $taughtClasses->pluck('id');

        $activeClasses = $taughtClasses->count();
        $totalClassesPerWeek = (int) $taughtClasses->sum(
            fn (CourseClass $courseClass): int => $courseClass->schedules->count()
        );

        $totalStudents = $classIds->isEmpty()
            ? 0
            : (int) User::query()
                ->role('student')
                ->whereHas('enrolledClasses', function ($query) use ($classIds): void {
                    $query->whereIn('classes.id', $classIds);
                })
                ->distinct('users.id')
                ->count('users.id');

        $resources = TeacherResource::query()
            ->where('teacher_id', $teacher->id)
            ->get(['category', 'download_count']);

        $totalResources = $resources->count();
        $homeworksCount = $resources
            ->where('category', TeacherResource::CATEGORY_HOMEWORK)
            ->count();
        $materialsCount = $resources
            ->where('category', TeacherResource::CATEGORY_COURSE_MATERIALS)
            ->count();
        $resourceDownloads = (int) $resources->sum('download_count');

        return view('teacher.dashboard', [
            ...$dashboardData,
            'user' => $teacher,
            'totalStudents' => $totalStudents,
            'activeClasses' => $activeClasses,
            'totalClassesPerWeek' => $totalClassesPerWeek,
            'weeklySchedule' => $this->buildWeeklySchedule($taughtClasses),
            'quickResources' => [
                ['name' => 'Resources ('.$totalResources.')', 'icon' => 'template', 'color' => '#EFF6FF', 'iconColor' => '#3B82F6'],
                ['name' => 'Homeworks ('.$homeworksCount.')', 'icon' => 'lightning', 'color' => '#FEF3C7', 'iconColor' => '#D97706'],
                ['name' => 'Downloads ('.$resourceDownloads.')', 'icon' => 'sparkles', 'color' => '#F3E8FF', 'iconColor' => '#9333EA'],
                ['name' => 'Materials ('.$materialsCount.')', 'icon' => 'book', 'color' => '#ECFDF5', 'iconColor' => '#059669'],
            ],
        ]);
    }

    /**
     * @param  Collection<int, CourseClass>  $taughtClasses
     * @return array<string, array<int, array{name:string,room:string,color:string,border:string}>>
     */
    private function buildWeeklySchedule(Collection $taughtClasses): array
    {
        $weeklySchedule = [
            'SATURDAY' => [],
            'SUNDAY' => [],
            'MONDAY' => [],
            'TUESDAY' => [],
            'WEDNESDAY' => [],
            'THURSDAY' => [],
            'FRIDAY' => [],
        ];

        $colors = [
            ['color' => '#D1FAE5', 'border' => '#10B981'],
            ['color' => '#E0E7FF', 'border' => '#6366F1'],
            ['color' => '#DBEAFE', 'border' => '#3B82F6'],
            ['color' => '#FEE2E2', 'border' => '#EF4444'],
        ];

        foreach ($taughtClasses as $index => $courseClass) {
            $style = $colors[$index % count($colors)];

            foreach ($courseClass->schedules as $schedule) {
                $dayKey = strtoupper((string) $schedule->day_of_week);

                if (! array_key_exists($dayKey, $weeklySchedule)) {
                    continue;
                }

                $slotIndex = $this->resolveSlotIndex($schedule->start_time);

                if ($slotIndex === null) {
                    continue;
                }

                $weeklySchedule[$dayKey][$slotIndex] = [
                    'name' => (string) ($courseClass->course?->name ?? 'Class #'.$courseClass->id),
                    'room' => (string) ($schedule->room?->name ?? 'TBA'),
                    'color' => $style['color'],
                    'border' => $style['border'],
                ];
            }
        }

        return $weeklySchedule;
    }

    private function resolveSlotIndex(mixed $startTime): ?int
    {
        if ($startTime instanceof \DateTimeInterface) {
            $hour = (int) $startTime->format('H');
            $minute = (int) $startTime->format('i');
        } else {
            $time = trim((string) $startTime);

            if ($time === '') {
                return null;
            }

            [$hour, $minute] = array_map('intval', explode(':', $time));
        }

        $minutesFromMidnight = ($hour * 60) + $minute;
        $slotStarts = [480, 570, 660, 750, 840, 930];

        foreach ($slotStarts as $index => $slotStart) {
            if ($minutesFromMidnight >= $slotStart && $minutesFromMidnight < $slotStart + 90) {
                return $index;
            }
        }

        return null;
    }
}
