<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\TuitionPayment;
use App\Models\User;
use App\Notifications\SecretaryAnnouncementNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['secretary', 'student', 'parent', 'teacher', 'admin'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

function createApprovedSecretaryForOperations(): User
{
    $secretary = User::factory()->create([
        'approved_at' => now(),
    ]);
    $secretary->assignRole('secretary');

    return $secretary;
}

it('renders secretary registrations using secretary view', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $response = $this->actingAs($secretary)
        ->get(route('secretary.registrations'));

    $response->assertOk();
    $response->assertViewIs('secretary.registrations');
    $response->assertSee('Create Account');
});

it('creates pending account from secretary registrations form', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $response = $this->actingAs($secretary)
        ->post(route('secretary.registrations.store'), [
            'name' => 'Pending Teacher',
            'email' => 'pending.teacher@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'requested_role' => 'teacher',
            'date_of_birth' => '1996-05-10',
        ]);

    $response->assertRedirect(route('secretary.registrations'));

    $created = User::query()->where('email', 'pending.teacher@example.com')->first();

    expect($created)->not->toBeNull();
    expect($created?->requested_role)->toBe('teacher');
    expect($created?->approved_at)->toBeNull();
    expect($created?->rejected_at)->toBeNull();
});

it('secretary-created registration appears in approvals queue', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $this->actingAs($secretary)
        ->post(route('secretary.registrations.store'), [
            'name' => 'Queue Student',
            'email' => 'queue.student@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'requested_role' => 'student',
            'date_of_birth' => '2001-03-14',
        ])
        ->assertRedirect(route('secretary.registrations'));

    $this->actingAs($secretary)
        ->get(route('approvals.index', ['role' => 'secretary']))
        ->assertOk()
        ->assertViewIs('approvals.index')
        ->assertSee('queue.student@example.com');
});

it('renders secretary payments page', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');

    $response = $this->actingAs($secretary)->get(route('secretary.payments'));

    $response->assertOk();
    $response->assertViewIs('secretary.payments');
    $response->assertSee('Student Payments');
});

it('records a student payment from secretary payments page', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');

    $this->actingAs($secretary)
        ->post(route('secretary.payments.store'), [
            'student_id' => $student->id,
            'amount' => 12000,
            'method' => 'cash',
            'reference' => 'PAY-TEST-001',
        ])
        ->assertRedirect(route('secretary.payments'));

    $this->assertDatabaseHas('tuition_payments', [
        'student_id' => $student->id,
        'recorded_by' => $secretary->id,
        'amount' => 12000,
        'method' => 'cash',
        'reference' => 'PAY-TEST-001',
    ]);

    expect(TuitionPayment::query()->count())->toBe(1);
});

it('renders secretary groups page with group data', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $course = Course::factory()->create();
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $room = Room::factory()->create();

    Schedule::factory()->create([
        'class_id' => $group->id,
        'room_id' => $room->id,
        'day_of_week' => 'monday',
    ]);

    $response = $this->actingAs($secretary)->get(route('secretary.groups'));

    $response->assertOk();
    $response->assertViewIs('secretary.groups');
    $response->assertSee('Manage Groups');
});

it('secretary can create group from course and enroll student', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $course = Course::factory()->create(['name' => 'English B1']);

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $this->actingAs($secretary)
        ->post(route('secretary.groups.store'), [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'capacity' => 20,
        ])
        ->assertRedirect(route('secretary.groups'));

    $group = CourseClass::query()->where('course_id', $course->id)->first();

    expect($group)->not->toBeNull();
    expect((int) $group?->capacity)->toBe(20);

    $this->actingAs($secretary)
        ->post(route('secretary.groups.enroll'), [
            'class_id' => $group?->id,
            'student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'));

    $this->assertDatabaseHas('class_student', [
        'class_id' => $group?->id,
        'user_id' => $student->id,
    ]);
});

it('secretary can update and delete group without schedules', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $courseA = Course::factory()->create(['name' => 'Course A']);
    $courseB = Course::factory()->create(['name' => 'Course B']);

    $teacherA = User::factory()->create(['approved_at' => now()]);
    $teacherA->assignRole('teacher');

    $teacherB = User::factory()->create(['approved_at' => now()]);
    $teacherB->assignRole('teacher');

    $group = CourseClass::factory()->create([
        'course_id' => $courseA->id,
        'teacher_id' => $teacherA->id,
        'capacity' => 25,
    ]);

    $this->actingAs($secretary)
        ->patch(route('secretary.groups.update', $group), [
            'course_id' => $courseB->id,
            'teacher_id' => $teacherB->id,
            'capacity' => 35,
        ])
        ->assertRedirect(route('secretary.groups'));

    $group->refresh();
    expect($group->course_id)->toBe($courseB->id);
    expect($group->teacher_id)->toBe($teacherB->id);
    expect((int) $group->capacity)->toBe(35);

    $this->actingAs($secretary)
        ->delete(route('secretary.groups.destroy', $group))
        ->assertRedirect(route('secretary.groups'));

    $this->assertDatabaseMissing('classes', [
        'id' => $group->id,
    ]);
});

it('secretary cannot delete group with schedules', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $course = Course::factory()->create();
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $room = Room::factory()->create();
    Schedule::factory()->create([
        'class_id' => $group->id,
        'room_id' => $room->id,
    ]);

    $this->actingAs($secretary)
        ->delete(route('secretary.groups.destroy', $group))
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHasErrors('group');

    $this->assertDatabaseHas('classes', [
        'id' => $group->id,
    ]);
});

it('renders secretary accounts page', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');

    $response = $this->actingAs($secretary)->get(route('secretary.accounts'));

    $response->assertOk();
    $response->assertViewIs('secretary.accounts');
    $response->assertSee('Manage Accounts');
});

it('secretary can edit managed account details', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $account = User::factory()->create([
        'name' => 'Old Student Name',
        'email' => 'old.student@example.com',
        'requested_role' => 'student',
        'approved_at' => now(),
    ]);
    $account->assignRole('student');

    $this->actingAs($secretary)
        ->patch(route('secretary.accounts.update', $account), [
            'name' => 'Updated Account Name',
            'email' => 'updated.student@example.com',
            'requested_role' => 'parent',
            'date_of_birth' => '2005-02-11',
        ])
        ->assertRedirect(route('secretary.accounts'));

    $account->refresh();
    expect($account->name)->toBe('Updated Account Name');
    expect($account->email)->toBe('updated.student@example.com');
    expect($account->requested_role)->toBe('parent');
    expect($account->hasRole('parent'))->toBeTrue();
});

it('secretary can delete managed account without dependencies', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $account = User::factory()->create([
        'approved_at' => now(),
        'requested_role' => 'teacher',
    ]);
    $account->assignRole('teacher');

    $this->actingAs($secretary)
        ->delete(route('secretary.accounts.destroy', $account))
        ->assertRedirect(route('secretary.accounts'));

    $this->assertDatabaseMissing('users', [
        'id' => $account->id,
    ]);
});

it('publishes secretary notifications to selected audience', function () {
    /** @var TestCase $this */
    Notification::fake();

    $secretary = createApprovedSecretaryForOperations();

    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $response = $this->actingAs($secretary)
        ->post(route('secretary.publish-notifications.send'), [
            'title' => 'Important Update',
            'message' => 'Please review the updated schedule.',
            'audience' => 'students',
            'include_secretaries' => false,
        ]);

    $response->assertRedirect(route('secretary.publish-notifications'));

    Notification::assertSentTo($student, SecretaryAnnouncementNotification::class);
    Notification::assertNotSentTo($teacher, SecretaryAnnouncementNotification::class);
});

it('renders secretary settings page', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $response = $this->actingAs($secretary)->get(route('secretary.settings'));

    $response->assertOk();
    $response->assertViewIs('secretary.settings');
    $response->assertSee('Profile Settings');
});
