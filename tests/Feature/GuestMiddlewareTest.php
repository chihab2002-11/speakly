<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('guest can access register-login page', function () {
    $response = $this->get('/register-login');

    $response->assertStatus(200);
    $response->assertViewIs('register-login-page');
});

test('authenticated user is redirected away from register-login page', function () {
    // Create the student role first
    Role::create(['name' => 'student', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'approved_at' => now(),
    ]);
    $user->assignRole('student');

    $response = $this->actingAs($user)->get('/register-login');

    $response->assertRedirect();
});
