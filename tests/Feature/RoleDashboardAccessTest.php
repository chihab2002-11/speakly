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

test('role can access its own dashboard', function (string $role, string $routeName) {
    $user = User::factory()->create([
        'approved_at' => now(),
    ]);
    $user->assignRole($role);

    $response = $this->actingAs($user)->get(route($routeName, ['role' => $role]));

    $response->assertOk();
})->with([
    ['student', 'role.dashboard'],
    ['teacher', 'role.dashboard'],
    ['secretary', 'role.dashboard'],
    ['parent', 'role.dashboard'],
    ['admin', 'role.dashboard'],
]);

test('role cannot access another role dashboard', function () {
    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');

    $response = $this->actingAs($student)->get(route('role.dashboard', ['role' => 'teacher']));

    $response->assertForbidden();
});

test('guest is redirected to login for role dashboards', function () {
    $response = $this->get(route('role.dashboard', ['role' => 'student']));

    $response->assertRedirect(route('login'));
});
