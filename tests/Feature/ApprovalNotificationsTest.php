<?php

use App\Models\User;
use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('admin');
    Role::findOrCreate('student');
});

it('sends approved notification when admin approves a pending user', function () {
    Notification::fake();

    $admin = User::factory()->create([
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $pendingUser = User::factory()->create([
        'approved_at' => null,
        'rejected_at' => null,
        'requested_role' => 'student',
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('approvals.approve', $pendingUser));

    $response->assertRedirect(route('approvals.index'));

    Notification::assertSentTo($pendingUser, AccountApprovedNotification::class);
});

it('sends rejected notification when admin rejects a pending user', function () {
    Notification::fake();

    $admin = User::factory()->create([
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $pendingUser = User::factory()->create([
        'approved_at' => null,
        'rejected_at' => null,
        'requested_role' => 'student',
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('approvals.reject', $pendingUser), [
            'reason' => 'Incomplete profile',
        ]);

    $response->assertRedirect(route('approvals.index'));

    Notification::assertSentTo($pendingUser, AccountRejectedNotification::class);
});
