<?php

use App\Models\CourseClass;
use App\Models\Schedule;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function seedAuthorizationFixtures(): void
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    (new PermissionSeeder)->run();
    (new RoleSeeder)->run();
}

function createApprovedUserWithRole(string $role): User
{
    $user = User::factory()->create([
        'approved_at' => now(),
    ]);
    $user->assignRole($role);

    return $user;
}

function scheduleClassOnDate(CourseClass $courseClass, string $date): Schedule
{
    $dayOfWeek = strtolower(Carbon::createFromFormat('Y-m-d', $date)->format('l'));

    return Schedule::factory()->create([
        'class_id' => $courseClass->id,
        'day_of_week' => $dayOfWeek,
    ]);
}
