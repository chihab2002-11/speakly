<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

// Helper to create all required roles
function createAllRoles(): void
{
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'secretary', 'guard_name' => 'web']);
    Role::create(['name' => 'teacher', 'guard_name' => 'web']);
    Role::create(['name' => 'student', 'guard_name' => 'web']);
}

test('secretary can access timetable explorer', function () {
    createAllRoles();

    // Create a secretary user
    $secretary = User::factory()->create(['approved_at' => now()]);
    $secretary->assignRole('secretary');

    $response = $this->actingAs($secretary)->get(route('secretary.timetable.index'));

    $response->assertStatus(200);
    $response->assertViewIs('secretary.timetable.index');
});

test('student cannot access secretary timetable explorer', function () {
    createAllRoles();

    // Create a student user
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $response = $this->actingAs($student)->get(route('secretary.timetable.index'));

    $response->assertStatus(403);
});

test('secretary timetable explorer returns required data', function () {
    createAllRoles();

    // Create a secretary user
    $secretary = User::factory()->create(['approved_at' => now()]);
    $secretary->assignRole('secretary');

    $response = $this->actingAs($secretary)->get(route('secretary.timetable.index'));

    $response->assertStatus(200);
    $response->assertViewHas('groupedSchedules');
    $response->assertViewHas('teachers');
    $response->assertViewHas('students');
    $response->assertViewHas('courses');
    $response->assertViewHas('classes');
    $response->assertViewHas('rooms');
});

test('secretary timetable explorer filters by teacher_id', function () {
    createAllRoles();

    $secretary = User::factory()->create(['approved_at' => now()]);
    $secretary->assignRole('secretary');

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    // Create course and room
    $course = Course::factory()->create();
    $room = Room::factory()->create();

    // Create class directly without factory (avoid factory's room field)
    $class = CourseClass::create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'capacity' => 30,
    ]);

    $schedule = Schedule::factory()->create([
        'class_id' => $class->id,
        'room_id' => $room->id,
    ]);

    $response = $this->actingAs($secretary)
        ->get(route('secretary.timetable.index', ['teacher_id' => $teacher->id]));

    $response->assertStatus(200);
    $response->assertViewHas('groupedSchedules');
    $schedules = $response->viewData('groupedSchedules')->flatten();
    expect($schedules->count())->toBeGreaterThan(0);
});
