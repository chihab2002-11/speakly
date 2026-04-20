<?php

use App\Models\Course;
use App\Models\StudentTuition;
use App\Models\TuitionPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['student', 'parent', 'secretary'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

test('student financial page uses stored tuition course price and payments', function () {
    /** @var TestCase $this */
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $course = Course::factory()->create(['name' => 'English A1', 'price' => 15000]);
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 15000,
    ]);

    TuitionPayment::factory()->create([
        'student_id' => $student->id,
        'amount' => 10000,
        'method' => 'cash',
        'reference' => 'PAY-9001',
    ]);

    $response = $this->actingAs($student)->get(route('student.financial'));

    $response->assertOk();
    $response->assertSee('English A1');
    $response->assertSee('10,000.00');
    $response->assertSee('PAY-9001');
});

test('parent financial page shows children invoices and payment history from database', function () {
    /** @var TestCase $this */
    $parent = User::factory()->create(['approved_at' => now()]);
    $parent->assignRole('parent');

    $student = User::factory()->create([
        'approved_at' => now(),
        'parent_id' => $parent->id,
        'name' => 'Child One',
    ]);
    $student->assignRole('student');

    $course = Course::factory()->create(['name' => 'Spanish A2', 'price' => 20000]);
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 20000,
    ]);

    TuitionPayment::factory()->create([
        'student_id' => $student->id,
        'parent_id' => $parent->id,
        'amount' => 7000,
        'method' => 'bank_transfer',
        'reference' => 'PAY-PARENT-01',
    ]);

    $response = $this->actingAs($parent)->get(route('parent.financial'));

    $response->assertOk();
    $response->assertSee('Child One');
    $response->assertSee('PAY-PARENT-01');
    $response->assertSee('13 000 DZD');
});
