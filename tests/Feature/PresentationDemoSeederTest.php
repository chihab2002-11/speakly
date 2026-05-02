<?php

use App\Models\ScholarshipActivation;
use App\Models\TuitionPayment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PresentationDemoSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not connect adult presentation students to parent accounts', function () {
    $this->seed([
        PermissionSeeder::class,
        RoleSeeder::class,
        PresentationDemoSeeder::class,
    ]);

    $alex = User::query()->where('email', 'student.alex@lumina.test')->firstOrFail();
    $omar = User::query()->where('email', 'student.omar@lumina.test')->firstOrFail();
    $lina = User::query()->where('email', 'student.lina@lumina.test')->firstOrFail();
    $yacine = User::query()->where('email', 'student.yacine@lumina.test')->firstOrFail();
    $sara = User::query()->where('email', 'student.sara@lumina.test')->firstOrFail();

    expect($alex->student_age)->toBeGreaterThanOrEqual(18);
    expect($omar->student_age)->toBeGreaterThanOrEqual(18);
    expect($alex->parent_id)->toBeNull();
    expect($omar->parent_id)->toBeNull();

    expect($lina->student_age)->toBeLessThan(18);
    expect($yacine->student_age)->toBeLessThan(18);
    expect($sara->student_age)->toBeLessThan(18);
    expect($lina->parent_id)->not()->toBeNull();
    expect($yacine->parent_id)->not()->toBeNull();
    expect($sara->parent_id)->not()->toBeNull();

    $adultLinkedStudents = User::query()
        ->role('student')
        ->where('email', 'like', 'student.%@lumina.test')
        ->whereNotNull('parent_id')
        ->get()
        ->filter(fn (User $student): bool => CarbonImmutable::parse($student->date_of_birth)->age >= 18);

    expect($adultLinkedStudents)->toHaveCount(0);

    expect(TuitionPayment::query()->whereIn('student_id', [$alex->id, $omar->id])->whereNotNull('parent_id')->count())->toBe(0);
    expect(ScholarshipActivation::query()->where('student_id', $alex->id)->firstOrFail()->parent_id)->toBe($alex->id);
});
