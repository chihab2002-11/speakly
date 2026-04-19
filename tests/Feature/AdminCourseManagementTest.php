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

function createAdminForCourseTests(): User
{
    $admin = User::factory()->create([
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    return $admin;
}

test('admin can view manage courses page', function () {
    /** @var TestCase $this */
    $admin = createAdminForCourseTests();

    $this->actingAs($admin)
        ->get(route('admin.courses.index'))
        ->assertOk()
        ->assertSee('Manage Courses');
});

test('admin can create course with auto code', function () {
    /** @var TestCase $this */
    $admin = createAdminForCourseTests();

    $this->actingAs($admin)
        ->post(route('admin.courses.store'), [
            'name' => 'English Conversation B1',
            'price' => 18000,
            'description' => 'Conversation-focused course.',
        ])
        ->assertRedirect(route('admin.courses.index'));

    $course = Course::query()->where('name', 'English Conversation B1')->first();

    expect($course)->not->toBeNull();
    expect((string) $course?->code)->toMatch('/^[A-Z]{3}\d{3}$/');
    expect((int) $course?->price)->toBe(18000);
});

test('admin can update course details', function () {
    /** @var TestCase $this */
    $admin = createAdminForCourseTests();
    $course = Course::factory()->create([
        'name' => 'French A1',
        'price' => 12000,
        'description' => 'Old description',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.courses.update', $course), [
            'name' => 'French A1 Updated',
            'price' => 25000,
            'description' => 'Updated description',
        ])
        ->assertRedirect(route('admin.courses.index'));

    $course->refresh();
    expect($course->name)->toBe('French A1 Updated');
    expect((int) $course->price)->toBe(25000);
    expect($course->description)->toBe('Updated description');
});

test('admin cannot delete course with existing classes', function () {
    /** @var TestCase $this */
    $admin = createAdminForCourseTests();
    $course = Course::factory()->create();
    CourseClass::factory()->create([
        'course_id' => $course->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.courses.destroy', $course))
        ->assertRedirect(route('admin.courses.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('courses', ['id' => $course->id]);
});
