<?php

use App\Models\Course;
use App\Models\LanguageProgram;
use App\Models\User;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

function registerSessionExpirationTestRoute(): void
{
    Route::post('/__tests/session-expired', function (): never {
        throw new TokenMismatchException;
    })->middleware('web');
}

it('redirects an expired web request to the login register page', function () {
    /** @var TestCase $this */
    registerSessionExpirationTestRoute();

    $response = $this->post('/__tests/session-expired');

    $response
        ->assertRedirect(route('register-login'))
        ->assertSessionHas('status', 'Your session has expired. Please log in again.');

    $this->assertGuest();
});

it('displays the session expired message on the login register page', function () {
    /** @var TestCase $this */
    $this->withSession([
        'status' => 'Your session has expired. Please log in again.',
    ]);

    $this->get(route('register-login'))
        ->assertOk()
        ->assertSee('Your session has expired. Please log in again.');
});

it('returns a json 419 response for expired json requests', function () {
    /** @var TestCase $this */
    registerSessionExpirationTestRoute();

    $this->postJson('/__tests/session-expired')
        ->assertStatus(419)
        ->assertJson([
            'message' => 'Your session has expired. Please log in again.',
        ]);
});

it('keeps csrf protection enabled for web forms', function () {
    /** @var TestCase $this */
    registerSessionExpirationTestRoute();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/__tests/session-expired')
        ->assertRedirect(route('register-login'));

    $this->assertGuest();
});

it('still allows normal login and registration with valid csrf tokens', function () {
    /** @var TestCase $this */
    $this->withMiddleware();
    Role::findOrCreate('parent', 'web');

    $user = User::factory()->create();

    $loginResponse = $this
        ->withSession(['_token' => 'valid-login-token'])
        ->post(route('login.store'), [
            '_token' => 'valid-login-token',
            'email' => $user->email,
            'password' => 'password',
        ]);

    $loginResponse
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('pending-approval');

    $this->assertAuthenticatedAs($user);
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();

    LanguageProgram::query()->create([
        'code' => 'SESSION',
        'locale_code' => 'session',
        'name' => 'Session Program',
        'title' => 'Session Program',
        'description' => 'Session test language program.',
        'full_description' => 'Session test language program full description.',
        'flag_url' => 'https://example.com/session.svg',
        'sort_order' => 1,
        'is_active' => true,
    ]);
    Course::factory()->create(['price' => 12000]);

    $registerResponse = $this
        ->withSession(['_token' => 'valid-register-token'])
        ->post(route('register.store'), [
            '_token' => 'valid-register-token',
            'name' => 'Session Student',
            'email' => 'session-student@example.com',
            'date_of_birth' => '2000-01-01',
            'password' => 'password',
            'password_confirmation' => 'password',
            'requested_role' => 'parent',
        ]);

    $registerResponse
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('pending-approval');

    $this->assertAuthenticated();
});
