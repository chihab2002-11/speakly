<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Database\Seeders\CleanTimetableFinancialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('clean timetable financial seeder resets domain tables and reseeds clean data', function () {
    /** @var TestCase $this */
    $this->seed(CleanTimetableFinancialSeeder::class);

    expect(Course::query()->whereIn('code', ['ENG-B1-VIS', 'FRE-A2-VIS', 'IELTS-VIS'])->count())->toBe(3);
    expect(CourseClass::query()->count())->toBeGreaterThanOrEqual(9);
    expect(Schedule::query()->count())->toBeGreaterThanOrEqual(8);
    expect(Room::query()->count())->toBeGreaterThanOrEqual(4);

    if (Schema::hasTable('tuition_payments')) {
        expect(DB::table('tuition_payments')->count())->toBeGreaterThanOrEqual(4);
    }

    expect(User::query()->where('email', 'demo.admin@speakly.com')->exists())->toBeTrue();
});
