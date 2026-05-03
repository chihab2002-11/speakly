<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\LanguageProgram;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScholarshipActivation;
use App\Models\StudentTuition;
use App\Models\TuitionPayment;
use App\Models\User;
use App\Notifications\SecretaryAnnouncementNotification;
use App\Notifications\StudentGroupEnrollmentChangedNotification;
use App\Notifications\TeacherGroupAssignedNotification;
use App\Notifications\TuitionPaymentRecordedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedAuthorizationFixtures();
});

function createApprovedSecretaryForOperations(): User
{
    $secretary = User::factory()->create([
        'approved_at' => now(),
    ]);
    $secretary->assignRole('secretary');

    return $secretary;
}

function createApprovedAdminForOperations(): User
{
    $admin = User::factory()->create([
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    return $admin;
}

function createLanguageProgramForSecretaryOperations(array $attributes = []): LanguageProgram
{
    static $sequence = 1;

    $programNumber = $sequence++;

    return LanguageProgram::query()->create(array_merge([
        'code' => 'SEC'.$programNumber,
        'locale_code' => 'sec-'.$programNumber,
        'name' => 'Secretary Program '.$programNumber,
        'title' => 'Secretary Program '.$programNumber,
        'description' => 'Secretary operations language program '.$programNumber,
        'full_description' => 'Secretary operations language program '.$programNumber.' full description.',
        'flag_url' => 'https://example.com/flags/secretary-program-'.$programNumber.'.svg',
        'sort_order' => $programNumber,
        'is_active' => true,
    ], $attributes));
}

it('renders secretary registrations using secretary view', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $program = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);

    Course::factory()->create([
        'name' => 'English A1',
        'program_id' => $program->id,
    ]);

    $response = $this->actingAs($secretary)
        ->get(route('secretary.registrations'));

    $response->assertOk();
    $response->assertViewIs('secretary.registrations');
    $response->assertSee('Create Account');
    $response->assertSee('Program Selection');
    $response->assertSee('English Program');
});

it('creates pending account from secretary registrations form', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    Storage::fake('public');

    $response = $this->actingAs($secretary)
        ->post(route('secretary.registrations.store'), [
            'name' => 'Pending Teacher',
            'email' => 'pending.teacher@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'requested_role' => 'teacher',
            'date_of_birth' => '1996-05-10',
            'registration_document' => UploadedFile::fake()->create('pending-teacher-cv.pdf', 240, 'application/pdf'),
        ]);

    $response->assertRedirect(route('secretary.registrations'));

    $created = User::query()->where('email', 'pending.teacher@example.com')->first();

    expect($created)->not->toBeNull();
    expect($created?->requested_role)->toBe('teacher');
    expect($created?->approved_at)->toBeNull();
    expect($created?->rejected_at)->toBeNull();
    expect($created?->registration_document_type)->toBe('cv');
    Storage::disk('public')->assertExists($created->registration_document_path);
});

it('secretary-created registration appears in approvals queue', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    Storage::fake('public');
    $program = createLanguageProgramForSecretaryOperations([
        'name' => 'German Program',
    ]);
    $course = Course::factory()->create([
        'name' => 'German A1',
        'price' => 18000,
        'program_id' => $program->id,
    ]);

    $this->actingAs($secretary)
        ->post(route('secretary.registrations.store'), [
            'name' => 'Queue Student',
            'email' => 'queue.student@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'requested_role' => 'student',
            'date_of_birth' => '2001-03-14',
            'program_id' => $program->id,
            'course_id' => $course->id,
            'registration_document' => UploadedFile::fake()->create('student-birth-certificate.pdf', 300, 'application/pdf'),
        ])
        ->assertRedirect(route('secretary.registrations'));

    $this->actingAs($secretary)
        ->get(route('approvals.index', ['role' => 'secretary']))
        ->assertOk()
        ->assertViewIs('approvals.index')
        ->assertSee('queue.student@example.com')
        ->assertSee('German A1')
        ->assertSee('student-birth-certificate.pdf')
        ->assertSee('Open')
        ->assertSee('Download');
});

it('reviewer can download pending registration document from approvals', function () {
    /** @var TestCase $this */
    Storage::fake('public');

    $secretary = createApprovedSecretaryForOperations();
    $program = createLanguageProgramForSecretaryOperations();
    $course = Course::factory()->create([
        'program_id' => $program->id,
        'price' => 16000,
    ]);

    $this->actingAs($secretary)
        ->post(route('secretary.registrations.store'), [
            'name' => 'Pending Student With Document',
            'email' => 'pending.student.document@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'requested_role' => 'student',
            'date_of_birth' => '2004-03-14',
            'program_id' => $program->id,
            'course_id' => $course->id,
            'registration_document' => UploadedFile::fake()->create('pending-student-document.pdf', 220, 'application/pdf'),
        ])
        ->assertRedirect(route('secretary.registrations'));

    $pendingUser = User::query()->where('email', 'pending.student.document@example.com')->firstOrFail();

    $response = $this->actingAs($secretary)
        ->get(route('approvals.document', ['role' => 'secretary', 'user' => $pendingUser]));

    $response->assertOk();
    $response->assertHeader('content-disposition', 'attachment; filename=pending-student-document.pdf');
});

it('secretary registration rejects a course outside the selected program', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $englishProgram = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $frenchProgram = createLanguageProgramForSecretaryOperations([
        'code' => 'SECFR',
        'locale_code' => 'sec-fr',
        'name' => 'French Program',
        'title' => 'French Program',
    ]);
    $course = Course::factory()->create([
        'name' => 'French A1',
        'program_id' => $frenchProgram->id,
    ]);

    $this->actingAs($secretary)
        ->from(route('secretary.registrations'))
        ->post(route('secretary.registrations.store'), [
            'name' => 'Mismatch Student',
            'email' => 'mismatch.student@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'requested_role' => 'student',
            'date_of_birth' => '2001-03-14',
            'program_id' => $englishProgram->id,
            'course_id' => $course->id,
            'registration_document' => UploadedFile::fake()->create('mismatch-student-birth-certificate.pdf', 300, 'application/pdf'),
        ])
        ->assertRedirect(route('secretary.registrations'))
        ->assertSessionHasErrors([
            'course_id' => 'Selected course does not belong to the selected program.',
        ]);
});

it('renders secretary payments page', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $course = Course::factory()->create(['name' => 'English B1', 'price' => 22000]);

    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 22000,
    ]);

    $response = $this->actingAs($secretary)->get(route('secretary.payments'));

    $response->assertOk();
    $response->assertViewIs('secretary.payments');
    $response->assertSee('Student Payments');
    $response->assertSee('English B1');
    $response->assertSee('Applied Discount');
    $response->assertSee('data-refresh-after-payment', false);
    $response->assertSee('window.location.reload()', false);
    $response->assertSee('22,000 DA');
    $response->assertSee('Cash');
    $response->assertSee('Baridi Mob');
    $response->assertSee('Card');
    $response->assertDontSee('Bank Transfer');
    $response->assertDontSee('Online');
});

it('applies scholarship discount on secretary payments page', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $parent = User::factory()->create(['approved_at' => now()]);
    $parent->assignRole('parent');

    $course = Course::factory()->create(['name' => 'Italian A2', 'price' => 16000]);

    $student = User::factory()->create([
        'approved_at' => now(),
        'parent_id' => $parent->id,
    ]);
    $student->assignRole('student');
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 16000,
    ]);

    ScholarshipActivation::query()->create([
        'parent_id' => $parent->id,
        'student_id' => $student->id,
        'offer_key' => 'academic_progress_2m',
        'discount_percent' => 10,
        'activated_at' => now(),
    ]);

    TuitionPayment::factory()->create([
        'student_id' => $student->id,
        'parent_id' => $parent->id,
        'amount' => 12000,
    ]);

    $response = $this->actingAs($secretary)->get(route('secretary.payments'));

    $response->assertOk();
    $response->assertSee('Italian A2');
    $response->assertSee('10%');
    $response->assertSee('-1,600 DA');
    $response->assertSee('14,400 DA');
    $response->assertSee('2,400 DA');
});

it('records a student payment from secretary payments page', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $course = Course::factory()->create(['price' => 16000]);

    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 16000,
    ]);

    $response = $this->actingAs($secretary)
        ->post(route('secretary.payments.store'), [
            'student_id' => $student->id,
            'amount' => 12000,
            'method' => 'bank_transfer',
            'reference' => 'PAY-TEST-001',
        ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('content-disposition', 'inline; filename="payment-receipt-PAY-TEST-001.pdf"');
    expect($response->getContent())->toStartWith('%PDF-1.4');
    expect($response->getContent())->toContain('/MediaBox [0 0 283.46 425.2]');
    expect($response->getContent())->toContain('PAY-TEST-001');
    expect($response->getContent())->toContain('Method: Baridi Mob');
    expect($response->getContent())->toContain('12 000 DZD');

    $this->assertDatabaseHas('tuition_payments', [
        'student_id' => $student->id,
        'recorded_by' => $secretary->id,
        'amount' => 12000,
        'method' => 'bank_transfer',
        'reference' => 'PAY-TEST-001',
    ]);

    expect(TuitionPayment::query()->count())->toBe(1);
});

it('records tuition payment and notifies the student and linked parent only', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $parent = createApprovedUserWithRole('parent');
    $unrelatedParent = createApprovedUserWithRole('parent');
    $course = Course::factory()->create(['price' => 16000]);

    $student = User::factory()->create([
        'approved_at' => now(),
        'parent_id' => $parent->id,
        'name' => 'Payment Student',
    ]);
    $student->assignRole('student');
    StudentTuition::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 16000,
    ]);

    $this->actingAs($secretary)
        ->post(route('secretary.payments.store'), [
            'student_id' => $student->id,
            'amount' => 8000,
            'method' => 'cash',
            'reference' => 'PAY-NOTIFY-001',
        ])
        ->assertOk();

    $payment = TuitionPayment::query()->where('reference', 'PAY-NOTIFY-001')->firstOrFail();
    $studentNotification = $student->fresh()->notifications()->latest()->first();
    $parentNotification = $parent->fresh()->notifications()->latest()->first();

    expect($studentNotification)->not()->toBeNull();
    expect($studentNotification->type)->toBe(TuitionPaymentRecordedNotification::class);
    expect($studentNotification->data['type'])->toBe('tuition_payment_recorded');
    expect($studentNotification->data['amount'])->toBe(8000);
    expect($studentNotification->data['payment_id'])->toBe($payment->id);
    expect($studentNotification->data['actor_id'])->toBe($secretary->id);
    expect($studentNotification->data['actor_role'])->toBe('secretary');
    expect($studentNotification->data['related_model'])->toBe(TuitionPayment::class);
    expect($studentNotification->data['url'])->toBe(route('student.financial'));

    expect($parentNotification)->not()->toBeNull();
    expect($parentNotification->type)->toBe(TuitionPaymentRecordedNotification::class);
    expect($parentNotification->data['recipient_type'])->toBe('parent');
    expect($parentNotification->data['child_id'])->toBe($student->id);
    expect($parentNotification->data['child_name'])->toBe('Payment Student');
    expect($parentNotification->data['url'])->toBe(route('parent.financial'));
    expect($unrelatedParent->fresh()->notifications()->count())->toBe(0);
});

it('does not accept online as a new secretary payment method', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');

    $this->actingAs($secretary)
        ->from(route('secretary.payments'))
        ->post(route('secretary.payments.store'), [
            'student_id' => $student->id,
            'amount' => 12000,
            'method' => 'online',
        ])
        ->assertRedirect(route('secretary.payments'))
        ->assertSessionHasErrors('method');

    expect(TuitionPayment::query()->count())->toBe(0);
});

it('approval creates locked tuition record and payment entry for selected course', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $course = Course::factory()->create([
        'name' => 'Spanish B2',
        'price' => 19500,
    ]);

    $student = User::factory()->create([
        'name' => 'Approved Student',
        'email' => 'approved.student@example.com',
        'approved_at' => null,
        'rejected_at' => null,
        'requested_role' => 'student',
        'requested_course_id' => $course->id,
    ]);

    $this->actingAs($secretary)
        ->post(route('approvals.approve', ['role' => 'secretary', 'user' => $student]))
        ->assertRedirect(route('approvals.index', ['role' => 'secretary']));

    $this->assertDatabaseHas('student_tuitions', [
        'student_id' => $student->id,
        'course_id' => $course->id,
        'course_price' => 19500,
    ]);

    $course->update(['price' => 25000]);

    $this->actingAs($secretary)
        ->get(route('secretary.payments'))
        ->assertOk()
        ->assertSee('Approved Student')
        ->assertSee('Spanish B2')
        ->assertSee('19,500 DA')
        ->assertDontSee('25,000 DA');
});

it('approval rejects student registration when selected course has no valid price', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $course = Course::factory()->create([
        'price' => 0,
    ]);

    $student = User::factory()->create([
        'approved_at' => null,
        'rejected_at' => null,
        'requested_role' => 'student',
        'requested_course_id' => $course->id,
    ]);

    $this->actingAs($secretary)
        ->post(route('approvals.approve', ['role' => 'secretary', 'user' => $student]))
        ->assertSessionHas('error', 'Selected course must have a valid price before approval.');

    $this->assertDatabaseMissing('student_tuitions', [
        'student_id' => $student->id,
    ]);
});

it('renders secretary groups page with group data', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $course = Course::factory()->create();
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $room = Room::factory()->create();

    Schedule::factory()->create([
        'class_id' => $group->id,
        'room_id' => $room->id,
        'day_of_week' => 'monday',
    ]);

    $response = $this->actingAs($secretary)->get(route('secretary.groups'));

    $response->assertOk();
    $response->assertViewIs('secretary.groups');
    $response->assertSee('Manage Groups');
    $response->assertSee('Create Group');
    $response->assertSee('Students in Group');
    $response->assertSee('Students List');
    $response->assertSee('secretaryGroupStudentsData', false);
    $response->assertSee('No students enrolled in this group yet.');
    $response->assertDontSee('Remove Student from Group');
    $response->assertDontSee('Enroll Student');
});

it('renders enrolled student details for the group students modal', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $parent = createApprovedUserWithRole('parent');
    $course = Course::factory()->create([
        'name' => 'Presentation English',
        'price' => 10000,
    ]);
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'capacity' => 12,
    ]);
    $student = User::factory()->create([
        'name' => 'Modal Student',
        'email' => 'modal.student@example.com',
        'phone' => '0555 123 456',
        'parent_id' => $parent->id,
        'approved_at' => now(),
    ]);
    $student->assignRole('student');
    $group->students()->attach($student->id, ['enrolled_at' => '2026-01-15 09:00:00']);
    TuitionPayment::factory()->create([
        'student_id' => $student->id,
        'parent_id' => $parent->id,
        'amount' => 4000,
    ]);

    $response = $this->actingAs($secretary)->get(route('secretary.groups'));

    $response->assertOk();
    $response->assertSee('Modal Student');
    $response->assertSee('modal.student@example.com');
    $response->assertSee($parent->name);
    $response->assertSee('0555 123 456');
    $response->assertSee('Jan 15, 2026');
    $response->assertSee('Partial');
});

it('displays group capacity as enrolled over capacity', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $course = Course::factory()->create();
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'capacity' => 6,
    ]);

    $response = $this->actingAs($secretary)->get(route('secretary.groups'));

    $response->assertOk();
    $response->assertSee('Enrolled: 0 / 6');
    $response->assertDontSee('6 / 0');
    $response->assertSee("data-capacity=\"{$group->capacity}\"", false);
    $response->assertSee('secretaryGroupClassesData', false);
});

it('keeps create group program selection independent from enroll student selection', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $englishProgram = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $frenchProgram = createLanguageProgramForSecretaryOperations([
        'code' => 'SECFR',
        'locale_code' => 'sec-fr',
        'name' => 'French Program',
        'title' => 'French Program',
    ]);

    $englishCourse = Course::factory()->create([
        'name' => 'English A2',
        'code' => 'ENG-A2',
        'program_id' => $englishProgram->id,
    ]);
    $frenchCourse = Course::factory()->create([
        'name' => 'French B1',
        'code' => 'FR-B1',
        'program_id' => $frenchProgram->id,
    ]);

    $aliceTeacher = User::factory()->create([
        'name' => 'Alice Teacher',
        'email' => 'alice.teacher@example.com',
        'approved_at' => now(),
    ]);
    $aliceTeacher->assignRole('teacher');

    $bobTeacher = User::factory()->create([
        'name' => 'Bob Teacher',
        'email' => 'bob.teacher@example.com',
        'approved_at' => now(),
    ]);
    $bobTeacher->assignRole('teacher');

    $saraStudent = User::factory()->create([
        'name' => 'Sara Student',
        'email' => 'sara.student@example.com',
        'approved_at' => now(),
    ]);
    $saraStudent->assignRole('student');

    $omarStudent = User::factory()->create([
        'name' => 'Omar Student',
        'email' => 'omar.student@example.com',
        'approved_at' => now(),
    ]);
    $omarStudent->assignRole('student');

    CourseClass::factory()->create([
        'course_id' => $englishCourse->id,
        'teacher_id' => $aliceTeacher->id,
    ]);

    $frenchGroup = CourseClass::factory()->create([
        'course_id' => $frenchCourse->id,
        'teacher_id' => $bobTeacher->id,
    ]);

    $response = $this->actingAs($secretary)->get(route('secretary.groups'));

    $response->assertOk();
    $response->assertViewHas('availablePrograms', fn ($programs): bool => $programs->pluck('id')->contains($englishProgram->id)
        && $programs->pluck('id')->contains($frenchProgram->id));
    $response->assertViewHas('courses', fn ($courses): bool => $courses->pluck('id')->contains($englishCourse->id)
        && $courses->pluck('id')->contains($frenchCourse->id));
    $response->assertViewHas('enrollGroups', fn ($groups): bool => $groups->pluck('id')->contains($frenchGroup->id));
    $response->assertSee('id="create_group_program_id"', false);
    $response->assertSee('id="create_group_course_id"', false);
    $response->assertSee('id="group_student_program_id"', false);
    $response->assertSee('id="group_student_course_id"', false);
    $response->assertSee('id="group_student_class_id"', false);
    $response->assertSee('id="group_student_search"', false);
    $response->assertSee('secretaryGroupCoursesData', false);
    $response->assertSee('secretaryGroupClassesData', false);
    $response->assertDontSee('Course Search');
    $response->assertDontSee('Teacher Search');
});

it('filters groups by enrolled student name', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $matchedCourse = Course::factory()->create(['name' => 'Matched Course']);
    $unrelatedCourse = Course::factory()->create(['name' => 'Unrelated Course']);
    $matchedGroup = CourseClass::factory()->create(['course_id' => $matchedCourse->id]);
    $unrelatedGroup = CourseClass::factory()->create(['course_id' => $unrelatedCourse->id]);
    $student = createApprovedUserWithRole('student');
    $student->forceFill([
        'name' => 'Needle Search Student',
        'email' => 'needle.student@example.com',
    ])->save();
    $otherStudent = createApprovedUserWithRole('student');
    $matchedGroup->students()->attach($student->id, ['enrolled_at' => now()]);
    $unrelatedGroup->students()->attach($otherStudent->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($secretary)
        ->get(route('secretary.groups', ['student_search' => 'Needle Search']));

    $response->assertOk();
    $response->assertSee('Matched Course');
    $response->assertSee('Needle Search Student');
    $response->assertSee('Student search results for "Needle Search"', false);
    $response->assertViewHas('groups', fn ($groups): bool => $groups->getCollection()->pluck('id')->all() === [$matchedGroup->id]);
});

it('filters groups by enrolled student email and keeps course filter behavior', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $matchedCourse = Course::factory()->create(['name' => 'Email Matched Course']);
    $otherCourse = Course::factory()->create(['name' => 'Filtered Out Course']);
    $matchedGroup = CourseClass::factory()->create(['course_id' => $matchedCourse->id]);
    $otherGroup = CourseClass::factory()->create(['course_id' => $otherCourse->id]);
    $student = createApprovedUserWithRole('student');
    $student->forceFill([
        'name' => 'Email Search Student',
        'email' => 'specific.student@example.com',
    ])->save();
    $matchedGroup->students()->attach($student->id, ['enrolled_at' => now()]);
    $otherGroup->students()->attach($student->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($secretary)
        ->get(route('secretary.groups', [
            'student_search' => 'specific.student@example.com',
            'course_id' => $matchedCourse->id,
        ]));

    $response->assertOk();
    $response->assertSee('Email Matched Course');
    $response->assertSee('specific.student@example.com');
    $response->assertViewHas('groups', fn ($groups): bool => $groups->getCollection()->pluck('id')->all() === [$matchedGroup->id]);
});

it('shows an empty state when student search has no matching enrolled groups', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    CourseClass::factory()->create([
        'course_id' => Course::factory()->create(['name' => 'Existing Course'])->id,
    ]);

    $response = $this->actingAs($secretary)
        ->get(route('secretary.groups', ['student_search' => 'missing.student@example.com']));

    $response->assertOk();
    $response->assertSee('No matching student groups found');
    $response->assertSee('No enrolled student name or email matched this search.');
    $response->assertViewHas('groups', fn ($groups): bool => $groups->isEmpty());
});

it('rejects enrollment when course does not belong to program', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $englishProgram = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $frenchProgram = createLanguageProgramForSecretaryOperations([
        'code' => 'SECFR',
        'locale_code' => 'sec-fr',
        'name' => 'French Program',
        'title' => 'French Program',
    ]);
    $englishCourse = Course::factory()->create([
        'name' => 'English A1',
        'program_id' => $englishProgram->id,
    ]);
    $frenchCourse = Course::factory()->create([
        'name' => 'French A1',
        'program_id' => $frenchProgram->id,
    ]);
    $frenchGroup = CourseClass::factory()->create([
        'course_id' => $frenchCourse->id,
    ]);
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $this->actingAs($secretary)
        ->from(route('secretary.groups'))
        ->post(route('secretary.groups.enroll'), [
            'enroll_program_id' => $englishProgram->id,
            'enroll_course_id' => $frenchCourse->id,
            'class_id' => $frenchGroup->id,
            'student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHasErrors([
            'enroll_course_id' => 'Selected course does not belong to the selected program.',
        ]);
});

it('rejects enrollment when class does not belong to course', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $program = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $courseA = Course::factory()->create([
        'name' => 'English A1',
        'program_id' => $program->id,
    ]);
    $courseB = Course::factory()->create([
        'name' => 'English A2',
        'program_id' => $program->id,
    ]);
    $groupB = CourseClass::factory()->create([
        'course_id' => $courseB->id,
    ]);
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $this->actingAs($secretary)
        ->from(route('secretary.groups'))
        ->post(route('secretary.groups.enroll'), [
            'enroll_program_id' => $program->id,
            'enroll_course_id' => $courseA->id,
            'class_id' => $groupB->id,
            'student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHasErrors([
            'class_id' => 'Selected group does not belong to the selected course.',
        ]);
});

it('secretary can create group by selecting program course teacher and capacity and enroll student', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $program = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $course = Course::factory()->create([
        'name' => 'English B1',
        'program_id' => $program->id,
    ]);

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $this->actingAs($secretary)
        ->post(route('secretary.groups.store'), [
            'program_id' => $program->id,
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'capacity' => 20,
        ])
        ->assertRedirect(route('secretary.groups'));

    $group = CourseClass::query()->where('course_id', $course->id)->first();

    expect($group)->not->toBeNull();
    expect((int) $group?->capacity)->toBe(20);

    $this->actingAs($secretary)
        ->post(route('secretary.groups.enroll'), [
            'enroll_program_id' => $program->id,
            'enroll_course_id' => $course->id,
            'class_id' => $group?->id,
            'student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'));

    $this->assertDatabaseHas('class_student', [
        'class_id' => $group?->id,
        'user_id' => $student->id,
    ]);
});

it('rejects group creation when selected course does not belong to selected program', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $englishProgram = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $frenchProgram = createLanguageProgramForSecretaryOperations([
        'code' => 'SECFR',
        'locale_code' => 'sec-fr',
        'name' => 'French Program',
        'title' => 'French Program',
    ]);
    $frenchCourse = Course::factory()->create([
        'name' => 'French A1',
        'program_id' => $frenchProgram->id,
    ]);

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $this->actingAs($secretary)
        ->from(route('secretary.groups'))
        ->post(route('secretary.groups.store'), [
            'program_id' => $englishProgram->id,
            'course_id' => $frenchCourse->id,
            'teacher_id' => $teacher->id,
            'capacity' => 20,
        ])
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHasErrors([
            'course_id' => 'Selected course does not belong to the selected program.',
        ]);

    expect(CourseClass::query()->count())->toBe(0);
});

it('does not require student lookup when creating a group', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $program = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $course = Course::factory()->create([
        'name' => 'English A1',
        'program_id' => $program->id,
    ]);

    $this->actingAs($secretary)
        ->post(route('secretary.groups.store'), [
            'program_id' => $program->id,
            'course_id' => $course->id,
            'capacity' => 18,
        ])
        ->assertRedirect(route('secretary.groups'));

    $this->assertDatabaseHas('classes', [
        'course_id' => $course->id,
        'teacher_id' => null,
        'capacity' => 18,
    ]);
});

it('rejects duplicate enrollment in same group', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $program = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $course = Course::factory()->create([
        'name' => 'English B1',
        'program_id' => $program->id,
    ]);
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
    ]);
    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    // First enrollment - should succeed
    $this->actingAs($secretary)
        ->post(route('secretary.groups.enroll'), [
            'enroll_program_id' => $program->id,
            'enroll_course_id' => $course->id,
            'class_id' => $group->id,
            'student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'));

    // Second enrollment - should fail
    $this->actingAs($secretary)
        ->from(route('secretary.groups'))
        ->post(route('secretary.groups.enroll'), [
            'enroll_program_id' => $program->id,
            'enroll_course_id' => $course->id,
            'class_id' => $group->id,
            'student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHasErrors([
            'student_id' => 'Student is already enrolled in this group.',
        ]);
});

it('secretary enrolls student into group and sends database notifications to student and linked parent only', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $parent = createApprovedUserWithRole('parent');
    $unrelatedStudent = createApprovedUserWithRole('student');
    $unrelatedParent = createApprovedUserWithRole('parent');
    $program = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $course = Course::factory()->create([
        'name' => 'English B1',
        'code' => 'B1',
        'program_id' => $program->id,
    ]);
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'capacity' => 10,
    ]);
    $student = User::factory()->create([
        'approved_at' => now(),
        'parent_id' => $parent->id,
        'name' => 'Linked Student',
    ]);
    $student->assignRole('student');

    $this->actingAs($secretary)
        ->post(route('secretary.groups.enroll'), [
            'enroll_program_id' => $program->id,
            'enroll_course_id' => $course->id,
            'class_id' => $group->id,
            'student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'));

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $student->id,
        'type' => StudentGroupEnrollmentChangedNotification::class,
    ]);

    $studentNotification = $student->fresh()->notifications()->latest()->first();
    $parentNotification = $parent->fresh()->notifications()->latest()->first();

    expect($studentNotification->data['type'])->toBe('student_group_enrollment_changed');
    expect($studentNotification->data['action'])->toBe('enrolled');
    expect($studentNotification->data['group_id'])->toBe($group->id);
    expect($studentNotification->data['group_name'])->toBe('Group #'.$group->id);
    expect($studentNotification->data['course_name'])->toBe('English B1');
    expect($studentNotification->data['actor_id'])->toBe($secretary->id);
    expect($studentNotification->data['actor_name'])->toBe($secretary->name);
    expect($studentNotification->data['actor_role'])->toBe('secretary');
    expect($studentNotification->data['related_model'])->toBe(CourseClass::class);
    expect($studentNotification->data['related_model_id'])->toBe($group->id);
    expect($studentNotification->data['created_at'])->not()->toBeEmpty();
    expect($studentNotification->data['url'])->toBe(route('student.academic'));

    expect($parentNotification)->not()->toBeNull();
    expect($parentNotification->type)->toBe(StudentGroupEnrollmentChangedNotification::class);
    expect($parentNotification->data['action'])->toBe('enrolled');
    expect($parentNotification->data['recipient_type'])->toBe('parent');
    expect($parentNotification->data['child_id'])->toBe($student->id);
    expect($parentNotification->data['child_name'])->toBe('Linked Student');
    expect($parentNotification->data['url'])->toBe(route('parent.child.academic', ['child' => $student->id]));
    expect($unrelatedStudent->fresh()->notifications()->count())->toBe(0);
    expect($unrelatedParent->fresh()->notifications()->count())->toBe(0);
});

it('admin enrolls student through secretary group functionality and sends the same action notification', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdminForOperations();
    $program = createLanguageProgramForSecretaryOperations();
    $course = Course::factory()->create([
        'name' => 'French A2',
        'code' => 'A2',
        'program_id' => $program->id,
    ]);
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'capacity' => 10,
    ]);
    $student = createApprovedUserWithRole('student');
    $unrelatedStudent = createApprovedUserWithRole('student');

    $this->actingAs($admin)
        ->post(route('secretary.groups.enroll'), [
            'enroll_program_id' => $program->id,
            'enroll_course_id' => $course->id,
            'class_id' => $group->id,
            'student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'));

    $notification = $student->fresh()->notifications()->latest()->first();

    expect($notification)->not()->toBeNull();
    expect($notification->type)->toBe(StudentGroupEnrollmentChangedNotification::class);
    expect($notification->data['action'])->toBe('enrolled');
    expect($notification->data['actor_id'])->toBe($admin->id);
    expect($notification->data['actor_name'])->toBe($admin->name);
    expect($notification->data['actor_role'])->toBe('admin');
    expect($notification->data['message'])->toContain('French A2');
    expect($unrelatedStudent->fresh()->notifications()->count())->toBe(0);
});

it('secretary can remove an enrolled student from a group and notify the student', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $parent = createApprovedUserWithRole('parent');
    $unrelatedStudent = createApprovedUserWithRole('student');
    $unrelatedParent = createApprovedUserWithRole('parent');
    $program = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $course = Course::factory()->create([
        'name' => 'English B2',
        'program_id' => $program->id,
    ]);
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'capacity' => 10,
    ]);
    $student = User::factory()->create([
        'approved_at' => now(),
        'parent_id' => $parent->id,
        'name' => 'Enrolled Student',
        'email' => 'enrolled.student@example.com',
    ]);
    $student->assignRole('student');
    $group->students()->attach($student->id, ['enrolled_at' => now()]);

    $this->actingAs($secretary)
        ->post(route('secretary.groups.remove-student'), [
            'remove_program_id' => $program->id,
            'remove_course_id' => $course->id,
            'remove_class_id' => $group->id,
            'remove_student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHas('success', 'Student removed from group successfully.');

    $this->assertDatabaseMissing('class_student', [
        'class_id' => $group->id,
        'user_id' => $student->id,
    ]);

    $studentNotification = $student->fresh()->notifications()->latest()->first();
    $parentNotification = $parent->fresh()->notifications()->latest()->first();

    expect($studentNotification)->not()->toBeNull();
    expect($studentNotification->type)->toBe(StudentGroupEnrollmentChangedNotification::class);
    expect($studentNotification->data['action'])->toBe('removed');
    expect($studentNotification->data['title'])->toBe('Removed from group');
    expect($studentNotification->data['message'])->toContain('Group #'.$group->id);
    expect($studentNotification->data['message'])->toContain($course->name);
    expect($studentNotification->data['message'])->toContain('English Program');
    expect($studentNotification->data['actor_id'])->toBe($secretary->id);
    expect($studentNotification->data['actor_role'])->toBe('secretary');

    expect($parentNotification)->not()->toBeNull();
    expect($parentNotification->type)->toBe(StudentGroupEnrollmentChangedNotification::class);
    expect($parentNotification->data['action'])->toBe('removed');
    expect($parentNotification->data['recipient_type'])->toBe('parent');
    expect($parentNotification->data['child_id'])->toBe($student->id);
    expect($unrelatedStudent->fresh()->notifications()->count())->toBe(0);
    expect($unrelatedParent->fresh()->notifications()->count())->toBe(0);
});

it('secretary cannot remove a student who is not enrolled in the selected group', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $program = createLanguageProgramForSecretaryOperations();
    $course = Course::factory()->create([
        'program_id' => $program->id,
    ]);
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
    ]);
    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');

    $this->actingAs($secretary)
        ->from(route('secretary.groups'))
        ->post(route('secretary.groups.remove-student'), [
            'remove_program_id' => $program->id,
            'remove_course_id' => $course->id,
            'remove_class_id' => $group->id,
            'remove_student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHasErrors([
            'remove_student_id' => 'Selected student is not enrolled in the selected group.',
        ]);
});

it('blocks mismatched program course and group combinations when removing a student from a group', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $programA = createLanguageProgramForSecretaryOperations([
        'name' => 'Program A',
    ]);
    $programB = createLanguageProgramForSecretaryOperations([
        'name' => 'Program B',
        'code' => 'SECB',
        'locale_code' => 'secb',
        'title' => 'Program B',
    ]);
    $courseA = Course::factory()->create([
        'program_id' => $programA->id,
    ]);
    $courseB = Course::factory()->create([
        'program_id' => $programB->id,
    ]);
    $courseC = Course::factory()->create([
        'program_id' => $programB->id,
    ]);
    $groupB = CourseClass::factory()->create([
        'course_id' => $courseB->id,
    ]);
    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');
    $groupB->students()->attach($student->id, ['enrolled_at' => now()]);

    $this->actingAs($secretary)
        ->from(route('secretary.groups'))
        ->post(route('secretary.groups.remove-student'), [
            'remove_program_id' => $programA->id,
            'remove_course_id' => $courseB->id,
            'remove_class_id' => $groupB->id,
            'remove_student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHasErrors([
            'remove_course_id' => 'Selected course does not belong to the selected program.',
        ]);

    $this->actingAs($secretary)
        ->from(route('secretary.groups'))
        ->post(route('secretary.groups.remove-student'), [
            'remove_program_id' => $programB->id,
            'remove_course_id' => $courseC->id,
            'remove_class_id' => $groupB->id,
            'remove_student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHasErrors([
            'remove_class_id' => 'Selected group does not belong to the selected course.',
        ]);
});

it('admin removes student through secretary group functionality and sends the same action notification', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdminForOperations();
    $program = createLanguageProgramForSecretaryOperations();
    $course = Course::factory()->create([
        'program_id' => $program->id,
    ]);
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
    ]);
    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');
    $group->students()->attach($student->id, ['enrolled_at' => now()]);

    $this->actingAs($admin)
        ->post(route('secretary.groups.remove-student'), [
            'remove_program_id' => $program->id,
            'remove_course_id' => $course->id,
            'remove_class_id' => $group->id,
            'remove_student_id' => $student->id,
        ])
        ->assertRedirect(route('secretary.groups'));

    $this->assertDatabaseMissing('class_student', [
        'class_id' => $group->id,
        'user_id' => $student->id,
    ]);

    $notification = $student->fresh()->notifications()->latest()->first();

    expect($notification)->not()->toBeNull();
    expect($notification->type)->toBe(StudentGroupEnrollmentChangedNotification::class);
    expect($notification->data['action'])->toBe('removed');
    expect($notification->data['actor_id'])->toBe($admin->id);
    expect($notification->data['actor_name'])->toBe($admin->name);
    expect($notification->data['actor_role'])->toBe('admin');
});

it('students cannot remove other students from groups', function () {
    /** @var TestCase $this */
    $actor = createApprovedUserWithRole('student');
    $program = createLanguageProgramForSecretaryOperations();
    $course = Course::factory()->create([
        'program_id' => $program->id,
    ]);
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
    ]);
    $student = createApprovedUserWithRole('student');
    $group->students()->attach($student->id, ['enrolled_at' => now()]);

    $this->actingAs($actor)
        ->post(route('secretary.groups.remove-student'), [
            'remove_program_id' => $program->id,
            'remove_course_id' => $course->id,
            'remove_class_id' => $group->id,
            'remove_student_id' => $student->id,
        ])
        ->assertForbidden();

    expect($student->fresh()->notifications()->count())->toBe(0);
});

it('student search endpoint returns only approved students with student role and enrollment status', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $program = createLanguageProgramForSecretaryOperations();
    $course = Course::factory()->create([
        'program_id' => $program->id,
    ]);
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
    ]);

    $approvedStudent = User::factory()->create([
        'name' => 'Alice Student',
        'email' => 'alice@example.com',
        'approved_at' => now(),
    ]);
    $approvedStudent->assignRole('student');

    $pendingStudent = User::factory()->create([
        'name' => 'Bob Student',
        'email' => 'bob@example.com',
        'approved_at' => null,
    ]);
    $pendingStudent->assignRole('student');

    $approvedTeacher = User::factory()->create([
        'name' => 'Alice Teacher',
        'email' => 'alice.teacher@example.com',
        'approved_at' => now(),
    ]);
    $approvedTeacher->assignRole('teacher');
    $group->students()->attach($approvedStudent->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($secretary)
        ->get(route('secretary.groups.students.search', [
            'q' => 'Alice',
            'class_id' => $group->id,
        ]));

    $response->assertOk();
    $data = $response->json();
    expect($data['students'])->toHaveCount(1);
    expect($data['students'][0]['id'])->toBe($approvedStudent->id);
    expect($data['students'][0]['name'])->toBe('Alice Student');
    expect($data['students'][0]['email'])->toBe('alice@example.com');
    expect($data['students'][0]['is_enrolled'])->toBeTrue();
});

it('student search endpoint marks non enrolled approved students as addable', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $program = createLanguageProgramForSecretaryOperations();
    $course = Course::factory()->create([
        'program_id' => $program->id,
    ]);
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
    ]);

    $student = User::factory()->create([
        'name' => 'Chris Student',
        'email' => 'chris@example.com',
        'approved_at' => now(),
    ]);
    $student->assignRole('student');

    $response = $this->actingAs($secretary)
        ->get(route('secretary.groups.students.search', [
            'q' => 'Chris',
            'class_id' => $group->id,
        ]));

    $response->assertOk();
    $data = $response->json();
    expect($data['students'])->toHaveCount(1);
    expect($data['students'][0]['id'])->toBe($student->id);
    expect($data['students'][0]['is_enrolled'])->toBeFalse();
});

it('notifies admins when secretary creates a group', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $admin = createApprovedAdminForOperations();

    $program = createLanguageProgramForSecretaryOperations([
        'name' => 'English Program',
    ]);
    $course = Course::factory()->create([
        'name' => 'English A2',
        'program_id' => $program->id,
    ]);

    $teacher = User::factory()->create([
        'name' => 'Group Teacher',
        'approved_at' => now(),
    ]);
    $teacher->assignRole('teacher');

    $this->actingAs($secretary)
        ->post(route('secretary.groups.store'), [
            'program_id' => $program->id,
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'capacity' => 24,
        ])
        ->assertRedirect(route('secretary.groups'));

    $notification = $admin->notifications()->first();

    expect($notification)->not->toBeNull();
    expect($notification?->type)->toBe(SecretaryAnnouncementNotification::class);
    expect($notification?->data['title'])->toBe('New group created');
    expect($notification?->data['message'])->toContain('English A2');
    expect($notification?->data['message'])->toContain('Group Teacher');
    expect($notification?->data['message'])->toContain('Capacity: 24');
    expect($notification?->data['message'])->toContain($secretary->name);
    expect($notification?->data['message'])->toContain('assign this group to a classroom');
    expect($notification?->data['url'])->toBe(route('admin.schedule.index'));

    $this->actingAs($admin)
        ->get(route('admin.notifications'))
        ->assertOk()
        ->assertSee('New group created')
        ->assertSee('English A2')
        ->assertSee('assign this group to a classroom');
});

it('does not notify admins when group creation validation fails', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $admin = createApprovedAdminForOperations();

    $this->actingAs($secretary)
        ->from(route('secretary.groups'))
        ->post(route('secretary.groups.store'), [
            'course_id' => '',
            'teacher_id' => '',
            'capacity' => 0,
        ])
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHasErrors(['course_id', 'capacity']);

    expect(CourseClass::query()->count())->toBe(0);
    expect($admin->notifications()->count())->toBe(0);
});

it('secretary can update and delete group without schedules', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $courseA = Course::factory()->create(['name' => 'Course A']);
    $courseB = Course::factory()->create(['name' => 'Course B']);

    $teacherA = User::factory()->create(['approved_at' => now()]);
    $teacherA->assignRole('teacher');

    $teacherB = User::factory()->create(['approved_at' => now()]);
    $teacherB->assignRole('teacher');

    $group = CourseClass::factory()->create([
        'course_id' => $courseA->id,
        'teacher_id' => $teacherA->id,
        'capacity' => 25,
    ]);

    $this->actingAs($secretary)
        ->patch(route('secretary.groups.update', $group), [
            'course_id' => $courseB->id,
            'teacher_id' => $teacherB->id,
            'capacity' => 35,
        ])
        ->assertRedirect(route('secretary.groups'));

    $group->refresh();
    expect($group->course_id)->toBe($courseB->id);
    expect($group->teacher_id)->toBe($teacherB->id);
    expect((int) $group->capacity)->toBe(35);

    $this->actingAs($secretary)
        ->delete(route('secretary.groups.destroy', $group))
        ->assertRedirect(route('secretary.groups'));

    $this->assertDatabaseMissing('classes', [
        'id' => $group->id,
    ]);
});

it('notifies the assigned teacher when secretary creates a group with a teacher', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $teacher = createApprovedUserWithRole('teacher');
    $otherTeacher = createApprovedUserWithRole('teacher');
    $course = Course::factory()->create([
        'name' => 'English',
        'code' => 'A1',
    ]);

    $this->actingAs($secretary)
        ->post(route('secretary.groups.store'), [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'capacity' => 24,
        ])
        ->assertRedirect(route('secretary.groups'));

    $notification = $teacher->fresh()->notifications()->latest()->first();

    expect($notification)->not()->toBeNull();
    expect($notification->type)->toBe(TeacherGroupAssignedNotification::class);
    expect($notification->data['type'])->toBe('teacher_group_assigned');
    expect($notification->data['title'])->toBe('New group assignment');
    expect($notification->data['message'])->toContain('Group #');
    expect($notification->data['message'])->toContain('English A1');
    expect($notification->data['issuer_name'])->toBe($secretary->name);
    expect($notification->data['actor_name'])->toBe($secretary->name);
    expect($notification->data['actor_role'])->toBe('secretary');
    expect($notification->data['related_model'])->toBe(CourseClass::class);
    expect($notification->data['url'])->toBe(route('timetable.teacher'));
    expect($otherTeacher->fresh()->notifications()->count())->toBe(0);
});

it('does not notify again when secretary updates a group without changing teacher', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $teacher = createApprovedUserWithRole('teacher');
    $course = Course::factory()->create();
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'capacity' => 20,
    ]);

    $this->actingAs($secretary)
        ->patch(route('secretary.groups.update', $group), [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'capacity' => 25,
        ])
        ->assertRedirect(route('secretary.groups'));

    expect($teacher->fresh()->notifications()->count())->toBe(0);
});

it('notifies the old and new teachers when secretary replaces a group teacher', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $teacherA = createApprovedUserWithRole('teacher');
    $teacherB = createApprovedUserWithRole('teacher');
    $otherTeacher = createApprovedUserWithRole('teacher');
    $course = Course::factory()->create();
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacherA->id,
        'capacity' => 20,
    ]);

    $this->actingAs($secretary)
        ->patch(route('secretary.groups.update', $group), [
            'course_id' => $course->id,
            'teacher_id' => $teacherB->id,
            'capacity' => 20,
        ])
        ->assertRedirect(route('secretary.groups'));

    $removedNotification = $teacherA->fresh()->notifications()->latest()->first();
    $assignedNotification = $teacherB->fresh()->notifications()->latest()->first();

    expect($removedNotification)->not()->toBeNull();
    expect($removedNotification->type)->toBe(TeacherGroupAssignedNotification::class);
    expect($removedNotification->data['type'])->toBe('teacher_group_removed');
    expect($removedNotification->data['action'])->toBe('removed');
    expect($removedNotification->data['actor_role'])->toBe('secretary');
    expect($assignedNotification)->not()->toBeNull();
    expect($assignedNotification->data['type'])->toBe('teacher_group_assigned');
    expect($assignedNotification->data['action'])->toBe('assigned');
    expect($otherTeacher->fresh()->notifications()->count())->toBe(0);
});

it('notifies the old teacher when secretary removes a group teacher without replacement', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();
    $teacher = createApprovedUserWithRole('teacher');
    $course = Course::factory()->create();
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'capacity' => 20,
    ]);

    $this->actingAs($secretary)
        ->patch(route('secretary.groups.update', $group), [
            'course_id' => $course->id,
            'teacher_id' => '',
            'capacity' => 20,
        ])
        ->assertRedirect(route('secretary.groups'));

    $notification = $teacher->fresh()->notifications()->latest()->first();

    expect($notification)->not()->toBeNull();
    expect($notification->type)->toBe(TeacherGroupAssignedNotification::class);
    expect($notification->data['type'])->toBe('teacher_group_removed');
    expect($notification->data['action'])->toBe('removed');
    expect($notification->data['message'])->toContain('no longer assigned');
});

it('secretary cannot delete group with schedules', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $course = Course::factory()->create();
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $room = Room::factory()->create();
    Schedule::factory()->create([
        'class_id' => $group->id,
        'room_id' => $room->id,
    ]);

    $this->actingAs($secretary)
        ->delete(route('secretary.groups.destroy', $group))
        ->assertRedirect(route('secretary.groups'))
        ->assertSessionHasErrors('group');

    $this->assertDatabaseHas('classes', [
        'id' => $group->id,
    ]);
});

it('renders secretary accounts page', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');

    $response = $this->actingAs($secretary)->get(route('secretary.accounts'));

    $response->assertOk();
    $response->assertViewIs('secretary.accounts');
    $response->assertSee('Manage Accounts');
});

it('secretary can edit managed account details', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $account = User::factory()->create([
        'name' => 'Old Student Name',
        'email' => 'old.student@example.com',
        'requested_role' => 'student',
        'approved_at' => now(),
    ]);
    $account->assignRole('student');

    $this->actingAs($secretary)
        ->patch(route('secretary.accounts.update', $account), [
            'name' => 'Updated Account Name',
            'email' => 'updated.student@example.com',
            'requested_role' => 'parent',
            'date_of_birth' => '2005-02-11',
        ])
        ->assertRedirect(route('secretary.accounts'));

    $account->refresh();
    expect($account->name)->toBe('Updated Account Name');
    expect($account->email)->toBe('updated.student@example.com');
    expect($account->requested_role)->toBe('parent');
    expect($account->hasRole('parent'))->toBeTrue();
});

it('secretary can delete managed account without dependencies', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $account = User::factory()->create([
        'approved_at' => now(),
        'requested_role' => 'teacher',
    ]);
    $account->assignRole('teacher');

    $this->actingAs($secretary)
        ->delete(route('secretary.accounts.destroy', $account))
        ->assertRedirect(route('secretary.accounts'));

    $this->assertDatabaseMissing('users', [
        'id' => $account->id,
    ]);
});

it('publishes secretary notifications to selected audience', function () {
    /** @var TestCase $this */
    Notification::fake();

    $secretary = createApprovedSecretaryForOperations();

    $student = User::factory()->create(['approved_at' => now()]);
    $student->assignRole('student');

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $response = $this->actingAs($secretary)
        ->post(route('secretary.publish-notifications.send'), [
            'title' => 'Important Update',
            'message' => 'Please review the updated schedule.',
            'audience' => 'students',
            'include_secretaries' => false,
        ]);

    $response->assertRedirect(route('secretary.publish-notifications'));

    Notification::assertSentTo($student, SecretaryAnnouncementNotification::class);
    Notification::assertNotSentTo($teacher, SecretaryAnnouncementNotification::class);
});

it('renders secretary settings page', function () {
    /** @var TestCase $this */
    $secretary = createApprovedSecretaryForOperations();

    $response = $this->actingAs($secretary)->get(route('secretary.settings'));

    $response->assertOk();
    $response->assertViewIs('secretary.settings');
    $response->assertSee('Profile Settings');
});
