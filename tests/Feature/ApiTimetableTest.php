<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['admin', 'parent', 'student', 'teacher'] as $role) {
        Role::create(['name' => $role, 'guard_name' => 'web']);
    }
});

it('returns only the authenticated student timetable', function () {
    $student = createApprovedUser('student', ['email' => 'student@example.com']);
    $otherStudent = createApprovedUser('student', ['email' => 'other@example.com']);
    $teacher = createApprovedUser('teacher');

    createScheduleForStudent($student, $teacher, 'English Basics', 'ENG101', 'monday', '09:00', '10:00');
    createScheduleForStudent($otherStudent, $teacher, 'Japanese Basics', 'JPN101', 'tuesday', '11:00', '12:00');

    Sanctum::actingAs($student);

    $this->getJson('/api/student/timetable')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.class.course.code', 'ENG101')
        ->assertJsonPath('meta.student.email', 'student@example.com')
        ->assertJsonPath('meta.total', 1)
        ->assertJsonMissing(['code' => 'JPN101']);
});

it('returns a linked child timetable for parents only', function () {
    $parent = createApprovedUser('parent', ['email' => 'parent@example.com']);
    $child = createApprovedUser('student', ['parent_id' => $parent->id, 'email' => 'child@example.com']);
    $otherChild = createApprovedUser('student', ['email' => 'other-child@example.com']);
    $teacher = createApprovedUser('teacher');

    createScheduleForStudent($child, $teacher, 'French Basics', 'FR101', 'wednesday', '08:00', '09:00');
    createScheduleForStudent($otherChild, $teacher, 'Greek Basics', 'GR101', 'thursday', '13:00', '14:00');

    Sanctum::actingAs($parent);

    $this->getJson('/api/parent/timetable?child_id='.$child->id)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.class.course.code', 'FR101')
        ->assertJsonPath('meta.student.email', 'child@example.com')
        ->assertJsonMissing(['code' => 'GR101']);

    $this->getJson('/api/parent/timetable?child_id='.$otherChild->id)
        ->assertNotFound();
});

it('lets admins view timetables with filters', function () {
    $admin = createApprovedUser('admin');
    $student = createApprovedUser('student');
    $otherStudent = createApprovedUser('student');
    $teacher = createApprovedUser('teacher');

    createScheduleForStudent($student, $teacher, 'German Basics', 'DE101', 'friday', '15:00', '16:00');
    createScheduleForStudent($otherStudent, $teacher, 'Italian Basics', 'IT101', 'saturday', '10:00', '11:00');

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/timetables?student_id='.$student->id)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.class.course.code', 'DE101')
        ->assertJsonPath('data.0.class.students.0.id', $student->id)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonMissing(['code' => 'IT101']);
});

it('blocks students from admin timetable data', function () {
    $student = createApprovedUser('student');

    Sanctum::actingAs($student);

    $this->getJson('/api/admin/timetables')
        ->assertForbidden();
});

function createApprovedUser(string $role, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'approved_at' => now(),
    ], $attributes));

    $user->assignRole($role);

    return $user;
}

function createScheduleForStudent(
    User $student,
    User $teacher,
    string $courseName,
    string $courseCode,
    string $day,
    string $startTime,
    string $endTime
): Schedule {
    $course = Course::factory()->create([
        'name' => $courseName,
        'code' => $courseCode,
    ]);

    $class = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $class->students()->attach($student->id);

    $room = Room::factory()->create();

    return Schedule::factory()->create([
        'class_id' => $class->id,
        'room_id' => $room->id,
        'day_of_week' => $day,
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);
}
