<?php

use App\Models\LanguageProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
});

function createAdminUser(): User
{
    $admin = User::factory()->create([
        'approved_at' => now(),
    ]);

    $admin->assignRole('admin');

    return $admin;
}

test('admin can view dashboard with language programs table', function () {
    $admin = createAdminUser();

    LanguageProgram::query()->create([
        'code' => 'en',
        'locale_code' => 'EN-GB',
        'name' => 'English',
        'title' => 'English Mastery',
        'description' => 'Short description',
        'full_description' => 'Full description',
        'flag_url' => 'https://flagcdn.com/w80/gb.png',
        'sort_order' => 1,
        'is_active' => true,
        'certifications' => [],
    ]);

    $response = $this->actingAs($admin)->get(route('role.dashboard', ['role' => 'admin']));

    $response
        ->assertOk()
        ->assertSee('Program Management')
        ->assertSee('English Mastery');
});

test('admin can create a language program', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin)->post(route('admin.programs.store'), [
        'code' => 'es',
        'locale_code' => 'ES-ES',
        'name' => 'Spanish',
        'title' => 'Spanish Immersion',
        'description' => 'Program description',
        'full_description' => 'Long program description',
        'flag_url' => 'https://flagcdn.com/w80/es.png',
        'is_active' => '1',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('language_programs', [
        'code' => 'es',
        'title' => 'Spanish Immersion',
        'is_active' => 1,
    ]);
});

test('admin can update and delete a language program', function () {
    $admin = createAdminUser();

    $program = LanguageProgram::query()->create([
        'code' => 'fr',
        'locale_code' => 'FR-FR',
        'name' => 'French',
        'title' => 'French Program',
        'description' => 'Program description',
        'full_description' => 'Long program description',
        'flag_url' => 'https://flagcdn.com/w80/fr.png',
        'sort_order' => 1,
        'is_active' => true,
        'certifications' => [],
    ]);

    $this->actingAs($admin)->patch(route('admin.programs.update', $program), [
        'code' => 'fr',
        'locale_code' => 'FR-FR',
        'name' => 'French',
        'title' => 'French Excellence',
        'description' => 'Updated description',
        'full_description' => 'Updated full description',
        'flag_url' => 'https://flagcdn.com/w80/fr.png',
        'is_active' => '0',
    ])->assertRedirect();

    $this->assertDatabaseHas('language_programs', [
        'id' => $program->id,
        'title' => 'French Excellence',
        'is_active' => 0,
    ]);

    $this->actingAs($admin)->delete(route('admin.programs.destroy', $program))->assertRedirect();

    $this->assertDatabaseMissing('language_programs', [
        'id' => $program->id,
    ]);
});
