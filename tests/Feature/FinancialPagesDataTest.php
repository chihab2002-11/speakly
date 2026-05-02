<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\ScholarshipActivation;
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
    $student = User::factory()->create([
        'approved_at' => now(),
        'date_of_birth' => now()->subYears(18)->toDateString(),
    ]);
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
    $response->assertSee('67%');
    $response->assertSee('5 000 DZD');
    $response->assertSee('Remaining: 5 000 DZD');
    $response->assertSee('PAY-9001');
    $response->assertSee(route('student.financial.payments.pdf', TuitionPayment::query()->where('reference', 'PAY-9001')->value('id')));
    $response->assertSee('Applied Discount');
    $response->assertSee('Scholarship Offers');
});

test('student financial page applies active scholarship discount', function () {
    /** @var TestCase $this */
    $parent = User::factory()->create(['approved_at' => now()]);
    $parent->assignRole('parent');

    $student = User::factory()->create([
        'approved_at' => now(),
        'date_of_birth' => now()->subYears(18)->toDateString(),
        'parent_id' => $parent->id,
    ]);
    $student->assignRole('student');

    $course = Course::factory()->create(['name' => 'German B1', 'price' => 20000]);
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 20000,
    ]);

    ScholarshipActivation::query()->create([
        'parent_id' => $parent->id,
        'student_id' => $student->id,
        'offer_key' => 'multi_course_4_plus',
        'discount_percent' => 10,
        'activated_at' => now(),
    ]);

    TuitionPayment::factory()->create([
        'student_id' => $student->id,
        'parent_id' => $parent->id,
        'amount' => 5000,
        'reference' => 'PAY-DISCOUNT-01',
    ]);

    $response = $this->actingAs($student)->get(route('student.financial'));

    $response->assertOk();
    $response->assertSee('German B1');
    $response->assertSee('Applied Discount');
    $response->assertSee('10%');
    $response->assertSee('-2 000 DZD');
    $response->assertSee('18 000 DZD');
    $response->assertSee('Remaining: 13 000 DZD');
});

test('student can activate an eligible scholarship offer for their own account', function () {
    /** @var TestCase $this */
    $student = User::factory()->create([
        'approved_at' => now(),
        'date_of_birth' => now()->subYears(18)->toDateString(),
    ]);
    $student->assignRole('student');

    $courses = collect(range(1, 4))->map(function (int $index) {
        return Course::factory()->create([
            'name' => 'Course '.$index,
            'price' => 5000 * $index,
        ]);
    });

    $classes = $courses->map(fn (Course $course) => CourseClass::factory()->create(['course_id' => $course->id]));

    $student->enrolledClasses()->syncWithoutDetaching(
        $classes->mapWithKeys(fn (CourseClass $class) => [
            $class->id => ['enrolled_at' => now()->subMonth()],
        ])->all()
    );

    $response = $this->actingAs($student)->post(route('student.financial.scholarships.activate'), [
        'offer_key' => 'multi_course_4_plus',
    ]);

    $response->assertRedirect(route('student.financial', ['offer' => 'multi_course_4_plus']));

    $this->assertDatabaseHas('scholarship_activations', [
        'parent_id' => $student->id,
        'student_id' => $student->id,
        'offer_key' => 'multi_course_4_plus',
        'discount_percent' => 10,
    ]);

    $page = $this->actingAs($student)->get(route('student.financial', ['offer' => 'multi_course_4_plus']));

    $page->assertOk();
    $page->assertSee('Multi Course Commitment');
    $page->assertSee('Active discount: 10%');
    $page->assertSee('Activated for your account');
    $page->assertSee('Active Discount');
    $page->assertDontSee('Offer Child');
});

test('student financial page hides parent only family offers and parent scoped discounts', function () {
    /** @var TestCase $this */
    $parent = User::factory()->create(['approved_at' => now()]);
    $parent->assignRole('parent');

    $student = User::factory()->create([
        'approved_at' => now(),
        'date_of_birth' => now()->subYears(18)->toDateString(),
        'parent_id' => $parent->id,
    ]);
    $student->assignRole('student');

    $course = Course::factory()->create(['name' => 'Arabic B1', 'price' => 12000]);
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 12000,
    ]);

    ScholarshipActivation::query()->create([
        'parent_id' => $parent->id,
        'student_id' => null,
        'offer_key' => 'family_3_children',
        'discount_percent' => 12,
        'activated_at' => now(),
    ]);

    $response = $this->actingAs($student)->get(route('student.financial'));

    $response->assertOk();
    $response->assertSee('Scholarship Offers');
    $response->assertSee('Academic Excellence Grant');
    $response->assertSee('Multi Course Commitment');
    $response->assertDontSee('Family Growth Offer');
    $response->assertDontSee('Offer Child');
    $response->assertSee('Active discount: 0%');
});

test('student financial page does not use another students scholarship activation', function () {
    /** @var TestCase $this */
    $parent = User::factory()->create(['approved_at' => now()]);
    $parent->assignRole('parent');

    $student = User::factory()->create([
        'approved_at' => now(),
        'date_of_birth' => now()->subYears(18)->toDateString(),
        'parent_id' => $parent->id,
    ]);
    $student->assignRole('student');

    $otherStudent = User::factory()->create([
        'approved_at' => now(),
        'date_of_birth' => now()->subYears(18)->toDateString(),
        'parent_id' => $parent->id,
    ]);
    $otherStudent->assignRole('student');

    $course = Course::factory()->create(['name' => 'Japanese A1', 'price' => 20000]);
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 20000,
    ]);
    StudentTuition::factory()->create([
        'student_id' => $otherStudent->id,
        'course_id' => $course->id,
        'course_price' => 20000,
    ]);

    ScholarshipActivation::query()->create([
        'parent_id' => $parent->id,
        'student_id' => $otherStudent->id,
        'offer_key' => 'multi_course_4_plus',
        'discount_percent' => 10,
        'activated_at' => now(),
    ]);

    $response = $this->actingAs($student)->get(route('student.financial'));

    $response->assertOk();
    $response->assertSee('Active discount: 0%');
    $response->assertSee('20 000 DZD');
    $response->assertDontSee('18 000 DZD');
    $response->assertSee('Not Eligible Yet');
});

test('underage students cannot access their financial page or receipts', function () {
    /** @var TestCase $this */
    $student = User::factory()->create([
        'approved_at' => now(),
        'date_of_birth' => now()->subYears(17)->toDateString(),
    ]);
    $student->assignRole('student');

    $payment = TuitionPayment::factory()->create([
        'student_id' => $student->id,
        'amount' => 10000,
        'reference' => 'PAY-UNDERAGE-01',
    ]);

    $this->actingAs($student)
        ->get(route('student.financial'))
        ->assertForbidden();

    $this->actingAs($student)
        ->get(route('student.financial.payments.pdf', $payment))
        ->assertForbidden();
});

test('guest cannot access the student financial page', function () {
    /** @var TestCase $this */
    $this->get(route('student.financial'))
        ->assertRedirect(route('login'));
});

test('unapproved students are redirected away from the student financial page', function () {
    /** @var TestCase $this */
    $student = User::factory()->create([
        'approved_at' => null,
        'date_of_birth' => now()->subYears(18)->toDateString(),
    ]);
    $student->assignRole('student');

    $this->actingAs($student)
        ->get(route('student.financial'))
        ->assertRedirect(route('pending-approval'));
});

test('parent users cannot access the student financial page', function () {
    /** @var TestCase $this */
    $parent = User::factory()->create(['approved_at' => now()]);
    $parent->assignRole('parent');

    $this->actingAs($parent)
        ->get(route('student.financial'))
        ->assertForbidden();
});

test('underage students do not see financial information in the student sidebar', function () {
    /** @var TestCase $this */
    $student = User::factory()->create([
        'approved_at' => now(),
        'date_of_birth' => now()->subYears(17)->toDateString(),
    ]);
    $student->assignRole('student');

    $response = $this->actingAs($student)->get(route('role.dashboard', ['role' => 'student']));

    $response->assertOk();
    $response->assertDontSee('Financial Information');
    $response->assertDontSee(route('student.financial'), false);
});

test('student can download own payment receipt pdf', function () {
    /** @var TestCase $this */
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $course = Course::factory()->create(['name' => 'English A1', 'price' => 15000]);
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 15000,
    ]);

    $payment = TuitionPayment::factory()->create([
        'student_id' => $student->id,
        'amount' => 10000,
        'method' => 'cash',
        'reference' => 'PAY-PDF-01',
    ]);

    $response = $this->actingAs($student)->get(route('student.financial.payments.pdf', $payment));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('content-disposition', 'inline; filename="payment-receipt-PAY-PDF-01.pdf"');

    expect($response->getContent())->toStartWith('%PDF-1.4');
    expect($response->getContent())->toContain('/MediaBox [0 0 283.46 425.2]');
    expect($response->getContent())->toContain('PAY-PDF-01');
    expect($response->getContent())->toContain('10 000 DZD');
});

test('student cannot download another students payment receipt pdf', function () {
    /** @var TestCase $this */
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $otherStudent = User::factory()->create(['approved_at' => now()]);
    $otherStudent->assignRole('student');

    $payment = TuitionPayment::factory()->create([
        'student_id' => $otherStudent->id,
        'amount' => 10000,
        'reference' => 'PAY-PRIVATE-01',
    ]);

    $this->actingAs($student)
        ->get(route('student.financial.payments.pdf', $payment))
        ->assertForbidden();
});

test('student financial page calculates payment progress and total remaining across enrolled courses', function () {
    /** @var TestCase $this */
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $english = Course::factory()->create(['name' => 'English A1', 'price' => 100]);
    $french = Course::factory()->create(['name' => 'French B2', 'price' => 100]);
    $englishGroup = CourseClass::factory()->create(['course_id' => $english->id]);
    $frenchGroup = CourseClass::factory()->create(['course_id' => $french->id]);

    $student->enrolledClasses()->syncWithoutDetaching([
        $englishGroup->id => ['enrolled_at' => now()],
        $frenchGroup->id => ['enrolled_at' => now()],
    ]);

    TuitionPayment::factory()->create([
        'student_id' => $student->id,
        'amount' => 90,
    ]);

    $response = $this->actingAs($student)->get(route('student.financial'));

    $response->assertOk();
    $response->assertSee('45%');
    $response->assertSee('110 DZD');
    $response->assertSee('English A1');
    $response->assertSee('French B2');
    $response->assertSee('Remaining: 10 DZD');
    $response->assertSee('Remaining: 100 DZD');
});

test('student financial page handles students with no courses and no payments', function () {
    /** @var TestCase $this */
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $response = $this->actingAs($student)->get(route('student.financial'));

    $response->assertOk();
    $response->assertSee('0%');
    $response->assertSee('0 DZD');
    $response->assertSee('No financial ledger entries yet.');
});

test('student financial page never shows negative remaining amounts when overpaid', function () {
    /** @var TestCase $this */
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $course = Course::factory()->create(['name' => 'Spanish C1', 'price' => 100]);
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 100,
    ]);

    TuitionPayment::factory()->create([
        'student_id' => $student->id,
        'amount' => 150,
    ]);

    $response = $this->actingAs($student)->get(route('student.financial'));

    $response->assertOk();
    $response->assertSee('150%');
    $response->assertSee('Remaining: 0 DZD');
    $response->assertDontSee('Remaining: -');
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

    $payment = TuitionPayment::factory()->create([
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
    $response->assertSee('Baridi Mob');
    $response->assertSee('13 000 DZD');

    $receiptResponse = $this->actingAs($parent)
        ->get(route('parent.financial.receipts.download', ['payment' => $payment]));

    $receiptResponse->assertOk();
    $receiptResponse->assertHeader('content-type', 'application/pdf');
    $receiptResponse->assertHeader('content-disposition', 'inline; filename="payment-receipt-PAY-PARENT-01.pdf"');
    expect($receiptResponse->getContent())->toStartWith('%PDF-1.4');
    expect($receiptResponse->getContent())->toContain('PAY-PARENT-01');
    expect($receiptResponse->getContent())->toContain('Applied discount: None');
});

test('parent can still activate a scholarship offer for an eligible child', function () {
    /** @var TestCase $this */
    $parent = User::factory()->create(['approved_at' => now()]);
    $parent->assignRole('parent');

    $student = User::factory()->create([
        'approved_at' => now(),
        'date_of_birth' => now()->subYears(16)->toDateString(),
        'parent_id' => $parent->id,
        'name' => 'Eligible Child',
    ]);
    $student->assignRole('student');

    $courses = collect(range(1, 4))->map(function (int $index) {
        return Course::factory()->create([
            'name' => 'Parent Course '.$index,
            'price' => 4000 * $index,
        ]);
    });

    $classes = $courses->map(fn (Course $course) => CourseClass::factory()->create(['course_id' => $course->id]));

    $student->enrolledClasses()->syncWithoutDetaching(
        $classes->mapWithKeys(fn (CourseClass $class) => [
            $class->id => ['enrolled_at' => now()->subMonth()],
        ])->all()
    );

    $response = $this->actingAs($parent)->post(route('parent.financial.scholarships.activate'), [
        'offer_key' => 'multi_course_4_plus',
        'selected_child_id' => $student->id,
    ]);

    $response->assertRedirect(route('parent.financial', ['offer' => 'multi_course_4_plus']));

    $this->assertDatabaseHas('scholarship_activations', [
        'parent_id' => $parent->id,
        'student_id' => $student->id,
        'offer_key' => 'multi_course_4_plus',
        'discount_percent' => 10,
    ]);
});
