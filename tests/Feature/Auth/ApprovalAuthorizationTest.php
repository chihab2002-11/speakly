<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure roles exist for tests
    Role::findOrCreate('admin');
    Role::findOrCreate('secretary');
    Role::findOrCreate('student');
    Role::findOrCreate('parent');
    Role::findOrCreate('user');
});

it('prevents normal user from approving a pending user', function () {
    $normalUser = User::factory()->create([
        'approved_at' => now(),
        'requested_role' => null,
    ]);
    $normalUser->assignRole('user');

    $pendingUser = User::factory()->create([
        'approved_at' => null,
        'rejected_at' => null,
        'requested_role' => 'student',
    ]);

    $response = $this
        ->actingAs($normalUser)
        ->post(route('approvals.approve', $pendingUser));

    $response->assertForbidden();
});

it('prevents normal user from rejecting a pending user', function () {
    $normalUser = User::factory()->create([
        'approved_at' => now(),
        'requested_role' => null,
    ]);
    $normalUser->assignRole('user');

    $pendingUser = User::factory()->create([
        'approved_at' => null,
        'rejected_at' => null,
        'requested_role' => 'student',
    ]);

    $response = $this
        ->actingAs($normalUser)
        ->post(route('approvals.reject', $pendingUser), [
            'reason' => 'Not eligible',
        ]);

    $response->assertForbidden();
});
