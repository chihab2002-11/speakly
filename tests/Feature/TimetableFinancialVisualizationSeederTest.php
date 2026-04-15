<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Schedule;
use App\Models\TuitionPayment;
use App\Models\User;
use Database\Seeders\TimetableFinancialVisualizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('timetable financial visualization seeder provides rich demo data', function () {
    /** @var TestCase $this */
    $this->seed(TimetableFinancialVisualizationSeeder::class);

    expect(User::query()->where('email', 'demo.admin@speakly.com')->exists())->toBeTrue();
    expect(User::query()->where('email', 'demo.secretary@speakly.com')->exists())->toBeTrue();

    expect(Course::query()->whereIn('code', ['ENG-B1-VIS', 'FRE-A2-VIS', 'IELTS-VIS'])->count())->toBe(3);
    expect(CourseClass::query()->count())->toBeGreaterThanOrEqual(9);
    expect(Schedule::query()->count())->toBeGreaterThanOrEqual(8);

    $studentAmine = User::query()->where('email', 'demo.student.amine@speakly.com')->first();
    expect($studentAmine)->not->toBeNull();
    expect($studentAmine?->enrolledClasses()->count())->toBeGreaterThanOrEqual(3);

    if (Schema::hasTable('tuition_payments')) {
        expect(TuitionPayment::query()->where('reference', 'PAY-VIS-0001')->exists())->toBeTrue();
        expect(TuitionPayment::query()->where('reference', 'PAY-VIS-0004')->exists())->toBeTrue();
    }
});
