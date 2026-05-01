<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('student', 'web');
});

test('student dashboard shows popular courses ordered by assigned student count', function () {
    $dashboardStudent = User::factory()->create([
        'approved_at' => now(),
    ]);
    $dashboardStudent->assignRole('student');

    $ielts = Course::factory()->create([
        'name' => 'IELTS Preparation',
        'code' => 'IELTS-PREP',
    ]);
    $french = Course::factory()->create([
        'name' => 'French A2',
        'code' => 'FR-A2',
    ]);
    $german = Course::factory()->create([
        'name' => 'German B1',
        'code' => 'DE-B1',
    ]);

    $ieltsGroupOne = CourseClass::factory()->create(['course_id' => $ielts->id]);
    $ieltsGroupTwo = CourseClass::factory()->create(['course_id' => $ielts->id]);
    $frenchGroup = CourseClass::factory()->create(['course_id' => $french->id]);
    $germanGroup = CourseClass::factory()->create(['course_id' => $german->id]);

    $students = User::factory()->count(4)->create([
        'approved_at' => now(),
    ]);
    $students->each->assignRole('student');

    $ieltsGroupOne->students()->attach([
        $students[0]->id => ['enrolled_at' => now()],
        $students[1]->id => ['enrolled_at' => now()],
        $students[2]->id => ['enrolled_at' => now()],
    ]);
    $ieltsGroupTwo->students()->attach([
        $students[0]->id => ['enrolled_at' => now()],
    ]);
    $frenchGroup->students()->attach([
        $students[0]->id => ['enrolled_at' => now()],
        $students[1]->id => ['enrolled_at' => now()],
    ]);
    $germanGroup->students()->attach([
        $students[3]->id => ['enrolled_at' => now()],
    ]);

    $response = $this->actingAs($dashboardStudent)
        ->get(route('role.dashboard', ['role' => 'student']));

    $response->assertOk();
    $response->assertSeeInOrder([
        'IELTS Preparation',
        '3 students',
        'French A2',
        '2 students',
        'German B1',
        '1 student',
    ]);
});
