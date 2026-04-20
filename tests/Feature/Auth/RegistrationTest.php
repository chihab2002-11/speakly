<?php

use App\Models\Course;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('parent', 'web');
});

test('registration screen can be rendered', function () {
    // /register now redirects to the custom /register-login page
    $response = $this->get(route('register'));

    $response->assertRedirectToRoute('register-login', ['tab' => 'register']);

    // Verify the custom register-login page loads correctly
    $response = $this->get(route('register-login'));
    $response->assertOk();
});

test('registration screen only shows courses with valid prices', function () {
    Course::factory()->create([
        'name' => 'Priced Course',
        'price' => 12000,
    ]);

    Course::factory()->create([
        'name' => 'Unpriced Course',
        'price' => 0,
    ]);

    $this->get(route('register-login'))
        ->assertOk()
        ->assertSee('Priced Course')
        ->assertDontSee('Unpriced Course');
});

test('new users can register', function () {
    $course = Course::factory()->create(['name' => 'English A1']);

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'date_of_birth' => '2000-01-01',
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'course_id' => $course->id,
    ]);

    $response->assertSessionHasNoErrors()
        // New users are not approved yet, so they should not reach dashboard
        ->assertRedirectToRoute('pending-approval');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'requested_course_id' => $course->id,
    ]);
});

test('underage students must provide parent email', function () {
    $course = Course::factory()->create();

    $response = $this->post(route('register.store'), [
        'name' => 'Minor Student',
        'email' => 'minor@example.com',
        'date_of_birth' => now()->subYears(16)->toDateString(),
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'course_id' => $course->id,
    ]);

    $response->assertSessionHasErrors('parent_email');
});

test('underage student is connected to parent account', function () {
    $course = Course::factory()->create();

    $parent = User::factory()->create([
        'email' => 'parent@example.com',
        'approved_at' => now(),
    ]);
    $parent->assignRole('parent');

    $response = $this->post(route('register.store'), [
        'name' => 'Minor Student',
        'email' => 'linked-minor@example.com',
        'date_of_birth' => now()->subYears(15)->toDateString(),
        'parent_email' => $parent->email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'course_id' => $course->id,
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirectToRoute('pending-approval');

    $student = User::query()->where('email', 'linked-minor@example.com')->first();

    expect($student)->not()->toBeNull();
    expect($student->parent_id)->toBe($parent->id);
    expect($student->requested_course_id)->toBe($course->id);
});

test('underage student cannot be linked to unapproved parent account', function () {
    $course = Course::factory()->create();

    $parent = User::factory()->create([
        'email' => 'pending-parent@example.com',
        'approved_at' => null,
    ]);
    $parent->assignRole('parent');

    $response = $this->post(route('register.store'), [
        'name' => 'Minor Student',
        'email' => 'blocked-minor@example.com',
        'date_of_birth' => now()->subYears(15)->toDateString(),
        'parent_email' => $parent->email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'course_id' => $course->id,
    ]);

    $response->assertSessionHasErrors('parent_email');
    $response->assertSessionHasErrors([
        'parent_email' => 'Parent account must exist and be approved.',
    ]);
});

test('student registration requires a course selection', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'No Course Student',
        'email' => 'no-course@example.com',
        'date_of_birth' => '2000-01-01',
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
    ]);

    $response->assertSessionHasErrors('course_id');
});

test('student registration rejects a course without a valid price', function () {
    $course = Course::factory()->create([
        'price' => 0,
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Invalid Course Student',
        'email' => 'invalid-course@example.com',
        'date_of_birth' => '2000-01-01',
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'course_id' => $course->id,
    ]);

    $response->assertSessionHasErrors([
        'course_id' => 'Selected course is not available for registration.',
    ]);
});
