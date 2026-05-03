<?php

use App\Models\EmployeePayment;
use App\Models\ScholarshipActivation;
use App\Models\TeacherResource;
use App\Models\TuitionPayment;
use App\Models\User;
use App\Notifications\ClassResourceUploadedNotification;
use App\Notifications\EmployeePaymentRecordedNotification;
use App\Notifications\StudentGroupEnrollmentChangedNotification;
use App\Notifications\TeacherGroupAssignedNotification;
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

it('seeds presentation employee payments and contextual notification flows', function () {
    $this->seed([
        PermissionSeeder::class,
        RoleSeeder::class,
        PresentationDemoSeeder::class,
    ]);

    $sofia = User::query()->where('email', 'teacher.sofia@lumina.test')->firstOrFail();
    $karim = User::query()->where('email', 'teacher.karim@lumina.test')->firstOrFail();
    $secretary = User::query()->where('email', 'secretary@lumina.test')->firstOrFail();
    $alex = User::query()->where('email', 'student.alex@lumina.test')->firstOrFail();
    $lina = User::query()->where('email', 'student.lina@lumina.test')->firstOrFail();
    $yacine = User::query()->where('email', 'student.yacine@lumina.test')->firstOrFail();
    $sara = User::query()->where('email', 'student.sara@lumina.test')->firstOrFail();
    $maya = User::query()->where('email', 'parent.maya@lumina.test')->firstOrFail();
    $amine = User::query()->where('email', 'parent.amine@lumina.test')->firstOrFail();

    expect(EmployeePayment::query()->where('employee_id', $sofia->id)->firstOrFail()->amount_paid)->toBe(50000);
    expect(EmployeePayment::query()->where('employee_id', $karim->id)->firstOrFail()->amount_paid)->toBe(20000);
    expect(EmployeePayment::query()->where('employee_id', $secretary->id)->firstOrFail()->amount_paid)->toBe(25000);

    expect($sofia->notifications()->where('type', EmployeePaymentRecordedNotification::class)->where('data->type', 'employee_payment_recorded')->exists())->toBeTrue();
    expect($karim->notifications()->where('type', TeacherGroupAssignedNotification::class)->where('data->type', 'teacher_group_assigned')->exists())->toBeTrue();
    expect($secretary->notifications()->where('type', EmployeePaymentRecordedNotification::class)->whereNull('read_at')->exists())->toBeTrue();
    expect($lina->notifications()->where('type', StudentGroupEnrollmentChangedNotification::class)->where('data->action', 'enrolled')->where('data->actor_role', 'secretary')->exists())->toBeTrue();
    expect($sara->notifications()->where('type', StudentGroupEnrollmentChangedNotification::class)->where('data->action', 'removed')->where('data->actor_role', 'admin')->exists())->toBeTrue();
    expect($maya->notifications()->where('type', StudentGroupEnrollmentChangedNotification::class)->where('data->child_name', 'Lina Benali')->exists())->toBeTrue();
    expect($amine->notifications()->where('type', StudentGroupEnrollmentChangedNotification::class)->where('data->child_name', 'Sara Haddad')->exists())->toBeTrue();

    foreach ([$alex, $lina, $yacine] as $student) {
        expect($student->notifications()->where('type', ClassResourceUploadedNotification::class)->where('data->type', 'homework_uploaded')->exists())->toBeTrue();
    }

    expect($sara->notifications()->where('type', ClassResourceUploadedNotification::class)->where('data->type', 'class_resource_uploaded')->exists())->toBeTrue();
    expect($maya->notifications()->where('type', ClassResourceUploadedNotification::class)->where('data->child_name', 'Lina Benali')->exists())->toBeTrue();
    expect($amine->notifications()->where('type', ClassResourceUploadedNotification::class)->where('data->child_name', 'Sara Haddad')->whereNull('read_at')->exists())->toBeTrue();
    expect(TeacherResource::query()->where('name', 'A2 Speaking Homework Week 1')->exists())->toBeTrue();
});

it('can rerun presentation demo seeder without duplicating fixed demo records', function () {
    $this->seed([
        PermissionSeeder::class,
        RoleSeeder::class,
        PresentationDemoSeeder::class,
    ]);

    $counts = [
        'users' => User::query()->where('email', 'like', '%@lumina.test')->count(),
        'payments' => TuitionPayment::query()->where('notes', 'Seeded presentation payment.')->count(),
        'employee_payments' => EmployeePayment::query()->where('notes', 'like', 'Presentation demo:%')->count(),
        'resources' => TeacherResource::query()->where('file_path', 'like', 'teacher-resources/demo/%')->count(),
        'notifications' => User::query()
            ->where('email', 'like', '%@lumina.test')
            ->get()
            ->sum(fn (User $user): int => $user->notifications()->count()),
    ];

    $this->seed(PresentationDemoSeeder::class);

    expect(User::query()->where('email', 'like', '%@lumina.test')->count())->toBe($counts['users']);
    expect(TuitionPayment::query()->where('notes', 'Seeded presentation payment.')->count())->toBe($counts['payments']);
    expect(EmployeePayment::query()->where('notes', 'like', 'Presentation demo:%')->count())->toBe($counts['employee_payments']);
    expect(TeacherResource::query()->where('file_path', 'like', 'teacher-resources/demo/%')->count())->toBe($counts['resources']);
    expect(User::query()
        ->where('email', 'like', '%@lumina.test')
        ->get()
        ->sum(fn (User $user): int => $user->notifications()->count()))->toBe($counts['notifications']);
});
