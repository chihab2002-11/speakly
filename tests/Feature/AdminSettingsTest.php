<?php

beforeEach(function () {
    seedAuthorizationFixtures();
});

it('admin can open settings from the admin area and update profile details', function () {
    $admin = createApprovedUserWithRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.settings'))
        ->assertOk()
        ->assertSee('Admin Settings')
        ->assertSee(route('admin.settings'), false)
        ->assertDontSee('Upload New Photo')
        ->assertDontSee('Max size of 800K');

    $this->actingAs($admin)
        ->patch(route('admin.settings.update'), [
            'name' => 'Updated Admin',
            'email' => 'updated.admin@example.com',
            'phone' => '+213555222333',
            'preferred_language' => 'french',
            'bio' => 'Admin profile bio.',
        ])
        ->assertRedirect(route('admin.settings'))
        ->assertSessionHas('success');

    $admin->refresh();

    expect($admin->name)->toBe('Updated Admin');
    expect($admin->email)->toBe('updated.admin@example.com');
    expect($admin->phone)->toBe('+213555222333');
    expect($admin->preferred_language)->toBe('french');
    expect($admin->bio)->toBe('Admin profile bio.');
});

it('settings pages no longer show photo upload controls', function () {
    $rolesAndRoutes = [
        'admin' => 'admin.settings',
        'teacher' => 'teacher.settings',
        'student' => 'student.settings',
        'parent' => 'parent.settings',
        'secretary' => 'secretary.settings',
    ];

    foreach ($rolesAndRoutes as $role => $routeName) {
        $user = createApprovedUserWithRole($role);

        $this->actingAs($user)
            ->get(route($routeName))
            ->assertOk()
            ->assertDontSee('Upload New Photo')
            ->assertDontSee('Upload Photo')
            ->assertDontSee('Max size of 800K');
    }
});
