<?php

use App\Models\Course;
use App\Models\LanguageProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedAuthorizationFixtures();
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

test('deleting a language program nulls assigned course program_id', function () {
    $admin = createAdminUser();

    $program = LanguageProgram::query()->create([
        'code' => 'de',
        'locale_code' => 'DE-DE',
        'name' => 'German',
        'title' => 'German Track',
        'description' => 'Program description',
        'full_description' => 'Long program description',
        'flag_url' => 'https://flagcdn.com/w80/de.png',
        'sort_order' => 1,
        'is_active' => true,
        'certifications' => [],
    ]);

    $course = Course::factory()->create([
        'program_id' => $program->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.programs.destroy', $program))
        ->assertRedirect();

    $this->assertDatabaseMissing('language_programs', [
        'id' => $program->id,
    ]);

    $this->assertDatabaseHas('courses', [
        'id' => $course->id,
        'program_id' => null,
    ]);
});

test('admin can create a language program without a flag', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin)->post(route('admin.programs.store'), [
        'code' => 'ar',
        'locale_code' => 'AR-AR',
        'name' => 'Arabic',
        'title' => 'Arabic Excellence',
        'description' => 'Program description',
        'full_description' => 'Long program description',
        'flag_url' => null,
        'is_active' => '1',
    ]);

    $response->assertRedirect();

    $program = LanguageProgram::query()->where('code', 'ar')->first();
    expect($program)->not->toBeNull();
    expect($program?->flag_url)->toBeNull();
    expect($program?->title)->toBe('Arabic Excellence');
});

test('visitor page displays fallback code when program has no flag', function () {
    LanguageProgram::query()->create([
        'code' => 'ja',
        'locale_code' => 'JA-JP',
        'name' => 'Japanese',
        'title' => 'Japanese Program',
        'description' => 'Learn Japanese',
        'full_description' => 'Comprehensive Japanese program',
        'flag_url' => null,
        'sort_order' => 1,
        'is_active' => true,
        'certifications' => [],
    ]);

    LanguageProgram::query()->create([
        'code' => 'ko',
        'locale_code' => 'KO-KR',
        'name' => 'Korean English',
        'title' => 'Korean Program',
        'description' => 'Learn Korean',
        'full_description' => 'Comprehensive Korean program',
        'flag_url' => null,
        'sort_order' => 2,
        'is_active' => true,
        'certifications' => [],
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Japanese Program');
    $response->assertSee('Korean Program');
    // Should display the generated code badge instead of flag
    $response->assertSee('JA', false);
    $response->assertSee('KE', false);
});

test('visitor page displays flag image when program has flag', function () {
    LanguageProgram::query()->create([
        'code' => 'en',
        'locale_code' => 'EN-GB',
        'name' => 'English',
        'title' => 'English Program',
        'description' => 'Learn English',
        'full_description' => 'Comprehensive English program',
        'flag_url' => 'https://flagcdn.com/w80/gb.png',
        'sort_order' => 1,
        'is_active' => true,
        'certifications' => [],
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('https://flagcdn.com/w80/gb.png');
});

test('admin can update program to remove flag', function () {
    $admin = createAdminUser();

    $program = LanguageProgram::query()->create([
        'code' => 'pt',
        'locale_code' => 'PT-PT',
        'name' => 'Portuguese',
        'title' => 'Portuguese Program',
        'description' => 'Program description',
        'full_description' => 'Long program description',
        'flag_url' => 'https://flagcdn.com/w80/pt.png',
        'sort_order' => 1,
        'is_active' => true,
        'certifications' => [],
    ]);

    $this->actingAs($admin)->patch(route('admin.programs.update', $program), [
        'code' => 'pt',
        'locale_code' => 'PT-PT',
        'name' => 'Portuguese',
        'title' => 'Portuguese Program',
        'description' => 'Updated description',
        'full_description' => 'Updated full description',
        'flag_url' => null,
        'is_active' => '1',
    ])->assertRedirect();

    $this->assertDatabaseHas('language_programs', [
        'id' => $program->id,
        'flag_url' => null,
        'title' => 'Portuguese Program',
    ]);
});
