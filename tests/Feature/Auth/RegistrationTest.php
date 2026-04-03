<?php

test('registration screen can be rendered', function () {
    // /register now redirects to the custom /register-login page
    $response = $this->get(route('register'));

    $response->assertRedirect(route('register-login', ['tab' => 'register']));

    // Verify the custom register-login page loads correctly
    $response = $this->get(route('register-login'));
    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'requested_role' => 'student',
    ]);

    $response->assertSessionHasNoErrors()
        // New users are not approved yet, so they should not reach dashboard
        ->assertRedirect(route('home', absolute: false));

    $this->assertAuthenticated();
});
