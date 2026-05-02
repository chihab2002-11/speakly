<?php

use App\Models\EmployeePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedAuthorizationFixtures();
});

it('allows a teacher to view their own payment information', function () {
    /** @var TestCase $this */
    $teacher = createApprovedUserWithRole('teacher');
    $teacher->update(['name' => 'Teacher One', 'email' => 'teacher1@example.com']);

    EmployeePayment::factory()->create([
        'employee_id' => $teacher->id,
        'expected_salary' => 100000,
        'amount_paid' => 60000,
        'notes' => 'Partial payment for April',
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.my-payments'));

    $response->assertOk();
    $response->assertSee('My Payments');
    $response->assertSee('Teacher One');
    $response->assertSee('teacher1@example.com');
    $response->assertSee('100 000 DA');
    $response->assertSee('60 000 DA');
    $response->assertSee('40 000 DA');
    $response->assertSee('Partial');
    $response->assertSee('Partial payment for April');
});

it('shows a helpful empty state when no employee payment record exists yet', function () {
    /** @var TestCase $this */
    $teacher = createApprovedUserWithRole('teacher');

    $response = $this->actingAs($teacher)->get(route('teacher.my-payments'));

    $response->assertOk();
    $response->assertSee('No payment information has been recorded yet.');
});

it('allows a secretary to view their own payment information', function () {
    /** @var TestCase $this */
    $secretary = createApprovedUserWithRole('secretary');
    $secretary->update(['name' => 'Secretary One', 'email' => 'secretary1@example.com']);

    EmployeePayment::factory()->create([
        'employee_id' => $secretary->id,
        'expected_salary' => 80000,
        'amount_paid' => 0,
        'notes' => 'Salary not recorded yet',
    ]);

    $response = $this->actingAs($secretary)->get(route('secretary.my-payments'));

    $response->assertOk();
    $response->assertSee('My Payments');
    $response->assertSee('Secretary One');
    $response->assertSee('secretary1@example.com');
    $response->assertSee('80 000 DA');
    $response->assertSee('0 DA');
    $response->assertSee('80 000 DA');
    $response->assertSee('Unpaid');
});

it('allows a teacher to download their own payment receipt PDF', function () {
    /** @var TestCase $this */
    $teacher = createApprovedUserWithRole('teacher');
    $teacher->update(['email' => 'teacherpdf@example.com']);

    EmployeePayment::factory()->create([
        'employee_id' => $teacher->id,
        'expected_salary' => 50000,
        'amount_paid' => 50000,
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.my-payments.receipt-pdf'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition');
    $this->assertStringContainsString('PDF', $response->getContent());
});

it('allows a secretary to download their own payment receipt PDF', function () {
    /** @var TestCase $this */
    $secretary = createApprovedUserWithRole('secretary');
    $secretary->update(['email' => 'secretarypdf@example.com']);

    EmployeePayment::factory()->create([
        'employee_id' => $secretary->id,
        'expected_salary' => 65000,
        'amount_paid' => 25000,
    ]);

    $response = $this->actingAs($secretary)->get(route('secretary.my-payments.receipt-pdf'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition');
    $this->assertStringContainsString('PDF', $response->getContent());
});

it('blocks student and parent users from employee payment pages', function () {
    /** @var TestCase $this */
    $student = createApprovedUserWithRole('student');
    $parent = createApprovedUserWithRole('parent');

    $this->actingAs($student)->get(route('teacher.my-payments'))->assertForbidden();
    $this->actingAs($student)->get(route('secretary.my-payments'))->assertForbidden();

    $this->actingAs($parent)->get(route('teacher.my-payments'))->assertForbidden();
    $this->actingAs($parent)->get(route('secretary.my-payments'))->assertForbidden();
});

it('redirects unapproved employees away from employee payment pages', function () {
    /** @var TestCase $this */
    $unapprovedTeacher = User::factory()->create(['approved_at' => null]);
    $unapprovedTeacher->assignRole('teacher');

    $unapprovedSecretary = User::factory()->create(['approved_at' => null]);
    $unapprovedSecretary->assignRole('secretary');

    $this->actingAs($unapprovedTeacher)->get(route('teacher.my-payments'))->assertRedirect(route('pending-approval'));
    $this->actingAs($unapprovedSecretary)->get(route('secretary.my-payments'))->assertRedirect(route('pending-approval'));
});

it('does not expose another employee payment information via self service routes', function () {
    /** @var TestCase $this */
    $teacherA = createApprovedUserWithRole('teacher');
    $teacherA->update(['name' => 'Teacher A', 'email' => 'teachera@example.com']);

    $teacherB = createApprovedUserWithRole('teacher');
    $teacherB->update(['name' => 'Teacher B', 'email' => 'teacherb@example.com']);

    EmployeePayment::factory()->create([
        'employee_id' => $teacherB->id,
        'expected_salary' => 90000,
        'amount_paid' => 45000,
    ]);

    $response = $this->actingAs($teacherA)->get(route('teacher.my-payments'));

    $response->assertOk();
    $response->assertSee('Teacher A');
    $response->assertSee('teachera@example.com');
    $response->assertDontSee('Teacher B');
    $response->assertDontSee('teacherb@example.com');
});

it('keeps admin employee payment routes working after employee self service is added', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');

    $this->actingAs($admin)->get(route('admin.employee-payments.index'))->assertOk();
});
