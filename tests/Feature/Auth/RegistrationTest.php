<?php

use App\Models\Course;
use App\Models\LanguageProgram;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('parent', 'web');
});

function createLanguageProgramForRegistration(array $attributes = []): LanguageProgram
{
    static $sequence = 1;

    $programNumber = $sequence++;

    return LanguageProgram::query()->create(array_merge([
        'code' => 'REG'.$programNumber,
        'locale_code' => 'reg-'.$programNumber,
        'name' => 'Program '.$programNumber,
        'title' => 'Program '.$programNumber,
        'description' => 'Registration test language program '.$programNumber,
        'full_description' => 'Registration test language program '.$programNumber.' full description.',
        'flag_url' => 'https://example.com/flags/program-'.$programNumber.'.svg',
        'sort_order' => $programNumber,
        'is_active' => true,
    ], $attributes));
}

test('registration screen can be rendered', function () {
    // /register now redirects to the custom /register-login page
    $response = $this->get(route('register'));

    $response->assertRedirectToRoute('register-login', ['tab' => 'register']);

    // Verify the custom register-login page loads correctly
    $response = $this->get(route('register-login'));
    $response->assertOk();
});

test('registration screen only shows active programs and courses with valid prices', function () {
    $activeProgram = createLanguageProgramForRegistration([
        'name' => 'English Program',
    ]);

    $inactiveProgram = createLanguageProgramForRegistration([
        'code' => 'REGI',
        'locale_code' => 'reg-inactive',
        'name' => 'Inactive Program',
        'title' => 'Inactive Program',
        'is_active' => false,
    ]);

    Course::factory()->create([
        'name' => 'Priced Course',
        'price' => 12000,
        'program_id' => $activeProgram->id,
    ]);

    Course::factory()->create([
        'name' => 'Unpriced Course',
        'price' => 0,
        'program_id' => $activeProgram->id,
    ]);

    Course::factory()->create([
        'name' => 'Inactive Program Course',
        'price' => 14500,
        'program_id' => $inactiveProgram->id,
    ]);

    $this->get(route('register-login'))
        ->assertOk()
        ->assertSee('English Program')
        ->assertDontSee('Inactive Program')
        ->assertSee('Priced Course')
        ->assertDontSee('Unpriced Course')
        ->assertDontSee('Inactive Program Course');
});

test('new users can register', function () {
    $program = createLanguageProgramForRegistration([
        'name' => 'English Program',
    ]);

    $course = Course::factory()->create([
        'name' => 'English A1',
        'program_id' => $program->id,
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'date_of_birth' => '2000-01-01',
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'program_id' => $program->id,
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
    $program = createLanguageProgramForRegistration();
    $course = Course::factory()->create([
        'program_id' => $program->id,
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Minor Student',
        'email' => 'minor@example.com',
        'date_of_birth' => now()->subYears(16)->toDateString(),
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'program_id' => $program->id,
        'course_id' => $course->id,
    ]);

    $response->assertSessionHasErrors('parent_email');
});

test('underage student is connected to parent account', function () {
    $program = createLanguageProgramForRegistration();
    $course = Course::factory()->create([
        'program_id' => $program->id,
    ]);

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
        'program_id' => $program->id,
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
    $program = createLanguageProgramForRegistration();
    $course = Course::factory()->create([
        'program_id' => $program->id,
    ]);

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
        'program_id' => $program->id,
        'course_id' => $course->id,
    ]);

    $response->assertSessionHasErrors('parent_email');
    $response->assertSessionHasErrors([
        'parent_email' => 'Parent account must exist and be approved.',
    ]);
});

test('student registration requires a program selection', function () {
    $program = createLanguageProgramForRegistration();
    $course = Course::factory()->create([
        'program_id' => $program->id,
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'No Program Student',
        'email' => 'no-program@example.com',
        'date_of_birth' => '2000-01-01',
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'course_id' => $course->id,
    ]);

    $response->assertSessionHasErrors('program_id');
});

test('student registration requires a course selection', function () {
    $program = createLanguageProgramForRegistration();

    $response = $this->post(route('register.store'), [
        'name' => 'No Course Student',
        'email' => 'no-course@example.com',
        'date_of_birth' => '2000-01-01',
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'program_id' => $program->id,
    ]);

    $response->assertSessionHasErrors('course_id');
});

test('student registration rejects a course without a valid price', function () {
    $program = createLanguageProgramForRegistration();

    $course = Course::factory()->create([
        'price' => 0,
        'program_id' => $program->id,
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Invalid Course Student',
        'email' => 'invalid-course@example.com',
        'date_of_birth' => '2000-01-01',
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'program_id' => $program->id,
        'course_id' => $course->id,
    ]);

    $response->assertSessionHasErrors([
        'course_id' => 'Selected course is not available for registration.',
    ]);
});

test('student registration rejects a course outside the selected program', function () {
    $englishProgram = createLanguageProgramForRegistration([
        'name' => 'English Program',
    ]);
    $frenchProgram = createLanguageProgramForRegistration([
        'code' => 'REGF',
        'locale_code' => 'reg-fr',
        'name' => 'French Program',
        'title' => 'French Program',
    ]);

    $course = Course::factory()->create([
        'name' => 'French A1',
        'program_id' => $frenchProgram->id,
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Wrong Program Student',
        'email' => 'wrong-program@example.com',
        'date_of_birth' => '2000-01-01',
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
        'program_id' => $englishProgram->id,
        'course_id' => $course->id,
    ]);

    $response->assertSessionHasErrors([
        'course_id' => 'Selected course does not belong to the selected program.',
    ]);
});
