<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('student can view their timetable', function () {
    // Create roles
    Role::create(['name' => 'student', 'guard_name' => 'web']);

    // Create student with role
    $student = User::factory()->create([
        'email' => 'student@test.com',
        'approved_at' => now(),
    ]);
    $student->syncRoles(['student']);

    $response = $this->actingAs($student)->get(route('timetable.index'));

    $response->assertStatus(200);
    $response->assertViewIs('timetable.index');
    $response->assertViewHas('enrolledClasses');
    $response->assertViewHas('timetable');
});

it('non-student cannot view timetable', function () {
    // Create roles
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create(['approved_at' => now()]);
    $admin->syncRoles(['admin']);

    $response = $this->actingAs($admin)->get(route('timetable.index'));

    $response->assertStatus(403);
});

it('unauthenticated user cannot view timetable', function () {
    $response = $this->get(route('timetable.index'));

    $response->assertRedirect(route('login'));
});
