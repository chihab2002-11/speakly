<?php

use App\Models\User;
use App\Notifications\AccountUnapprovedNotification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

beforeEach(function () {
    seedAuthorizationFixtures();
});

function createApprovedAccountForUnapproval(string $role): User
{
    $account = User::factory()->create([
        'approved_at' => now(),
        'requested_role' => $role,
    ]);

    $account->assignRole($role);

    return $account;
}

function unapproveAccount(TestCase $test, User $actor, User $account): TestResponse
{
    return $test
        ->actingAs($actor)
        ->patch(route('secretary.accounts.unapprove', $account));
}

it('admin can unapprove a student', function () {
    $admin = createApprovedUserWithRole('admin');
    $student = createApprovedAccountForUnapproval('student');

    unapproveAccount($this, $admin, $student)
        ->assertRedirect(route('secretary.accounts'))
        ->assertSessionHas('success');

    $student->refresh();
    expect($student->approved_at)->toBeNull();
    expect($student->hasRole('student'))->toBeTrue();

    $notification = $student->notifications()->latest()->first();
    expect($notification)->not()->toBeNull();
    expect($notification->type)->toBe(AccountUnapprovedNotification::class);
    expect($notification->data['type'])->toBe('account_unapproved');
    expect($notification->data['actor_id'])->toBe($admin->id);
    expect($notification->data['actor_role'])->toBe('admin');
    expect($notification->data['url'])->toBe(route('pending-approval'));
});

it('admin can unapprove a teacher', function () {
    $admin = createApprovedUserWithRole('admin');
    $teacher = createApprovedAccountForUnapproval('teacher');

    unapproveAccount($this, $admin, $teacher)
        ->assertRedirect(route('secretary.accounts'))
        ->assertSessionHas('success');

    $teacher->refresh();
    expect($teacher->approved_at)->toBeNull();
    expect($teacher->hasRole('teacher'))->toBeTrue();
});

it('admin can unapprove a parent', function () {
    $admin = createApprovedUserWithRole('admin');
    $parent = createApprovedAccountForUnapproval('parent');

    unapproveAccount($this, $admin, $parent)
        ->assertRedirect(route('secretary.accounts'))
        ->assertSessionHas('success');

    $parent->refresh();
    expect($parent->approved_at)->toBeNull();
    expect($parent->hasRole('parent'))->toBeTrue();
});

it('admin can unapprove a secretary', function () {
    $admin = createApprovedUserWithRole('admin');
    $secretary = createApprovedAccountForUnapproval('secretary');

    unapproveAccount($this, $admin, $secretary)
        ->assertRedirect(route('secretary.accounts'))
        ->assertSessionHas('success');

    $secretary->refresh();
    expect($secretary->approved_at)->toBeNull();
    expect($secretary->hasRole('secretary'))->toBeTrue();
});

it('admin can unapprove another admin', function () {
    $admin = createApprovedUserWithRole('admin');
    $otherAdmin = createApprovedAccountForUnapproval('admin');

    unapproveAccount($this, $admin, $otherAdmin)
        ->assertRedirect(route('secretary.accounts'))
        ->assertSessionHas('success');

    $otherAdmin->refresh();
    expect($otherAdmin->approved_at)->toBeNull();
    expect($otherAdmin->hasRole('admin'))->toBeTrue();
});

it('secretary can unapprove a student', function () {
    $secretary = createApprovedUserWithRole('secretary');
    $student = createApprovedAccountForUnapproval('student');

    unapproveAccount($this, $secretary, $student)
        ->assertRedirect(route('secretary.accounts'))
        ->assertSessionHas('success');

    expect($student->fresh()->approved_at)->toBeNull();
});

it('secretary can unapprove a teacher', function () {
    $secretary = createApprovedUserWithRole('secretary');
    $teacher = createApprovedAccountForUnapproval('teacher');

    unapproveAccount($this, $secretary, $teacher)
        ->assertRedirect(route('secretary.accounts'))
        ->assertSessionHas('success');

    expect($teacher->fresh()->approved_at)->toBeNull();
});

it('secretary can unapprove a parent', function () {
    $secretary = createApprovedUserWithRole('secretary');
    $parent = createApprovedAccountForUnapproval('parent');

    unapproveAccount($this, $secretary, $parent)
        ->assertRedirect(route('secretary.accounts'))
        ->assertSessionHas('success');

    expect($parent->fresh()->approved_at)->toBeNull();
});

it('secretary cannot unapprove an admin', function () {
    $secretary = createApprovedUserWithRole('secretary');
    $admin = createApprovedAccountForUnapproval('admin');

    unapproveAccount($this, $secretary, $admin)->assertForbidden();

    expect($admin->fresh()->approved_at)->not->toBeNull();
});

it('secretary cannot unapprove another secretary', function () {
    $secretary = createApprovedUserWithRole('secretary');
    $otherSecretary = createApprovedAccountForUnapproval('secretary');

    unapproveAccount($this, $secretary, $otherSecretary)->assertForbidden();

    expect($otherSecretary->fresh()->approved_at)->not->toBeNull();
});

it('student teacher and parent cannot unapprove users', function (string $role) {
    $actor = createApprovedUserWithRole($role);
    $student = createApprovedAccountForUnapproval('student');

    unapproveAccount($this, $actor, $student)->assertForbidden();

    expect($student->fresh()->approved_at)->not->toBeNull();
})->with(['student', 'teacher', 'parent']);

it('unapproved user is blocked from approved-only dashboard routes', function () {
    $admin = createApprovedUserWithRole('admin');
    $student = createApprovedAccountForUnapproval('student');

    unapproveAccount($this, $admin, $student)->assertRedirect(route('secretary.accounts'));

    $this->actingAs($student->fresh())
        ->get(route('role.dashboard', ['role' => 'student']))
        ->assertRedirect(route('pending-approval'));
});

it('admin cannot unapprove themselves', function () {
    $admin = createApprovedUserWithRole('admin');

    unapproveAccount($this, $admin, $admin)
        ->assertRedirect(route('secretary.accounts'))
        ->assertSessionHasErrors('account');

    expect($admin->fresh()->approved_at)->not->toBeNull();
});
