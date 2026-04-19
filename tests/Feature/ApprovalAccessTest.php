<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedAuthorizationFixtures();
});

function createApprovedUserWithRole(string $role): User
{
    $user = User::factory()->create([
        'approved_at' => now(),
    ]);
    $user->assignRole($role);

    return $user;
}

it('redirects admin /approvals shortcut to role-scoped approvals', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');

    $this->actingAs($admin)
        ->get('/approvals')
        ->assertRedirect(route('approvals.index', ['role' => 'admin']));
});

it('redirects secretary /approvals shortcut to role-scoped approvals', function () {
    /** @var TestCase $this */
    $secretary = createApprovedUserWithRole('secretary');

    $this->actingAs($secretary)
        ->get('/approvals')
        ->assertRedirect(route('approvals.index', ['role' => 'secretary']));
});

it('redirects typo /aprovals to role-scoped approvals', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');

    $this->actingAs($admin)
        ->get('/aprovals')
        ->assertRedirect(route('approvals.index', ['role' => 'admin']));
});

it('allows secretary to approve teacher requests', function () {
    /** @var TestCase $this */
    $secretary = createApprovedUserWithRole('secretary');

    $pendingTeacher = User::factory()->create([
        'approved_at' => null,
        'rejected_at' => null,
        'requested_role' => 'teacher',
    ]);

    $this->actingAs($secretary)
        ->post(route('approvals.approve', ['role' => 'secretary', 'user' => $pendingTeacher]))
        ->assertRedirect(route('approvals.index', ['role' => 'secretary']));

    $pendingTeacher->refresh();

    expect($pendingTeacher->approved_at)->not->toBeNull();
    expect($pendingTeacher->hasRole('teacher'))->toBeTrue();
});

it('prevents secretary from approving office requests', function () {
    /** @var TestCase $this */
    $secretary = createApprovedUserWithRole('secretary');

    $pendingSecretary = User::factory()->create([
        'approved_at' => null,
        'rejected_at' => null,
        'requested_role' => 'secretary',
    ]);

    $this->actingAs($secretary)
        ->post(route('approvals.approve', ['role' => 'secretary', 'user' => $pendingSecretary]))
        ->assertForbidden();

    $pendingSecretary->refresh();

    expect($pendingSecretary->approved_at)->toBeNull();
    expect($pendingSecretary->hasRole('secretary'))->toBeFalse();
});

it('allows admin to approve office requests', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');

    $pendingSecretary = User::factory()->create([
        'approved_at' => null,
        'rejected_at' => null,
        'requested_role' => 'secretary',
    ]);

    $this->actingAs($admin)
        ->post(route('approvals.approve', ['role' => 'admin', 'user' => $pendingSecretary]))
        ->assertRedirect(route('approvals.index', ['role' => 'admin']));

    $pendingSecretary->refresh();

    expect($pendingSecretary->approved_at)->not->toBeNull();
    expect($pendingSecretary->hasRole('secretary'))->toBeTrue();
});
