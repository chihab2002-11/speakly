<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\CourseClass;
use App\Models\User;
use App\Notifications\TeacherAttendanceSavedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $taughtClasses = $teacher->taughtClasses()
            ->with('course')
            ->withCount('students')
            ->orderBy('created_at')
            ->get();

        $selectedDate = $this->resolveSelectedDate((string) $request->query('date', now()->toDateString()));
        $selectedClass = $this->resolveSelectedClass($taughtClasses, $request->query('class_id'));

        $students = [];
        $stats = [
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'total' => 0,
        ];

        if ($selectedClass) {
            $records = AttendanceRecord::query()
                ->where('class_id', $selectedClass->id)
                ->whereDate('attendance_date', $selectedDate)
                ->get()
                ->keyBy('student_id');

            $students = $selectedClass->students()
                ->orderBy('name')
                ->get(['users.id', 'users.name'])
                ->map(function (User $student) use ($records): array {
                    $record = $records->get($student->id);
                    $status = (string) ($record?->status ?? 'present');

                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'avatar' => null,
                        'attendance' => $status,
                        'grade' => $record?->grade,
                        'feedback' => (string) ($record?->feedback ?? ''),
                    ];
                })
                ->values()
                ->all();

            $studentsCollection = collect($students);
            $stats = [
                'present' => $studentsCollection->where('attendance', 'present')->count(),
                'late' => $studentsCollection->where('attendance', 'late')->count(),
                'absent' => $studentsCollection->where('attendance', 'absent')->count(),
                'total' => $studentsCollection->count(),
            ];
        }

        return view('teacher.attendance', [
            'user' => $teacher,
            'classes' => $taughtClasses
                ->map(function (CourseClass $courseClass): array {
                    return [
                        'id' => $courseClass->id,
                        'name' => $courseClass->course?->name ?? 'Class #'.$courseClass->id,
                        'students_count' => $courseClass->students_count,
                    ];
                })
                ->values()
                ->all(),
            'students' => $students,
            'stats' => $stats,
            'selectedClass' => $selectedClass
                ? [
                    'id' => $selectedClass->id,
                    'name' => $selectedClass->course?->name ?? 'Class #'.$selectedClass->id,
                    'students_count' => $selectedClass->students_count,
                ]
                : null,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'class_id' => [
                'required',
                'integer',
                Rule::exists('classes', 'id')->where(fn ($query) => $query->where('teacher_id', $teacher->id)),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'distinct'],
            'records.*.status' => ['required', Rule::in(['present', 'late', 'absent'])],
            'records.*.grade' => ['nullable', 'integer', 'between:0,100'],
            'records.*.feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        $courseClass = $teacher->taughtClasses()
            ->with(['students:id', 'schedules:id,class_id,day_of_week'])
            ->findOrFail((int) $validated['class_id']);

        $selectedDate = Carbon::createFromFormat('Y-m-d', $validated['date']);

        if (! $this->classHasSessionOnDate($courseClass, $selectedDate)) {
            throw ValidationException::withMessages([
                'date' => 'No class is scheduled on '.$selectedDate->format('l, M j, Y').'. Attendance and grades were not saved.',
            ]);
        }

        $enrolledStudentIds = $courseClass->students->pluck('id')->map(fn (mixed $id): int => (int) $id);
        $submittedStudentIds = collect($validated['records'])
            ->pluck('student_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->unique();

        $invalidStudentId = $submittedStudentIds
            ->first(fn (int $studentId): bool => ! $enrolledStudentIds->contains($studentId));

        if ($invalidStudentId !== null) {
            throw ValidationException::withMessages([
                'records' => 'One or more selected students are not enrolled in the selected class.',
            ]);
        }

        $existingRecords = AttendanceRecord::query()
            ->where('class_id', $courseClass->id)
            ->whereDate('attendance_date', $validated['date'])
            ->get()
            ->keyBy('student_id');

        foreach ($validated['records'] as $record) {
            $studentId = (int) $record['student_id'];
            $status = (string) $record['status'];
            $grade = $status === 'absent' ? null : ($record['grade'] ?? null);
            $feedback = trim((string) ($record['feedback'] ?? ''));

            $attendanceRecord = $existingRecords->get($studentId);

            if ($attendanceRecord) {
                $attendanceRecord->update([
                    'status' => $status,
                    'grade' => $grade,
                    'feedback' => $feedback === '' ? null : $feedback,
                ]);

                continue;
            }

            $createdRecord = AttendanceRecord::query()->create([
                'class_id' => $courseClass->id,
                'student_id' => $studentId,
                'attendance_date' => $validated['date'],
                'status' => $status,
                'grade' => $grade,
                'feedback' => $feedback === '' ? null : $feedback,
            ]);

            $existingRecords->put($studentId, $createdRecord);
        }

        $teacher->notify(new TeacherAttendanceSavedNotification(
            classId: (int) $courseClass->id,
            className: (string) ($courseClass->course?->name ?? 'Class #'.$courseClass->id),
            date: $validated['date'],
            recordsCount: $submittedStudentIds->count(),
        ));

        return redirect()
            ->route('teacher.attendance', [
                'class_id' => $courseClass->id,
                'date' => $validated['date'],
            ])
            ->with('success', 'Attendance and evaluations saved successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $selectedDate = $this->resolveSelectedDate($validated['date']);

        $courseClass = CourseClass::query()
            ->with('course')
            ->findOrFail((int) $validated['class_id']);

        abort_unless((int) $courseClass->teacher_id === (int) $teacher->id, 403);

        $records = AttendanceRecord::query()
            ->where('class_id', $courseClass->id)
            ->whereDate('attendance_date', $selectedDate)
            ->get()
            ->keyBy('student_id');

        $rows = $courseClass->students()
            ->orderBy('name')
            ->get(['users.id', 'users.name'])
            ->map(function (User $student) use ($records): array {
                $record = $records->get($student->id);

                return [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'status' => (string) ($record?->status ?? 'present'),
                    'grade' => $record?->grade,
                    'feedback' => (string) ($record?->feedback ?? ''),
                ];
            })
            ->values()
            ->all();

        $filename = sprintf('attendance-class-%d-%s.csv', $courseClass->id, $selectedDate);

        return response()->streamDownload(function () use ($rows, $courseClass, $selectedDate): void {
            $handle = fopen('php://output', 'w');
            $delimiter = ';';

            if ($handle === false) {
                return;
            }

            // Add UTF-8 BOM so Excel opens Unicode content correctly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Class', 'Date', 'Student ID', 'Student Name', 'Attendance', 'Grade', 'Feedback'], $delimiter);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    (string) ($courseClass->course?->name ?? 'Class #'.$courseClass->id),
                    $selectedDate,
                    $row['student_id'],
                    $row['student_name'],
                    $row['status'],
                    $row['grade'] ?? '',
                    $row['feedback'],
                ], $delimiter);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function resolveSelectedDate(string $date): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $date)->toDateString();
        } catch (\Throwable) {
            return now()->toDateString();
        }
    }

    private function classHasSessionOnDate(CourseClass $courseClass, Carbon $date): bool
    {
        $scheduledDays = $courseClass->schedules
            ->pluck('day_of_week')
            ->filter()
            ->map(fn (mixed $day): string => strtolower(trim((string) $day)));

        if ($scheduledDays->isEmpty()) {
            return false;
        }

        $fullDay = strtolower($date->format('l'));
        $shortDay = strtolower($date->format('D'));

        return $scheduledDays->contains($fullDay) || $scheduledDays->contains($shortDay);
    }

    /**
     * @param  Collection<int, CourseClass>  $taughtClasses
     */
    private function resolveSelectedClass(Collection $taughtClasses, mixed $selectedClassId): ?CourseClass
    {
        if ($taughtClasses->isEmpty()) {
            return null;
        }

        if ($selectedClassId !== null && ctype_digit((string) $selectedClassId)) {
            $matched = $taughtClasses->firstWhere('id', (int) $selectedClassId);

            if ($matched instanceof CourseClass) {
                return $matched;
            }
        }

        $firstClass = $taughtClasses->first();

        return $firstClass instanceof CourseClass ? $firstClass : null;
    }
}
