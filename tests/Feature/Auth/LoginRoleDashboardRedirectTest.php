<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('student', 'web');
    Role::findOrCreate('teacher', 'web');
    Role::findOrCreate('secretary', 'web');
    Role::findOrCreate('parent', 'web');
    Role::findOrCreate('admin', 'web');
});

test('approved users are redirected to their role dashboard after login', function (string $role, string $expectedRoute) {
    $user = User::factory()->create([
        'approved_at' => now(),
    ]);
    $user->assignRole($role);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirectToRoute($expectedRoute, ['role' => $role]);
    $this->assertAuthenticatedAs($user);
})->with([
    ['student', 'role.dashboard'],
    ['teacher', 'role.dashboard'],
    ['secretary', 'role.dashboard'],
    ['parent', 'role.dashboard'],
    ['admin', 'role.dashboard'],
]);

test('pending users are redirected to pending approval after login', function () {
    $user = User::factory()->create([
        'approved_at' => null,
    ]);
    $user->assignRole('student');

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirectToRoute('pending-approval');
});
