<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedAuthorizationFixtures();
});

test('admin can access secretary operations pages', function () {
    /** @var TestCase $this */
    $admin = User::factory()->create([
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('secretary.registrations'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('secretary.payments'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('secretary.groups'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('secretary.accounts'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('secretary.publish-notifications'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('secretary.settings'))
        ->assertOk();
});

test('admin sees combined admin and secretary sidebar with mode marker on secretary pages', function () {
    /** @var TestCase $this */
    $admin = User::factory()->create([
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)
        ->get(route('secretary.groups'));

    $response->assertOk();
    $response->assertSee('Manage Schedule');
    $response->assertSee('Manage Courses');
    $response->assertSee('Secretary Mode');
    $response->assertSee('Students List');
    $response->assertSee(route('secretary.groups'), false);
});
