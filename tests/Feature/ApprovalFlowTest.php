<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('user');
});

it('redirects pending users to pending-approval when accessing dashboard', function () {
    $pendingUser = User::factory()->create([
        'approved_at' => null,
        'rejected_at' => null,
        'requested_role' => 'student',
    ]);
    $pendingUser->assignRole('user');

    $response = $this
        ->actingAs($pendingUser)
        ->get('/dashboard');

    $response->assertRedirect('/pending-approval');
});

it('allows approved users to access dashboard', function () {
    $approvedUser = User::factory()->create([
        'approved_at' => now(),
        'rejected_at' => null,
        'requested_role' => null,
    ]);
    $approvedUser->assignRole('user');

    $response = $this
        ->actingAs($approvedUser)
        ->get('/dashboard');

    $response->assertOk();
});
