<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('teacher', 'web');
});

it('teacher can update their own profile settings', function () {
    $teacher = createApprovedTeacher();

    $response = $this->actingAs($teacher)->patch(route('teacher.settings.update'), [
        'name' => 'Nadia Updated',
        'email' => 'nadia.updated@example.com',
        'phone' => '+213555123456',
        'preferred_language' => 'french',
        'bio' => 'Updated teacher bio.',
    ]);

    $response
        ->assertRedirect(route('teacher.settings'))
        ->assertSessionHas('success');

    $teacher->refresh();

    expect($teacher->name)->toBe('Nadia Updated');
    expect($teacher->email)->toBe('nadia.updated@example.com');
    expect($teacher->phone)->toBe('+213555123456');
    expect($teacher->preferred_language)->toBe('french');
    expect($teacher->bio)->toBe('Updated teacher bio.');
    expect($teacher->email_verified_at)->toBeNull();
});

it('invalid teacher settings updates are rejected', function () {
    $teacher = createApprovedTeacher();

    $response = $this->actingAs($teacher)->patch(route('teacher.settings.update'), [
        'name' => '',
        'email' => 'invalid-email',
        'phone' => str_repeat('9', 30),
        'preferred_language' => 'italian',
        'bio' => str_repeat('a', 1100),
    ]);

    $response->assertSessionHasErrors([
        'name',
        'email',
        'phone',
        'preferred_language',
        'bio',
    ]);
});

it('teacher cannot update another users settings through payload tampering', function () {
    $teacher = createApprovedTeacher([
        'name' => 'Teacher One',
        'email' => 'teacher.one@example.com',
    ]);

    $otherTeacher = createApprovedTeacher([
        'name' => 'Teacher Two',
        'email' => 'teacher.two@example.com',
    ]);

    $this->actingAs($teacher)->patch(route('teacher.settings.update'), [
        'user_id' => $otherTeacher->id,
        'name' => 'Teacher One Updated',
        'email' => 'teacher.one.updated@example.com',
        'phone' => '+213500000000',
        'preferred_language' => 'english',
        'bio' => 'Only my profile should update.',
    ])->assertRedirect(route('teacher.settings'));

    expect($teacher->refresh()->name)->toBe('Teacher One Updated');
    expect($otherTeacher->refresh()->name)->toBe('Teacher Two');
});
