<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('issues a token and returns the authenticated api user', function () {
    Role::create(['name' => 'student', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'email' => 'student@example.com',
        'password' => Hash::make('secret-password'),
        'approved_at' => now(),
    ]);
    $user->assignRole('student');

    $login = $this->postJson('/api/login', [
        'email' => 'student@example.com',
        'password' => 'secret-password',
        'device_name' => 'FlutterFlow',
    ]);

    $login
        ->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('role', 'student')
        ->assertJsonPath('user.email', 'student@example.com')
        ->assertJsonStructure([
            'token',
            'token_type',
            'role',
            'user' => ['id', 'name', 'email', 'roles'],
        ]);

    $token = $login->json('token');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('role', 'student')
        ->assertJsonPath('user.email', 'student@example.com');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out.');

    expect(PersonalAccessToken::query()->count())->toBe(0);

    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/me')
        ->assertUnauthorized();
});

it('does not issue tokens to unapproved users', function () {
    Role::create(['name' => 'student', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'email' => 'pending@example.com',
        'password' => Hash::make('secret-password'),
        'approved_at' => null,
    ]);
    $user->assignRole('student');

    $this->postJson('/api/login', [
        'email' => 'pending@example.com',
        'password' => 'secret-password',
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'Account is pending approval.');

    expect(PersonalAccessToken::query()->count())->toBe(0);
});
