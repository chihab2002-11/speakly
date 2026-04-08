<?php

use App\Models\Message;
use App\Models\Schedule;
use App\Models\TeacherResource;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TeacherWorkflowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function runTeacherWorkflowSeeders(): void
{
    foreach ([PermissionSeeder::class, RoleSeeder::class, TeacherWorkflowSeeder::class] as $seederClass) {
        app($seederClass)->run();
    }
}

it('creates coherent teacher workflow development data', function () {
    runTeacherWorkflowSeeders();

    $teacher = User::query()->where('email', 'teacher.nadia@speakly.com')->first();
    $student = User::query()->where('email', 'student.amir@speakly.com')->first();
    $parent = User::query()->where('email', 'parent.layla@speakly.com')->first();
    $pendingTeacher = User::query()->where('email', 'pending.teacher@speakly.com')->first();

    expect($teacher)->not()->toBeNull();
    expect($student)->not()->toBeNull();
    expect($parent)->not()->toBeNull();
    expect($pendingTeacher)->not()->toBeNull();

    expect($teacher->hasRole('teacher'))->toBeTrue();
    expect($teacher->approved_at)->not()->toBeNull();
    expect($student->hasRole('student'))->toBeTrue();
    expect($student->parent_id)->toBe($parent->id);
    expect($pendingTeacher->approved_at)->toBeNull();

    $teacherClassIds = $teacher->taughtClasses()->pluck('id');

    expect($teacherClassIds->isNotEmpty())->toBeTrue();
    expect(Schedule::query()->whereIn('class_id', $teacherClassIds)->count())->toBeGreaterThan(0);
    expect($teacher->teacherResources()->count())->toBeGreaterThan(0);
    expect(
        TeacherResource::query()->whereIn('class_id', $teacherClassIds)->count()
    )->toBeGreaterThan(0);
    expect(Message::query()->where('receiver_id', $teacher->id)->count())->toBeGreaterThan(0);
    expect($student->enrolledClasses()->count())->toBeGreaterThan(0);
});

it('is idempotent when run repeatedly', function () {
    runTeacherWorkflowSeeders();

    $initialCounts = [
        'users' => User::query()->count(),
        'schedules' => Schedule::query()->count(),
        'resources' => TeacherResource::query()->count(),
        'messages' => Message::query()->count(),
    ];

    app(TeacherWorkflowSeeder::class)->run();

    expect(User::query()->count())->toBe($initialCounts['users']);
    expect(Schedule::query()->count())->toBe($initialCounts['schedules']);
    expect(TeacherResource::query()->count())->toBe($initialCounts['resources']);
    expect(Message::query()->count())->toBe($initialCounts['messages']);
});
