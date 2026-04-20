<?php

use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['teacher', 'student'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

function createApprovedTeacherForAttendance(): User
{
    $teacher = User::factory()->create([
        'approved_at' => now(),
    ]);

    $teacher->assignRole('teacher');

    return $teacher;
}

function createClassForTeacherAttendance(User $teacher, ?string $courseName = null): CourseClass
{
    $course = Course::factory()->create([
        'name' => $courseName ?? fake()->sentence(2),
    ]);

    return CourseClass::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
    ]);
}

function createApprovedStudentForAttendance(string $name): User
{
    $student = User::factory()->create([
        'name' => $name,
        'approved_at' => now(),
    ]);

    $student->assignRole('student');

    return $student;
}

it('loads real attendance data and summary stats for selected class and date', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacherForAttendance();
    $class = createClassForTeacherAttendance($teacher, 'English B2');
    $date = '2026-04-07';

    $studentOne = createApprovedStudentForAttendance('Student One');
    $studentTwo = createApprovedStudentForAttendance('Student Two');
    $studentThree = createApprovedStudentForAttendance('Student Three');

    $class->students()->attach([$studentOne->id, $studentTwo->id, $studentThree->id]);

    AttendanceRecord::query()->create([
        'class_id' => $class->id,
        'student_id' => $studentOne->id,
        'attendance_date' => $date,
        'status' => 'present',
        'grade' => 90,
        'feedback' => 'Great participation',
    ]);

    AttendanceRecord::query()->create([
        'class_id' => $class->id,
        'student_id' => $studentTwo->id,
        'attendance_date' => $date,
        'status' => 'late',
        'grade' => 75,
        'feedback' => null,
    ]);

    AttendanceRecord::query()->create([
        'class_id' => $class->id,
        'student_id' => $studentThree->id,
        'attendance_date' => $date,
        'status' => 'absent',
        'grade' => null,
        'feedback' => 'Medical leave',
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.attendance', [
        'class_id' => $class->id,
        'date' => $date,
    ]));

    $response->assertOk();
    $response->assertViewHas('selectedDate', $date);
    $response->assertViewHas('selectedClass', fn (array $selectedClass): bool => $selectedClass['id'] === $class->id);
    $response->assertViewHas('stats', fn (array $stats): bool => $stats['present'] === 1
        && $stats['late'] === 1
        && $stats['absent'] === 1
        && $stats['total'] === 3
    );
});

it('loads different student rosters when switching selected class', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacherForAttendance();
    $classA = createClassForTeacherAttendance($teacher, 'Class A');
    $classB = createClassForTeacherAttendance($teacher, 'Class B');

    $classAStudent = createApprovedStudentForAttendance('Class A Student');
    $classBStudent = createApprovedStudentForAttendance('Class B Student');

    $classA->students()->attach([$classAStudent->id]);
    $classB->students()->attach([$classBStudent->id]);

    $date = '2026-04-08';

    $classAResponse = $this->actingAs($teacher)->get(route('teacher.attendance', [
        'class_id' => $classA->id,
        'date' => $date,
    ]));

    $classAResponse->assertOk();
    $classAResponse->assertViewHas('selectedClass', fn (array $selectedClass): bool => $selectedClass['id'] === $classA->id);
    $classAResponse->assertViewHas('selectedDate', $date);
    $classAResponse->assertViewHas('students', function (array $students) use ($classAStudent, $classBStudent): bool {
        $studentIds = collect($students)->pluck('id')->all();

        return in_array($classAStudent->id, $studentIds, true)
            && ! in_array($classBStudent->id, $studentIds, true);
    });

    $classBResponse = $this->actingAs($teacher)->get(route('teacher.attendance', [
        'class_id' => $classB->id,
        'date' => $date,
    ]));

    $classBResponse->assertOk();
    $classBResponse->assertViewHas('selectedClass', fn (array $selectedClass): bool => $selectedClass['id'] === $classB->id);
    $classBResponse->assertViewHas('selectedDate', $date);
    $classBResponse->assertViewHas('students', function (array $students) use ($classAStudent, $classBStudent): bool {
        $studentIds = collect($students)->pluck('id')->all();

        return in_array($classBStudent->id, $studentIds, true)
            && ! in_array($classAStudent->id, $studentIds, true);
    });
});

it('saves and upserts attendance evaluation records for a teachers own class', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacherForAttendance();
    $class = createClassForTeacherAttendance($teacher);
    $date = '2026-04-07';

    $studentOne = createApprovedStudentForAttendance('Student One');
    $studentTwo = createApprovedStudentForAttendance('Student Two');

    $class->students()->attach([$studentOne->id, $studentTwo->id]);
    scheduleClassOnDate($class, $date);

    $this->actingAs($teacher)->post(route('teacher.attendance.store'), [
        'class_id' => $class->id,
        'date' => $date,
        'records' => [
            [
                'student_id' => $studentOne->id,
                'status' => 'present',
                'grade' => 88,
                'feedback' => 'Good progress',
            ],
            [
                'student_id' => $studentTwo->id,
                'status' => 'absent',
                'grade' => null,
                'feedback' => 'Family emergency',
            ],
        ],
    ])->assertRedirect(route('teacher.attendance', ['class_id' => $class->id, 'date' => $date]));

    expect(AttendanceRecord::query()
        ->where('class_id', $class->id)
        ->where('student_id', $studentOne->id)
        ->whereDate('attendance_date', $date)
        ->where('status', 'present')
        ->where('grade', 88)
        ->where('feedback', 'Good progress')
        ->exists())->toBeTrue();

    expect(AttendanceRecord::query()
        ->where('class_id', $class->id)
        ->where('student_id', $studentTwo->id)
        ->whereDate('attendance_date', $date)
        ->where('status', 'absent')
        ->whereNull('grade')
        ->where('feedback', 'Family emergency')
        ->exists())->toBeTrue();

    $this->actingAs($teacher)->post(route('teacher.attendance.store'), [
        'class_id' => $class->id,
        'date' => $date,
        'records' => [
            [
                'student_id' => $studentOne->id,
                'status' => 'late',
                'grade' => 81,
                'feedback' => 'Arrived late',
            ],
            [
                'student_id' => $studentTwo->id,
                'status' => 'present',
                'grade' => 93,
                'feedback' => 'Excellent recovery',
            ],
        ],
    ])->assertRedirect(route('teacher.attendance', ['class_id' => $class->id, 'date' => $date]));

    expect(AttendanceRecord::query()->where('class_id', $class->id)->whereDate('attendance_date', $date)->count())->toBe(2);

    expect(AttendanceRecord::query()
        ->where('class_id', $class->id)
        ->where('student_id', $studentOne->id)
        ->whereDate('attendance_date', $date)
        ->where('status', 'late')
        ->where('grade', 81)
        ->where('feedback', 'Arrived late')
        ->exists())->toBeTrue();
});

it('rejects attendance submissions for classes not taught by the current teacher', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacherForAttendance();
    $otherTeacher = createApprovedTeacherForAttendance();

    $otherClass = createClassForTeacherAttendance($otherTeacher);
    $student = createApprovedStudentForAttendance('Student Outside Class');
    $otherClass->students()->attach([$student->id]);

    $response = $this->actingAs($teacher)->post(route('teacher.attendance.store'), [
        'class_id' => $otherClass->id,
        'date' => '2026-04-07',
        'records' => [
            [
                'student_id' => $student->id,
                'status' => 'present',
                'grade' => 90,
                'feedback' => 'Should not persist',
            ],
        ],
    ]);

    $response->assertSessionHasErrors('class_id');
    expect(AttendanceRecord::query()->count())->toBe(0);
});

it('rejects attendance submissions with duplicate student rows', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacherForAttendance();
    $class = createClassForTeacherAttendance($teacher);
    $student = createApprovedStudentForAttendance('Duplicated Student');

    $class->students()->attach([$student->id]);

    $response = $this->actingAs($teacher)->post(route('teacher.attendance.store'), [
        'class_id' => $class->id,
        'date' => '2026-04-07',
        'records' => [
            [
                'student_id' => $student->id,
                'status' => 'present',
                'grade' => 90,
                'feedback' => 'First row',
            ],
            [
                'student_id' => $student->id,
                'status' => 'late',
                'grade' => 80,
                'feedback' => 'Duplicate row',
            ],
        ],
    ]);

    $response->assertSessionHasErrors('records.1.student_id');
    expect(AttendanceRecord::query()->count())->toBe(0);
});

it('exports attendance csv for a teachers own class', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacherForAttendance();
    $class = createClassForTeacherAttendance($teacher, 'English B2');
    $date = '2026-04-08';

    $student = createApprovedStudentForAttendance('Export Student');
    $class->students()->attach([$student->id]);

    AttendanceRecord::query()->create([
        'class_id' => $class->id,
        'student_id' => $student->id,
        'attendance_date' => $date,
        'status' => 'late',
        'grade' => 72,
        'feedback' => 'Arrived 10 minutes late',
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.attendance.export', [
        'class_id' => $class->id,
        'date' => $date,
    ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = (string) $response->streamedContent();
    $csvWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $csv) ?? $csv;
    $lines = array_filter(array_map('trim', explode("\n", $csvWithoutBom)));
    $parsed = array_map(fn (string $line): array => str_getcsv($line, ';'), $lines);

    expect($parsed[0])->toBe([
        'Class',
        'Date',
        'Student ID',
        'Student Name',
        'Attendance',
        'Grade',
        'Feedback',
    ]);

    expect($csvWithoutBom)->toContain('English B2');
    expect($csvWithoutBom)->toContain('Export Student');
});

it('rejects attendance export for another teachers class', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacherForAttendance();
    $otherTeacher = createApprovedTeacherForAttendance();

    $otherClass = createClassForTeacherAttendance($otherTeacher, 'Private Class');

    $response = $this->actingAs($teacher)->get(route('teacher.attendance.export', [
        'class_id' => $otherClass->id,
        'date' => '2026-04-08',
    ]));

    $response->assertForbidden();
});

it('includes expected exported attendance columns and data', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacherForAttendance();
    $class = createClassForTeacherAttendance($teacher, 'Conversation A2');
    $date = '2026-04-08';

    $studentOne = createApprovedStudentForAttendance('Alice Student');
    $studentTwo = createApprovedStudentForAttendance('Bob Student');

    $class->students()->attach([$studentOne->id, $studentTwo->id]);

    AttendanceRecord::query()->create([
        'class_id' => $class->id,
        'student_id' => $studentOne->id,
        'attendance_date' => $date,
        'status' => 'absent',
        'grade' => null,
        'feedback' => 'Medical leave',
    ]);

    AttendanceRecord::query()->create([
        'class_id' => $class->id,
        'student_id' => $studentTwo->id,
        'attendance_date' => $date,
        'status' => 'present',
        'grade' => 95,
        'feedback' => 'Excellent participation',
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.attendance.export', [
        'class_id' => $class->id,
        'date' => $date,
    ]));

    $response->assertOk();

    $csv = (string) $response->streamedContent();
    $csvWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $csv) ?? $csv;
    $lines = array_filter(array_map('trim', explode("\n", $csvWithoutBom)));
    $parsed = array_map(fn (string $line): array => str_getcsv($line, ';'), $lines);

    expect($parsed[0])->toBe([
        'Class',
        'Date',
        'Student ID',
        'Student Name',
        'Attendance',
        'Grade',
        'Feedback',
    ]);

    expect($csvWithoutBom)->toContain('Conversation A2');
    expect($csvWithoutBom)->toContain('Alice Student');
    expect($csvWithoutBom)->toContain('Bob Student');
    expect($csvWithoutBom)->toContain('absent');
    expect($csvWithoutBom)->toContain('present');
    expect($csvWithoutBom)->toContain('Excellent participation');
});
