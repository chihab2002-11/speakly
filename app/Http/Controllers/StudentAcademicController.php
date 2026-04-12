<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\CourseClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StudentAcademicController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();

        $enrolledClasses = $student->enrolledClasses()
            ->with([
                'course:id,name,code',
                'schedules:id,class_id,day_of_week,start_time,end_time,room_id',
                'schedules.room:id,name',
            ])
            ->get();

        $attendanceRecords = AttendanceRecord::query()
            ->where('student_id', $student->id)
            ->whereIn('class_id', $enrolledClasses->pluck('id'))
            ->with(['courseClass.course:id,name,code', 'courseClass.teacher:id,name'])
            ->orderByDesc('attendance_date')
            ->get();

        $classesPerWeek = (int) $enrolledClasses->sum(
            fn (CourseClass $courseClass): int => $courseClass->schedules->count()
        );

        $hoursPerWeek = (int) round($enrolledClasses->sum(function (CourseClass $courseClass): float {
            return $courseClass->schedules->sum(function ($schedule): float {
                if (! $schedule->start_time || ! $schedule->end_time) {
                    return 0;
                }

                $start = strtotime((string) $schedule->start_time);
                $end = strtotime((string) $schedule->end_time);

                if (! $start || ! $end || $end <= $start) {
                    return 0;
                }

                return ($end - $start) / 3600;
            });
        }));

        return view('student.academic', [
            'user' => $student,
            'weeklySchedule' => $this->buildWeeklySchedule($enrolledClasses),
            'attendanceWeeks' => $this->buildAttendanceWeeks($enrolledClasses, $attendanceRecords),
            'currentStreak' => 12,
            'evaluations' => $this->buildEvaluations($attendanceRecords),
            'classesPerWeek' => $classesPerWeek,
            'hoursPerWeek' => $hoursPerWeek,
        ]);
    }

    /**
     * @param  Collection<int, CourseClass>  $enrolledClasses
     * @return array<string, array<int, array{name:string,room:string,color:string,border:string}>>
     */
    private function buildWeeklySchedule(Collection $enrolledClasses): array
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

        foreach ($enrolledClasses as $index => $courseClass) {
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

                if (isset($weeklySchedule[$dayKey][$slotIndex])) {
                    $existingName = $weeklySchedule[$dayKey][$slotIndex]['name'];

                    $weeklySchedule[$dayKey][$slotIndex] = [
                        'name' => 'Conflict: '.$existingName,
                        'room' => 'Multiple classes',
                        'color' => '#FEE2E2',
                        'border' => '#EF4444',
                    ];

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

    /**
     * @param  Collection<int, CourseClass>  $enrolledClasses
     * @param  Collection<int, AttendanceRecord>  $attendanceRecords
    * @return array<int, array{label:string,startLabel:string,weekStart:string,days:array<int, array{name:string,date:string,today:bool,classes:array<int, array{lang:string,group:string,time:string,status:string,timeOrder:int}>}>}>
     */
    private function buildAttendanceWeeks(Collection $enrolledClasses, Collection $attendanceRecords): array
    {
        if ($attendanceRecords->isEmpty()) {
            return [];
        }

        $scheduleLookup = [];

        foreach ($enrolledClasses as $courseClass) {
            foreach ($courseClass->schedules as $schedule) {
                $dayKey = strtolower((string) $schedule->day_of_week);

                $scheduleLookup[$courseClass->id][$dayKey][] = [
                    'start' => (string) $schedule->start_time,
                    'end' => (string) $schedule->end_time,
                ];
            }
        }

        $weeks = [];
        $weekStarts = $attendanceRecords
            ->filter(fn (AttendanceRecord $record): bool => (bool) $record->attendance_date)
            ->map(fn (AttendanceRecord $record): string => $record->attendance_date->copy()->startOfWeek()->toDateString())
            ->unique()
            ->sort()
            ->values();

        foreach ($weekStarts as $index => $weekStartDate) {
            $weekStart = Carbon::parse($weekStartDate)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            $weekRecords = $attendanceRecords->filter(function (AttendanceRecord $record) use ($weekStart, $weekEnd): bool {
                if (! $record->attendance_date) {
                    return false;
                }

                $date = $record->attendance_date;

                return $date->betweenIncluded($weekStart, $weekEnd);
            });

            $days = [];

            for ($day = 0; $day < 7; $day++) {
                $date = $weekStart->copy()->addDays($day);

                $dayRecords = $weekRecords
                    ->filter(fn (AttendanceRecord $record): bool => $record->attendance_date?->isSameDay($date) ?? false)
                    ->values();

                $classes = $dayRecords->map(function (AttendanceRecord $record) use ($scheduleLookup, $date): array {
                    $dayKey = strtolower((string) $date->format('l'));
                    $timeSlots = $scheduleLookup[$record->class_id][$dayKey] ?? [];
                    $slot = $timeSlots[0] ?? null;

                    $startText = $slot ? $this->formatShortTime($slot['start']) : '--:--';
                    $endText = $slot ? $this->formatShortTime($slot['end']) : '--:--';

                    $timeOrder = $slot ? $this->timeToMinutes($slot['start']) : 9999;
                    $courseName = (string) ($record->courseClass?->course?->name ?? ('Class #'.$record->class_id));
                    $teacherName = (string) ($record->courseClass?->teacher?->name ?? 'Teacher TBA');

                    return [
                        'lang' => $courseName,
                        'group' => $teacherName,
                        'time' => $startText.' - '.$endText,
                        'status' => (string) $record->status,
                        'timeOrder' => $timeOrder,
                    ];
                })
                    ->sortBy('timeOrder')
                    ->values()
                    ->all();

                $days[] = [
                    'name' => (string) $date->format('l'),
                    'date' => (string) $date->format('M j'),
                    'today' => $date->isSameDay(now()),
                    'classes' => $classes,
                ];
            }

            $weeks[] = [
                'label' => 'Week '.($index + 1),
                'startLabel' => $weekStart->format('M j').' - '.$weekEnd->format('M j, Y'),
                'weekStart' => $weekStart->toDateString(),
                'days' => $days,
            ];
        }

        return $weeks;
    }

    private function formatShortTime(string $time): string
    {
        if (! preg_match('/(\d{1,2}):(\d{2})/', $time, $matches)) {
            return '--:--';
        }

        return str_pad((string) ((int) $matches[1]), 2, '0', STR_PAD_LEFT).':'.$matches[2];
    }

    private function timeToMinutes(string $time): int
    {
        if (! preg_match('/(\d{1,2}):(\d{2})/', $time, $matches)) {
            return 9999;
        }

        return (((int) $matches[1]) * 60) + ((int) $matches[2]);
    }

    /**
     * @param  Collection<int, AttendanceRecord>  $attendanceRecords
     * @return array<int, array{subject:string,group:string,teacher:string,teacherId:int|null,assessment:string,score:int|null,feedback:string,date:string,weekStart:string,contactUrl:string|null}>
     */
    private function buildEvaluations(Collection $attendanceRecords): array
    {
        return $attendanceRecords
            ->filter(function (AttendanceRecord $record): bool {
                return ! is_null($record->grade) || filled($record->feedback);
            })
            ->values()
            ->map(function (AttendanceRecord $record): array {
                $teacher = $record->courseClass?->teacher;
                $teacherId = $teacher?->id ? (int) $teacher->id : null;

                $courseName = (string) ($record->courseClass?->course?->name ?? ('Class #'.$record->class_id));
                $courseCode = (string) ($record->courseClass?->course?->code ?? ('Class '.$record->class_id));
                $feedback = trim((string) ($record->feedback ?? ''));

                return [
                    'subject' => $courseName,
                    'group' => $courseCode,
                    'teacher' => (string) ($teacher?->name ?? 'Teacher TBA'),
                    'teacherId' => $teacherId,
                    'assessment' => 'Class Evaluation',
                    'score' => is_null($record->grade) ? null : (int) $record->grade,
                    'feedback' => $feedback !== '' ? $feedback : 'No written feedback provided.',
                    'date' => (string) ($record->attendance_date?->format('M j, Y') ?? 'Date N/A'),
                    'weekStart' => (string) ($record->attendance_date?->copy()->startOfWeek()->toDateString() ?? ''),
                    'contactUrl' => $teacherId
                        ? route('role.messages.conversation', ['role' => 'student', 'conversation' => $teacherId])
                        : null,
                ];
            })
            ->values()
            ->all();
    }
}
