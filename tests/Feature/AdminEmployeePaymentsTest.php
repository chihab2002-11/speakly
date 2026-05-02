<?php

use App\Models\EmployeePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedAuthorizationFixtures();
});

it('renders employee payment aggregates and statuses for teachers and secretaries', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');

    $paidTeacher = createApprovedUserWithRole('teacher');
    $paidTeacher->update(['name' => 'Paid Teacher']);
    EmployeePayment::factory()->create([
        'employee_id' => $paidTeacher->id,
        'expected_salary' => 100000,
        'amount_paid' => 100000,
    ]);

    $partialSecretary = createApprovedUserWithRole('secretary');
    $partialSecretary->update(['name' => 'Partial Secretary']);
    EmployeePayment::factory()->create([
        'employee_id' => $partialSecretary->id,
        'expected_salary' => 80000,
        'amount_paid' => 20000,
    ]);

    $unpaidTeacher = createApprovedUserWithRole('teacher');
    $unpaidTeacher->update(['name' => 'Unpaid Teacher']);
    EmployeePayment::factory()->create([
        'employee_id' => $unpaidTeacher->id,
        'expected_salary' => 50000,
        'amount_paid' => 0,
    ]);

    $pendingSecretary = createApprovedUserWithRole('secretary');
    $pendingSecretary->update(['name' => 'Pending Secretary']);

    $response = $this->actingAs($admin)->get(route('admin.employee-payments.index'));

    $response->assertOk();
    $response->assertViewIs('admin.employee-payments');
    $response->assertSee('Employee payments');
    $response->assertSee('230,000 DA');
    $response->assertSee('120,000 DA');
    $response->assertSee('110,000 DA');
    $response->assertSee('1 paid / 1 partial');
    $response->assertSee('1 unpaid / 1 pending');
    $response->assertSee('Paid Teacher');
    $response->assertSee('Partial Secretary');
    $response->assertSee('Unpaid Teacher');
    $response->assertSee('Pending Secretary');
    $response->assertSee('paid');
    $response->assertSee('partial');
    $response->assertSee('unpaid');
    $response->assertSee('pending');
});

it('keeps remaining at zero when an employee is overpaid', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');
    $teacher = createApprovedUserWithRole('teacher');

    EmployeePayment::factory()->create([
        'employee_id' => $teacher->id,
        'expected_salary' => 50000,
        'amount_paid' => 75000,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.employee-payments.index'));

    $response->assertOk();
    $response->assertViewHas('rows', function ($rows) use ($teacher): bool {
        $row = $rows->firstWhere('employee.id', $teacher->id);

        return $row !== null
            && $row['remaining'] === 0
            && $row['status'] === 'paid';
    });
});

it('shows only approved teachers and secretaries and excludes unapproved staff from aggregates', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');

    $approvedTeacher = createApprovedUserWithRole('teacher');
    $approvedTeacher->update(['name' => 'Approved Payroll Teacher']);
    EmployeePayment::factory()->create([
        'employee_id' => $approvedTeacher->id,
        'expected_salary' => 60000,
        'amount_paid' => 15000,
    ]);

    $unapprovedTeacher = User::factory()->create([
        'name' => 'Unapproved Payroll Teacher',
        'approved_at' => null,
    ]);
    $unapprovedTeacher->assignRole('teacher');
    EmployeePayment::factory()->create([
        'employee_id' => $unapprovedTeacher->id,
        'expected_salary' => 90000,
        'amount_paid' => 90000,
    ]);

    $unapprovedSecretary = User::factory()->create([
        'name' => 'Unapproved Payroll Secretary',
        'approved_at' => null,
    ]);
    $unapprovedSecretary->assignRole('secretary');
    EmployeePayment::factory()->create([
        'employee_id' => $unapprovedSecretary->id,
        'expected_salary' => 70000,
        'amount_paid' => 35000,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.employee-payments.index'));

    $response->assertOk();
    $response->assertSee('Approved Payroll Teacher');
    $response->assertDontSee('Unapproved Payroll Teacher');
    $response->assertDontSee('Unapproved Payroll Secretary');
    $response->assertViewHas('totals', [
        'total_employees' => 1,
        'total_salaries' => 60000,
        'total_paid' => 15000,
        'total_remaining' => 45000,
        'count_paid' => 0,
        'count_unpaid' => 0,
        'count_partial' => 1,
        'count_pending' => 0,
    ]);
});

it('updates employee salary payment details from the admin page', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');
    $secretary = createApprovedUserWithRole('secretary');

    $this->actingAs($admin)
        ->patch(route('admin.employee-payments.update', $secretary), [
            'expected_salary' => 70000,
            'amount_paid' => 25000,
            'notes' => 'April payroll',
        ])
        ->assertRedirect(route('admin.employee-payments.index'));

    $this->assertDatabaseHas('employee_payments', [
        'employee_id' => $secretary->id,
        'recorded_by' => $admin->id,
        'expected_salary' => 70000,
        'amount_paid' => 25000,
        'notes' => 'April payroll',
    ]);
});

it('allows admins and blocks non admin employees from employee payments', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');
    $secretary = createApprovedUserWithRole('secretary');
    $teacher = createApprovedUserWithRole('teacher');

    $this->actingAs($admin)
        ->get(route('admin.employee-payments.index'))
        ->assertOk();

    $this->actingAs($secretary)
        ->get(route('admin.employee-payments.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('admin.employee-payments.index'))
        ->assertForbidden();
});

it('displays employee payment details page for admin with full information', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');
    $teacher = createApprovedUserWithRole('teacher');
    $teacher->update(['name' => 'John Doe', 'email' => 'john@example.com']);

    EmployeePayment::factory()->create([
        'employee_id' => $teacher->id,
        'expected_salary' => 100000,
        'amount_paid' => 60000,
        'notes' => 'Partial payment for March',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.employee-payment.show', $teacher));

    $response->assertOk();
    $response->assertViewIs('admin.employee-payment-details');
    $response->assertViewHas('employee', function ($employee) use ($teacher): bool {
        return $employee->id === $teacher->id && $employee->name === 'John Doe';
    });
    $response->assertViewHas('paymentData', function ($data): bool {
        return $data['expected_salary'] === 100000
            && $data['amount_paid'] === 60000
            && $data['remaining'] === 40000
            && $data['status'] === 'partial'
            && $data['notes'] === 'Partial payment for March';
    });
});

it('displays employee payment details for secretary with pending status', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');
    $secretary = createApprovedUserWithRole('secretary');
    $secretary->update(['name' => 'Jane Smith']);

    // Secretary without any payment record yet
    $response = $this->actingAs($admin)->get(route('admin.employee-payment.show', $secretary));

    $response->assertOk();
    $response->assertViewIs('admin.employee-payment-details');
    $response->assertViewHas('paymentData', function ($data): bool {
        return $data['expected_salary'] === 0
            && $data['amount_paid'] === 0
            && $data['remaining'] === 0
            && $data['status'] === 'pending';
    });
});

it('generates PDF receipt for employee payment', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');
    $teacher = createApprovedUserWithRole('teacher');
    $teacher->update(['name' => 'Alice Johnson', 'email' => 'alice@example.com']);

    EmployeePayment::factory()->create([
        'employee_id' => $teacher->id,
        'expected_salary' => 75000,
        'amount_paid' => 75000,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.employee-payment.receipt-pdf', $teacher));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition');
    $this->assertStringContainsString('PDF', $response->getContent());
});

it('blocks non-admin users from viewing employee payment details', function () {
    /** @var TestCase $this */
    $teacher1 = createApprovedUserWithRole('teacher');
    $teacher2 = createApprovedUserWithRole('teacher');
    $secretary = createApprovedUserWithRole('secretary');

    EmployeePayment::factory()->create([
        'employee_id' => $teacher1->id,
        'expected_salary' => 50000,
        'amount_paid' => 30000,
    ]);

    $this->actingAs($teacher2)->get(route('admin.employee-payment.show', $teacher1))->assertForbidden();
    $this->actingAs($secretary)->get(route('admin.employee-payment.show', $teacher1))->assertForbidden();
});

it('blocks non-admin users from downloading employee payment PDF', function () {
    /** @var TestCase $this */
    $teacher = createApprovedUserWithRole('teacher');
    $secretary = createApprovedUserWithRole('secretary');

    EmployeePayment::factory()->create([
        'employee_id' => $teacher->id,
        'expected_salary' => 50000,
        'amount_paid' => 50000,
    ]);

    $admin = createApprovedUserWithRole('admin');

    $this->actingAs($secretary)->get(route('admin.employee-payment.receipt-pdf', $teacher))->assertForbidden();
    $this->actingAs($teacher)->get(route('admin.employee-payment.receipt-pdf', $teacher))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.employee-payment.receipt-pdf', $teacher))->assertOk();
});

it('only shows details and PDF for approved employees', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');
    $unapprovedTeacher = User::factory()->create(['approved_at' => null]);
    $unapprovedTeacher->assignRole('teacher');

    $this->actingAs($admin)->get(route('admin.employee-payment.show', $unapprovedTeacher))->assertNotFound();
    $this->actingAs($admin)->get(route('admin.employee-payment.receipt-pdf', $unapprovedTeacher))->assertNotFound();
});

it('returns 404 for non-employee users trying to access payment details', function () {
    /** @var TestCase $this */
    $admin = createApprovedUserWithRole('admin');
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $this->actingAs($admin)->get(route('admin.employee-payment.show', $student))->assertNotFound();
    $this->actingAs($admin)->get(route('admin.employee-payment.receipt-pdf', $student))->assertNotFound();
});
