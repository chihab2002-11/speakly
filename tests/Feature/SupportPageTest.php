<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedAuthorizationFixtures();
});

it('shows the shared support page for every approved role', function (string $role) {
    /** @var TestCase $this */
    $user = User::factory()->create([
        'approved_at' => now(),
        'requested_role' => $role,
    ]);
    $user->assignRole($role);

    $response = $this->actingAs($user)->get(route('support'));

    $response->assertOk();
    $response->assertSee('How to use the platform');
    $response->assertSee('Common questions');
    $response->assertSee('How do I register?');
    $response->assertSee('Contact administration');
    $response->assertSee('support@speakly.com');
})->with([
    'student',
    'teacher',
    'parent',
    'secretary',
    'admin',
]);

it('uses the same support route in role sidebars and headers', function () {
    /** @var TestCase $this */
    $teacher = User::factory()->create([
        'approved_at' => now(),
        'requested_role' => 'teacher',
    ]);
    $teacher->assignRole('teacher');

    $this->actingAs($teacher)
        ->get(route('support'))
        ->assertOk()
        ->assertSee('href="'.route('support').'"', false)
        ->assertSee('aria-label="Support"', false);
});
