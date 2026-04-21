<?php

namespace App\Providers;

use App\Models\AttendanceRecord;
use App\Models\CourseClass;
use App\Models\Message;
use App\Models\TeacherResource;
use App\Models\User;
use App\Support\TuitionFinancialService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ParentDashboardDataProvider
{
    public function __construct(private TuitionFinancialService $tuitionFinancialService) {}

    /**
     * @return array<string, mixed>
     */
    public function forParent(User $parent, ?int $selectedChildId = null): array
    {
        $children = User::query()
            ->where('parent_id', $parent->id)
            ->whereNotNull('approved_at')
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'student');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'date_of_birth']);

        $childrenCards = $children
            ->values()
            ->map(function (User $child, int $index): array {
                $theme = $index % 2 === 0
                    ? ['color' => 'var(--lumina-child-1)', 'textColor' => 'var(--lumina-child-1-text)']
                    : ['color' => 'var(--lumina-child-2)', 'textColor' => 'var(--lumina-child-2-text)'];

                return [
                    'id' => (int) $child->id,
                    'name' => (string) $child->name,
                    'initials' => $child->initials(),
                    'grade' => 'Student',
                    'stream' => 'Language Track',
                    'color' => $theme['color'],
                    'textColor' => $theme['textColor'],
                ];
            })
            ->values()
            ->all();

        $selectedChild = collect($childrenCards)->firstWhere('id', (int) $selectedChildId);
        if (! $selectedChild) {
            $selectedChild = $childrenCards[0] ?? null;
        }

        $childDashboardData = [];
        foreach ($children as $child) {
            $childDashboardData[(int) $child->id] = $this->buildChildDashboardData($parent, $child);
        }

        $financialData = $this->tuitionFinancialService->buildParentPageData($parent);
        $payments = collect($financialData['invoices'] ?? [])
            ->map(fn (array $invoice): array => [
                'child' => (string) ($invoice['child'] ?? 'Student'),
                'term' => 'Outstanding',
                'amount' => (int) ($invoice['amount'] ?? 0),
            ])
            ->values()
            ->all();

        return [
            'children' => $childrenCards,
            'selectedChild' => $selectedChild,
            'childDashboardData' => $childDashboardData,
            'academicTerm' => $this->academicTermLabel(),
            'totalOutstanding' => (int) ($financialData['totalOutstanding'] ?? 0),
            'payments' => $payments,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildChildDashboardData(User $parent, User $child): array
    {
        $classIds = $this->resolveStudentClassIds($child);

        $classes = CourseClass::query()
            ->whereIn('id', $classIds)
            ->with([
                'course:id,name,code',
                'teacher:id,name',
                'schedules:id,class_id,day_of_week,start_time,end_time,room_id',
                'schedules.room:id,name',
            ])
            ->get();

        $teachers = $classes
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->values();

        $teacherGroupsById = [];
        foreach ($classes as $class) {
            $teacherId = (int) ($class->teacher?->id ?? 0);
            if ($teacherId <= 0) {
                continue;
            }

            $groupLabel = (string) ($class->course?->name ?? ('Group #'.$class->id));
            $teacherGroupsById[$teacherId] ??= [];
            $teacherGroupsById[$teacherId][] = $groupLabel;
        }

        [$weekStart, $weekEnd] = $this->currentFourWeekRange();

        $attendanceRecords = AttendanceRecord::query()
            ->where('student_id', $child->id)
            ->whereIn('class_id', $classIds)
            ->whereBetween('attendance_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('attendance_date')
            ->orderBy('id')
            ->get();

        $gradeSeries = $attendanceRecords
            ->filter(fn (AttendanceRecord $record): bool => ! is_null($record->grade))
            ->map(function (AttendanceRecord $record) use ($weekStart): array {
                $rawGrade = (float) $record->grade;
                $normalizedGrade = $rawGrade > 20 ? ($rawGrade / 5) : $rawGrade;

                return [
                    'week' => $this->weekNumberForDate($weekStart, $record->attendance_date),
                    'value' => round(max(0, min(20, $normalizedGrade)), 1),
                ];
            })
            ->values()
            ->all();

        $gradeProgress = collect($gradeSeries)
            ->pluck('value')
            ->map(fn ($value): float => round((float) $value, 1))
            ->values()
            ->all();

        if ($gradeProgress === []) {
            $fallbackGrades = AttendanceRecord::query()
                ->where('student_id', $child->id)
                ->whereIn('class_id', $classIds)
                ->whereNotNull('grade')
                ->select(['attendance_date', 'grade'])
                ->latest('attendance_date')
                ->take(8)
                ->reverse()
                ->get();

            $gradeSeries = $fallbackGrades
                ->map(function (AttendanceRecord $record) use ($weekStart): array {
                    $rawGrade = (float) $record->grade;
                    $normalizedGrade = $rawGrade > 20 ? ($rawGrade / 5) : $rawGrade;

                    return [
                        'week' => $this->weekNumberForDate($weekStart, $record->attendance_date),
                        'value' => round(max(0, min(20, $normalizedGrade)), 1),
                    ];
                })
                ->values()
                ->all();

            $gradeProgress = collect($gradeSeries)
                ->pluck('value')
                ->values()
                ->all();
        }

        $attendanceSeries = $attendanceRecords
            ->map(function (AttendanceRecord $record) use ($weekStart): array {
                $status = $this->normalizeAttendanceStatus((string) $record->status);

                return [
                    'week' => $this->weekNumberForDate($weekStart, $record->attendance_date),
                    'score' => $this->attendanceScore($status),
                    'status' => $status,
                ];
            })
            ->values()
            ->all();

        $attendanceProgress = collect($attendanceSeries)
            ->pluck('score')
            ->map(fn ($value): int => (int) $value)
            ->values()
            ->all();

        if ($attendanceProgress === []) {
            $fallbackAttendance = AttendanceRecord::query()
                ->where('student_id', $child->id)
                ->whereIn('class_id', $classIds)
                ->select(['attendance_date', 'status'])
                ->latest('attendance_date')
                ->take(8)
                ->reverse()
                ->get();

            $attendanceSeries = $fallbackAttendance
                ->map(function (AttendanceRecord $record) use ($weekStart): array {
                    $status = $this->normalizeAttendanceStatus((string) $record->status);

                    return [
                        'week' => $this->weekNumberForDate($weekStart, $record->attendance_date),
                        'score' => $this->attendanceScore($status),
                        'status' => $status,
                    ];
                })
                ->values()
                ->all();

            $attendanceProgress = collect($attendanceSeries)
                ->pluck('score')
                ->values()
                ->all();
        }

        $documentsCount = TeacherResource::query()
            ->whereIn('class_id', $classIds)
            ->where('category', TeacherResource::CATEGORY_HOMEWORK)
            ->count();

        $teacherIds = $teachers->pluck('id')->map(fn ($id): int => (int) $id)->values();

        $unreadMessagesCount = $teacherIds->isNotEmpty()
            ? Message::query()
                ->where('receiver_id', $parent->id)
                ->whereIn('sender_id', $teacherIds)
                ->whereNull('read_at')
                ->count()
            : 0;

        return [
            'timetable' => $this->buildTimetableRows($classes),
            'teacherFeedbacks' => $this->buildTeacherFeedbacks($classes, $attendanceRecords, $weekStart),
            'teachers' => $teachers->map(fn (User $teacher): array => [
                'id' => (int) $teacher->id,
                'name' => (string) $teacher->name,
                'group' => implode(', ', array_values(array_unique($teacherGroupsById[(int) $teacher->id] ?? ['Assigned Group']))),
                'messageUrl' => route('role.messages.conversation', ['role' => 'parent', 'conversation' => $teacher->id]),
            ])->values()->all(),
            'progress' => $gradeProgress,
            'progressSeries' => $gradeSeries,
            'attendanceProgress' => $attendanceProgress,
            'attendanceSeries' => $attendanceSeries,
            'documentsCount' => (int) $documentsCount,
            'teachersCount' => (int) $teachers->count(),
            'unreadMessagesCount' => (int) $unreadMessagesCount,
        ];
    }

    /**
     * @param  Collection<int, CourseClass>  $classes
     * @param  Collection<int, AttendanceRecord>  $attendanceRecords
     * @return array<int, array<string, mixed>>
     */
    private function buildTeacherFeedbacks(Collection $classes, Collection $attendanceRecords, CarbonInterface $weekStart): array
    {
        $classMap = $classes->keyBy('id');
        $feedbacks = [];

        foreach ($attendanceRecords as $record) {
            $class = $classMap->get((int) $record->class_id);
            if (! $class) {
                continue;
            }

            $feedbackText = trim((string) ($record->feedback ?? ''));
            if ($feedbackText === '' || is_null($record->grade)) {
                continue;
            }

            $teacherId = (int) ($class->teacher?->id ?? 0);
            if ($teacherId <= 0) {
                continue;
            }

            $grade = (int) $record->grade;
            $feedbacks[] = [
                'teacherId' => $teacherId,
                'teacher' => (string) ($class->teacher?->name ?? 'Teacher'),
                'course' => (string) ($class->course?->name ?? ('Class #'.$class->id)),
                'comment' => $feedbackText,
                'grade' => $grade,
                'tone' => $this->feedbackToneFromGrade($grade),
                'messageUrl' => route('role.messages.conversation', ['role' => 'parent', 'conversation' => $teacherId]),
                'week' => $this->weekNumberForDate($weekStart, $record->attendance_date),
                'recordedAt' => Carbon::parse((string) $record->attendance_date)->format('M d, Y'),
                'recordedAtTs' => Carbon::parse((string) $record->attendance_date)->timestamp,
            ];
        }

        if ($feedbacks === []) {
            foreach ($classes as $class) {
                $teacherId = (int) ($class->teacher?->id ?? 0);
                if ($teacherId <= 0) {
                    continue;
                }

                $records = $attendanceRecords->where('class_id', $class->id)->values();
                $graded = $records
                    ->filter(fn (AttendanceRecord $record): bool => ! is_null($record->grade))
                    ->pluck('grade')
                    ->map(fn ($value): float => (float) $value)
                    ->values();

                if ($graded->isEmpty()) {
                    continue;
                }

                $avgGrade = round($graded->avg(), 1);
                $latestRecord = $records
                    ->filter(fn (AttendanceRecord $record): bool => ! is_null($record->grade))
                    ->sortBy('attendance_date')
                    ->last();

                $feedbacks[] = [
                    'teacherId' => $teacherId,
                    'teacher' => (string) ($class->teacher?->name ?? 'Teacher'),
                    'course' => (string) ($class->course?->name ?? ('Class #'.$class->id)),
                    'comment' => $this->feedbackComment($avgGrade, null),
                    'grade' => (int) round($avgGrade),
                    'tone' => $this->feedbackToneFromGrade((int) round($avgGrade)),
                    'messageUrl' => route('role.messages.conversation', ['role' => 'parent', 'conversation' => $teacherId]),
                    'week' => $latestRecord
                        ? $this->weekNumberForDate($weekStart, $latestRecord->attendance_date)
                        : 4,
                    'recordedAt' => $latestRecord?->attendance_date
                        ? Carbon::parse((string) $latestRecord->attendance_date)->format('M d, Y')
                        : 'No recent session',
                    'recordedAtTs' => $latestRecord?->attendance_date
                        ? Carbon::parse((string) $latestRecord->attendance_date)->timestamp
                        : 0,
                ];
            }
        }

        usort($feedbacks, fn (array $a, array $b): int => ($b['recordedAtTs'] ?? 0) <=> ($a['recordedAtTs'] ?? 0));

        return array_map(function (array $feedback): array {
            unset($feedback['recordedAtTs']);

            return $feedback;
        }, $feedbacks);
    }

    private function feedbackToneFromGrade(int $grade): string
    {
        return $grade >= 10 ? 'good' : 'bad';
    }

    private function feedbackRating(?float $avgGrade, ?int $attendanceRate): int
    {
        $gradeComponent = is_null($avgGrade) ? 3.2 : max(1.0, min(5.0, ($avgGrade / 20) * 5));
        $attendanceComponent = is_null($attendanceRate) ? 3.4 : max(1.0, min(5.0, ($attendanceRate / 100) * 5));

        return (int) round(($gradeComponent * 0.65) + ($attendanceComponent * 0.35));
    }

    private function feedbackComment(?float $avgGrade, ?int $attendanceRate): string
    {
        if (! is_null($avgGrade) && $avgGrade >= 15 && (! is_null($attendanceRate) && $attendanceRate >= 85)) {
            return 'Excellent consistency in class participation and assessment performance. Keep this momentum.';
        }

        if (! is_null($avgGrade) && $avgGrade >= 12) {
            return 'Good academic progress overall. More revision before assessments can raise results further.';
        }

        if (! is_null($attendanceRate) && $attendanceRate < 70) {
            return 'Attendance is impacting continuity. Improving weekly presence will quickly improve outcomes.';
        }

        return 'Steady progress observed. Focus on assignment completion and regular practice to strengthen performance.';
    }

    /**
     * @return list<int>
     */
    private function resolveStudentClassIds(User $child): array
    {
        $enrolled = $child->enrolledClasses()->pluck('classes.id');

        $attendance = AttendanceRecord::query()
            ->where('student_id', $child->id)
            ->pluck('class_id');

        return $enrolled
            ->merge($attendance)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, CourseClass>  $classes
     * @return array<int, array{day:string,time:string,course:string,room:string}>
     */
    private function buildTimetableRows(Collection $classes): array
    {
        $rows = [];

        foreach ($classes as $class) {
            foreach ($class->schedules as $schedule) {
                $rows[] = [
                    'day' => $this->normalizeDayLabel((string) $schedule->day_of_week),
                    'time' => $this->formatScheduleTime($schedule->start_time, $schedule->end_time),
                    'course' => (string) ($class->course?->name ?? ('Class #'.$class->id)),
                    'room' => (string) ($schedule->room?->name ?? 'TBA'),
                    'timeOrder' => $this->timeToMinutes((string) $schedule->start_time),
                ];
            }
        }

        $dayOrder = [
            'Saturday' => 0,
            'Sunday' => 1,
            'Monday' => 2,
            'Tuesday' => 3,
            'Wednesday' => 4,
            'Thursday' => 5,
            'Friday' => 6,
        ];

        usort($rows, function (array $a, array $b) use ($dayOrder): int {
            $dayDiff = ($dayOrder[$a['day']] ?? 99) <=> ($dayOrder[$b['day']] ?? 99);
            if ($dayDiff !== 0) {
                return $dayDiff;
            }

            return ($a['timeOrder'] ?? 9999) <=> ($b['timeOrder'] ?? 9999);
        });

        return array_map(function (array $row): array {
            unset($row['timeOrder']);

            return $row;
        }, $rows);
    }

    private function normalizeDayLabel(string $day): string
    {
        $normalized = strtolower(trim($day));

        return match ($normalized) {
            'sat', 'saturday' => 'Saturday',
            'sun', 'sunday' => 'Sunday',
            'mon', 'monday' => 'Monday',
            'tue', 'tues', 'tuesday' => 'Tuesday',
            'wed', 'wednesday' => 'Wednesday',
            'thu', 'thur', 'thurs', 'thursday' => 'Thursday',
            'fri', 'friday' => 'Friday',
            default => ucfirst($normalized),
        };
    }

    private function formatScheduleTime(mixed $startTime, mixed $endTime): string
    {
        $start = $this->normalizeTimeString($startTime);
        $end = $this->normalizeTimeString($endTime);

        return $start.' - '.$end;
    }

    private function normalizeTimeString(mixed $time): string
    {
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i');
        }

        $timeString = trim((string) $time);

        if ($timeString === '') {
            return '--:--';
        }

        if (preg_match('/^(\d{1,2}):(\d{2})/', $timeString, $matches) === 1) {
            return str_pad((string) ((int) $matches[1]), 2, '0', STR_PAD_LEFT).':'.$matches[2];
        }

        return '--:--';
    }

    private function timeToMinutes(string $time): int
    {
        if (preg_match('/^(\d{1,2}):(\d{2})/', trim($time), $matches) !== 1) {
            return 9999;
        }

        return (((int) $matches[1]) * 60) + ((int) $matches[2]);
    }

    /**
        * @return array{0:CarbonInterface,1:CarbonInterface}
     */
    private function currentFourWeekRange(): array
    {
        $start = now()->startOfWeek(Carbon::SATURDAY)->subWeeks(3);
        $end = now()->startOfWeek(Carbon::SATURDAY)->copy()->endOfWeek(Carbon::FRIDAY);

        return [$start, $end];
    }

    private function academicTermLabel(): string
    {
        $month = (int) now()->format('n');
        $year = (int) now()->format('Y');

        if ($month >= 9) {
            return 'Fall '.$year;
        }

        if ($month >= 2) {
            return 'Spring '.$year;
        }

        return 'Winter '.$year;
    }

    private function attendanceScore(string $status): int
    {
        return match (strtolower(trim($status))) {
            'present' => 100,
            'late' => 60,
            'absent' => 20,
            default => 40,
        };
    }

    private function normalizeAttendanceStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'present' => 'present',
            'late' => 'late',
            'absent' => 'absent',
            default => 'unknown',
        };
    }

    private function weekNumberForDate(CarbonInterface $rangeStart, mixed $date): int
    {
        if (! $date) {
            return 4;
        }

        $dateValue = $date instanceof CarbonInterface ? $date->copy() : Carbon::parse((string) $date);
        $start = $rangeStart->copy()->startOfDay();
        $target = $dateValue->copy()->startOfDay();
        $diffDays = $start->diffInDays($target, false);
        $diffWeeks = (int) floor($diffDays / 7);
        $week = $diffWeeks + 1;

        return max(1, min(4, $week));
    }
}
