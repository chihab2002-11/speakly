<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedAuthorizationFixtures();
});

function createApprovedAdminUser(): User
{
    $admin = User::factory()->create([
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    return $admin;
}

test('admin can open manage employees page', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdminUser();

    $response = $this->actingAs($admin)->get(route('admin.employees.index'));

    $response->assertOk();
    $response->assertSee('Manage employees');
    $response->assertSee('Academic Faculty');
    $response->assertDontSee('Add Teacher');
    $response->assertDontSee('Add Secretary');
});

test('admin can create and update secretary employee', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdminUser();

    $this->actingAs($admin)
        ->post(route('admin.employees.secretaries.store'), [
            'name' => 'Elena Belova',
            'email' => 'elena.secretary@example.com',
            'phone' => '0555-100-100',
            'password' => 'Password123!',
        ])
        ->assertRedirect(route('admin.employees.index'));

    $secretary = User::query()->where('email', 'elena.secretary@example.com')->first();
    expect($secretary)->not->toBeNull();
    expect($secretary?->hasRole('secretary'))->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('admin.employees.secretaries.update', $secretary), [
            'name' => 'Elena Belova Updated',
            'email' => 'elena.updated@example.com',
            'phone' => '0555-200-200',
        ])
        ->assertRedirect(route('admin.employees.index'));

    $secretary->refresh();
    expect($secretary->name)->toBe('Elena Belova Updated');
    expect($secretary->email)->toBe('elena.updated@example.com');
});

test('admin can manage teacher employee and assign language', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdminUser();

    $this->actingAs($admin)
        ->post(route('admin.employees.teachers.store'), [
            'name' => 'Dr Sofia Rossi',
            'email' => 'sofia.teacher@example.com',
            'phone' => '0555-300-300',
            'preferred_language' => 'italian',
            'bio' => 'Language specialist',
            'password' => 'Password123!',
        ])
        ->assertRedirect(route('admin.employees.index'));

    $teacher = User::query()->where('email', 'sofia.teacher@example.com')->first();
    expect($teacher)->not->toBeNull();
    expect($teacher?->hasRole('teacher'))->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('admin.employees.teachers.assign-language', $teacher), [
            'preferred_language' => 'french',
        ])
        ->assertRedirect(route('admin.employees.index'));

    $teacher->refresh();
    expect($teacher->preferred_language)->toBe('french');

    $course = Course::factory()->create(['name' => 'French B1']);
    CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.employees.teachers.destroy', $teacher))
        ->assertRedirect(route('admin.employees.index'))
        ->assertSessionHasErrors('teacher');
});
