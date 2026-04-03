<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('teacher can view their timetable', function () {
    // Create roles
    Role::create(['name' => 'teacher', 'guard_name' => 'web']);

    // Create teacher
    $teacher = User::factory()->create([
        'email' => 'teacher@test.com',
        'approved_at' => now(),
    ]);
    $teacher->syncRoles(['teacher']);

    // Create course and class
    $course = Course::factory()->create();
    $class = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    // Create room and schedule
    $room = Room::factory()->create();
    Schedule::factory()->create([
        'class_id' => $class->id,
        'room_id' => $room->id,
        'day_of_week' => 'monday',
    ]);

    $response = $this->actingAs($teacher)->get(route('timetable.teacher'));

    $response->assertStatus(200);
    $response->assertViewIs('timetable.teacher');
    $response->assertViewHas('taughtClasses');
    $response->assertViewHas('timetable');
    $response->assertSee($course->name);
    $response->assertSee($room->name);
});

it('teacher sees multiple classes in timetable', function () {
    Role::create(['name' => 'teacher', 'guard_name' => 'web']);

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->syncRoles(['teacher']);

    // Create multiple courses and classes
    $course1 = Course::factory()->create(['name' => 'Math 101']);
    $course2 = Course::factory()->create(['name' => 'Physics 201']);

    $class1 = CourseClass::factory()->create(['course_id' => $course1->id, 'teacher_id' => $teacher->id]);
    $class2 = CourseClass::factory()->create(['course_id' => $course2->id, 'teacher_id' => $teacher->id]);

    $room1 = Room::factory()->create(['name' => 'Room 101']);
    $room2 = Room::factory()->create(['name' => 'Room 205']);

    Schedule::factory()->create(['class_id' => $class1->id, 'room_id' => $room1->id, 'day_of_week' => 'monday']);
    Schedule::factory()->create(['class_id' => $class2->id, 'room_id' => $room2->id, 'day_of_week' => 'monday']);

    $response = $this->actingAs($teacher)->get(route('timetable.teacher'));

    $response->assertStatus(200);
    $response->assertSee('Math 101');
    $response->assertSee('Physics 201');
    $response->assertSee('Room 101');
    $response->assertSee('Room 205');
});

it('teacher sees student enrollment count', function () {
    Role::create(['name' => 'teacher', 'guard_name' => 'web']);
    Role::create(['name' => 'student', 'guard_name' => 'web']);

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->syncRoles(['teacher']);

    $student1 = User::factory()->create(['approved_at' => now()]);
    $student1->syncRoles(['student']);

    $student2 = User::factory()->create(['approved_at' => now()]);
    $student2->syncRoles(['student']);

    $course = Course::factory()->create();
    $class = CourseClass::factory()->create(['course_id' => $course->id, 'teacher_id' => $teacher->id]);

    $room = Room::factory()->create();
    Schedule::factory()->create(['class_id' => $class->id, 'room_id' => $room->id, 'day_of_week' => 'monday']);

    // Enroll students
    $class->students()->attach([$student1->id, $student2->id]);

    $response = $this->actingAs($teacher)->get(route('timetable.teacher'));

    $response->assertStatus(200);
    $response->assertSee('2 students');
});

it('teacher sees empty state when no classes assigned', function () {
    Role::create(['name' => 'teacher', 'guard_name' => 'web']);

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->syncRoles(['teacher']);

    $response = $this->actingAs($teacher)->get(route('timetable.teacher'));

    $response->assertStatus(200);
    $response->assertSee('No classes assigned');
});

it('non-teacher cannot view teacher timetable', function () {
    Role::create(['name' => 'student', 'guard_name' => 'web']);

    $student = User::factory()->create(['approved_at' => now()]);
    $student->syncRoles(['student']);

    $response = $this->actingAs($student)->get(route('timetable.teacher'));

    $response->assertStatus(403);
});

it('unauthenticated user cannot view teacher timetable', function () {
    $response = $this->get(route('timetable.teacher'));

    $response->assertRedirect(route('login'));
});

it('schedules are sorted by start time within each day', function () {
    Role::create(['name' => 'teacher', 'guard_name' => 'web']);

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->syncRoles(['teacher']);

    $course = Course::factory()->create(['name' => 'Test Course']);
    $class = CourseClass::factory()->create(['course_id' => $course->id, 'teacher_id' => $teacher->id]);

    $room = Room::factory()->create();

    // Create schedules on same day with different times
    Schedule::factory()->create([
        'class_id' => $class->id,
        'room_id' => $room->id,
        'day_of_week' => 'monday',
        'start_time' => '14:00',
        'end_time' => '15:30',
    ]);

    Schedule::factory()->create([
        'class_id' => $class->id,
        'room_id' => $room->id,
        'day_of_week' => 'monday',
        'start_time' => '09:00',
        'end_time' => '10:30',
    ]);

    $response = $this->actingAs($teacher)->get(route('timetable.teacher'));

    $response->assertStatus(200);

    // Get the timetable data
    $timetable = $response->viewData('timetable');

    // Verify Monday has 2 classes
    expect($timetable['monday'])->toHaveCount(2);

    // Verify they're sorted by start time (09:00 before 14:00)
    expect((string) $timetable['monday'][0]['start_time'])->toContain('09:00');
    expect((string) $timetable['monday'][1]['start_time'])->toContain('14:00');
});
