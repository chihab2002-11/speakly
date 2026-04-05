<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('student', 'web');
});

test('guests are redirected to the login page for role dashboard', function () {
    $response = $this->get(route('role.dashboard', ['role' => 'student']));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit their role dashboard', function () {
    $user = User::factory()->create([
        'approved_at' => now(),
    ]);
    $user->assignRole('student');

    $this->actingAs($user);

    $response = $this->get(route('role.dashboard', ['role' => 'student']));
    $response->assertOk();
});

test('legacy dashboard route redirects to role dashboard', function () {
    $user = User::factory()->create([
        'approved_at' => now(),
    ]);
    $user->assignRole('student');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirectToRoute('role.dashboard', ['role' => 'student']);
});
