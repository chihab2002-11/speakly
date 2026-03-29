<?php

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

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
        ->assertRedirect(route('pending-approval', absolute: false));

    $this->assertAuthenticated();
});
