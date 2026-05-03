<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\EmployeePayment;
use App\Models\LanguageProgram;
use App\Models\Message;
use App\Models\Review;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScholarshipActivation;
use App\Models\StudentCard;
use App\Models\StudentTuition;
use App\Models\TeacherResource;
use App\Models\TuitionPayment;
use App\Models\User;
use App\Notifications\ClassResourceUploadedNotification;
use App\Notifications\EmployeePaymentRecordedNotification;
use App\Notifications\SecretaryAnnouncementNotification;
use App\Notifications\StudentGroupEnrollmentChangedNotification;
use App\Notifications\TeacherGroupAssignedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PresentationDemoSeeder extends Seeder
{
    private string $password = 'password';

    /**
     * Seed a clean, predictable presentation dataset.
     */
    public function run(): void
    {
        $admin = $this->createUser('Admin Demo', 'admin@lumina.test', 'admin');
        $secretary = $this->createUser('Sarah Secretary', 'secretary@lumina.test', 'secretary', [
            'phone' => '0550 100 100',
        ], $admin);

        $programs = $this->seedPrograms();
        $courses = $this->seedCourses($programs);
        $rooms = $this->seedRooms();

        $teachers = [
            'sofia' => $this->createUser('Sofia Rossi', 'teacher.sofia@lumina.test', 'teacher', [
                'phone' => '0551 210 001',
                'preferred_language' => 'english',
                'bio' => 'IELTS and academic writing specialist with a practical coaching style.',
            ], $admin),
            'karim' => $this->createUser('Karim Haddad', 'teacher.karim@lumina.test', 'teacher', [
                'phone' => '0551 210 002',
                'preferred_language' => 'french',
                'bio' => 'French and Spanish instructor focused on conversation confidence.',
            ], $admin),
            'nadia' => $this->createUser('Nadia Klein', 'teacher.nadia@lumina.test', 'teacher', [
                'phone' => '0551 210 003',
                'preferred_language' => 'german',
                'bio' => 'German language mentor for professional and study-abroad learners.',
            ], $admin),
        ];

        $groups = $this->seedGroups($courses, $teachers, $rooms);

        $parents = [
            'maya' => $this->createUser('Maya Benali', 'parent.maya@lumina.test', 'parent', [
                'phone' => '0552 300 001',
            ], $admin),
            'amine' => $this->createUser('Amine Haddad', 'parent.amine@lumina.test', 'parent', [
                'phone' => '0552 300 002',
            ], $admin),
        ];

        $students = [
            'alex' => $this->createStudent('Alex Benali', 'student.alex@lumina.test', '2003-04-12', $parents['maya'], $courses['ielts'], $admin),
            'lina' => $this->createStudent('Lina Benali', 'student.lina@lumina.test', '2010-09-18', $parents['maya'], $courses['english'], $admin),
            'yacine' => $this->createStudent('Yacine Benali', 'student.yacine@lumina.test', '2011-02-05', $parents['maya'], $courses['french'], $admin),
            'omar' => $this->createStudent('Omar Haddad', 'student.omar@lumina.test', '2002-11-28', $parents['amine'], $courses['french'], $admin),
            'sara' => $this->createStudent('Sara Haddad', 'student.sara@lumina.test', '2009-07-09', $parents['amine'], $courses['spanish'], $admin),
            'nour' => $this->createStudent('Nour Bensaid', 'student.nour@lumina.test', '2001-01-21', null, $courses['german'], $admin),
        ];

        $this->seedEnrollments($groups, $students);
        $this->seedFinancials($students, $parents, $secretary);
        $this->seedEmployeePayments($teachers, $secretary, $admin);
        $this->seedAttendance($groups, $students);
        $this->seedResources($groups, $teachers);
        $this->seedMessages($students, $parents, $teachers, $secretary, $admin);
        $this->seedNotifications($admin, $secretary, $teachers, $parents, $students, $groups);
        $this->seedReviews($students);
        $this->seedPendingApproval($courses['english']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(string $name, string $email, string $role, array $attributes = [], ?User $approver = null): User
    {
        $user = User::query()->updateOrCreate(['email' => $email], array_merge([
            'name' => $name,
            'email_verified_at' => now(),
            'password' => $this->password,
            'requested_role' => $role,
            'approved_at' => now()->subDays(45),
            'approved_by' => $approver?->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ], $attributes));

        $user->syncRoles([$role]);

        return $user;
    }

    private function createStudent(string $name, string $email, string $birthDate, ?User $parent, Course $requestedCourse, User $approver): User
    {
        $student = $this->createUser($name, $email, 'student', [
            'date_of_birth' => $birthDate,
            'parent_id' => $this->parentIdForStudent($parent, $birthDate),
            'requested_course_id' => $requestedCourse->id,
        ], $approver);

        $cardNumber = 'LUM-2026-'.str_pad((string) $student->id, 4, '0', STR_PAD_LEFT);

        StudentCard::query()->updateOrCreate(
            ['card_number' => $cardNumber],
            [
                'user_id' => $student->id,
                'valid_from' => now()->subDays(45)->toDateString(),
                'valid_to' => now()->addMonths(4)->toDateString(),
                'academic_year' => now()->year.'/'.(now()->year + 1),
                'status' => 'active',
            ]
        );

        StudentTuition::query()->updateOrCreate(
            ['student_id' => $student->id],
            [
                'course_id' => $requestedCourse->id,
                'course_price' => (int) $requestedCourse->price,
            ]
        );

        return $student;
    }

    private function parentIdForStudent(?User $parent, string $birthDate): ?int
    {
        if (! $parent instanceof User) {
            return null;
        }

        return CarbonImmutable::parse($birthDate)->age < 18 ? $parent->id : null;
    }

    /**
     * @return array<string, LanguageProgram>
     */
    private function seedPrograms(): array
    {
        $programs = [
            'english' => [
                'code' => 'en',
                'locale_code' => 'EN-GB',
                'name' => 'English',
                'title' => 'English Mastery',
                'description' => 'Practical fluency for school, work, and international exams.',
                'full_description' => 'A structured English pathway covering speaking, grammar, academic writing, and exam preparation.',
                'flag_url' => 'https://flagcdn.com/w80/gb.png',
                'certifications' => [['name' => 'IELTS', 'exams' => ['Academic', 'General Training']]],
                'sort_order' => 1,
            ],
            'french' => [
                'code' => 'fr',
                'locale_code' => 'FR-FR',
                'name' => 'French',
                'title' => 'French Excellence',
                'description' => 'French for mobility, study, and professional communication.',
                'full_description' => 'A communication-first French track with DELF-aligned progression and weekly speaking labs.',
                'flag_url' => 'https://flagcdn.com/w80/fr.png',
                'certifications' => [['name' => 'DELF', 'exams' => ['A1', 'A2', 'B1', 'B2']]],
                'sort_order' => 2,
            ],
            'spanish' => [
                'code' => 'es',
                'locale_code' => 'ES-ES',
                'name' => 'Spanish',
                'title' => 'Spanish Immersion',
                'description' => 'Everyday Spanish through guided conversation and grammar.',
                'full_description' => 'A beginner-friendly Spanish program designed around real situations and DELE foundations.',
                'flag_url' => 'https://flagcdn.com/w80/es.png',
                'certifications' => [['name' => 'DELE', 'exams' => ['A1', 'A2', 'B1']]],
                'sort_order' => 3,
            ],
            'german' => [
                'code' => 'de',
                'locale_code' => 'DE-DE',
                'name' => 'German',
                'title' => 'Business German',
                'description' => 'German for work, study-abroad, and technical communication.',
                'full_description' => 'A practical German program with CEFR progression, workplace vocabulary, and interview practice.',
                'flag_url' => 'https://flagcdn.com/w80/de.png',
                'certifications' => [['name' => 'Goethe', 'exams' => ['A1', 'A2', 'B1', 'B2']]],
                'sort_order' => 4,
            ],
        ];

        return collect($programs)
            ->map(fn (array $program): LanguageProgram => LanguageProgram::query()->updateOrCreate(
                ['code' => $program['code']],
                array_merge($program, ['is_active' => true])
            ))
            ->all();
    }

    /**
     * @param  array<string, LanguageProgram>  $programs
     * @return array<string, Course>
     */
    private function seedCourses(array $programs): array
    {
        $courses = [
            'ielts' => ['IELTS Preparation', 'IELTS-PREP', 36000, 'Focused preparation for IELTS Academic and General modules.', $programs['english']],
            'english' => ['English A2 Conversation', 'ENG-A2', 24000, 'Build daily communication through small-group practice.', $programs['english']],
            'french' => ['French B1 Intensive', 'FR-B1', 26000, 'Intermediate French with DELF-style speaking and writing.', $programs['french']],
            'spanish' => ['Spanish A1 Starter', 'ES-A1', 22000, 'A friendly entry point for first-time Spanish learners.', $programs['spanish']],
            'german' => ['German B1 Professional', 'DE-B1', 28000, 'Professional German for workplace and study contexts.', $programs['german']],
        ];

        return collect($courses)
            ->map(fn (array $course): Course => Course::query()->updateOrCreate(
                ['code' => $course[1]],
                [
                    'name' => $course[0],
                    'price' => $course[2],
                    'description' => $course[3],
                    'program_id' => $course[4]->id,
                ]
            ))
            ->all();
    }

    /**
     * @return array<string, Room>
     */
    private function seedRooms(): array
    {
        return [
            'orchid' => Room::query()->updateOrCreate(['name' => 'Room Orchid'], ['capacity' => 24, 'location' => 'First floor']),
            'cedar' => Room::query()->updateOrCreate(['name' => 'Room Cedar'], ['capacity' => 30, 'location' => 'Second floor']),
            'atlas' => Room::query()->updateOrCreate(['name' => 'Room Atlas'], ['capacity' => 18, 'location' => 'Ground floor']),
        ];
    }

    /**
     * @param  array<string, Course>  $courses
     * @param  array<string, User>  $teachers
     * @param  array<string, Room>  $rooms
     * @return array<string, CourseClass>
     */
    private function seedGroups(array $courses, array $teachers, array $rooms): array
    {
        $groups = [
            'ielts' => CourseClass::query()->updateOrCreate(['course_id' => $courses['ielts']->id], ['teacher_id' => $teachers['sofia']->id, 'capacity' => 30]),
            'english' => CourseClass::query()->updateOrCreate(['course_id' => $courses['english']->id], ['teacher_id' => $teachers['sofia']->id, 'capacity' => 24]),
            'french' => CourseClass::query()->updateOrCreate(['course_id' => $courses['french']->id], ['teacher_id' => $teachers['karim']->id, 'capacity' => 24]),
            'spanish' => CourseClass::query()->updateOrCreate(['course_id' => $courses['spanish']->id], ['teacher_id' => $teachers['karim']->id, 'capacity' => 18]),
            'german' => CourseClass::query()->updateOrCreate(['course_id' => $courses['german']->id], ['teacher_id' => $teachers['nadia']->id, 'capacity' => 18]),
        ];

        $slots = [
            ['ielts', 'monday', '09:00', '10:30', 'cedar'],
            ['ielts', 'wednesday', '09:00', '10:30', 'cedar'],
            ['english', 'tuesday', '14:00', '15:30', 'orchid'],
            ['english', 'thursday', '14:00', '15:30', 'orchid'],
            ['french', 'monday', '16:00', '17:30', 'atlas'],
            ['french', 'thursday', '16:00', '17:30', 'atlas'],
            ['spanish', 'saturday', '10:00', '11:30', 'orchid'],
            ['german', 'sunday', '11:00', '12:30', 'cedar'],
        ];

        foreach ($slots as [$groupKey, $day, $start, $end, $roomKey]) {
            Schedule::query()->updateOrCreate(
                [
                    'class_id' => $groups[$groupKey]->id,
                    'day_of_week' => $day,
                    'start_time' => $start,
                ],
                [
                    'end_time' => $end,
                    'room_id' => $rooms[$roomKey]->id,
                ]
            );
        }

        return $groups;
    }

    /**
     * @param  array<string, CourseClass>  $groups
     * @param  array<string, User>  $students
     */
    private function seedEnrollments(array $groups, array $students): void
    {
        $enrollments = [
            'ielts' => ['alex', 'lina', 'omar', 'sara', 'nour'],
            'english' => ['alex', 'lina', 'yacine'],
            'french' => ['alex', 'omar', 'yacine'],
            'spanish' => ['alex', 'lina', 'sara'],
            'german' => ['sara', 'nour'],
        ];

        foreach ($enrollments as $groupKey => $studentKeys) {
            $groups[$groupKey]->students()->syncWithoutDetaching(
                collect($studentKeys)
                    ->mapWithKeys(fn (string $studentKey): array => [
                        $students[$studentKey]->id => ['enrolled_at' => now()->subWeeks(6)],
                    ])
                    ->all()
            );
        }
    }

    /**
     * @param  array<string, User>  $students
     * @param  array<string, User>  $parents
     */
    private function seedFinancials(array $students, array $parents, User $secretary): void
    {
        if ($this->linkedChildrenCount($students, $parents['maya']) >= 3) {
            ScholarshipActivation::query()->updateOrCreate(
                [
                    'parent_id' => $parents['maya']->id,
                    'student_id' => null,
                    'offer_key' => 'family_3_children',
                ],
                [
                    'discount_percent' => 12,
                    'activated_at' => now()->subWeeks(2),
                    'meta' => ['reason' => 'Three linked children enrolled for the presentation demo.'],
                ]
            );
        }

        ScholarshipActivation::query()->updateOrCreate(
            [
                'parent_id' => $students['alex']->parent_id ?? $students['alex']->id,
                'student_id' => $students['alex']->id,
                'offer_key' => 'multi_course_4_plus',
            ],
            [
                'discount_percent' => 10,
                'activated_at' => now()->subWeek(),
                'meta' => ['reason' => 'Alex is enrolled in four active course groups.'],
            ]
        );

        $payments = [
            ['alex', 42000, 'cash', 'PAY-ALEX-001', now()->subWeeks(5)],
            ['alex', 18000, 'bank_transfer', 'PAY-ALEX-002', now()->subWeeks(2)],
            ['lina', 12000, 'card', 'PAY-LINA-001', now()->subWeeks(3)],
            ['yacine', 10000, 'cash', 'PAY-YACINE-001', now()->subDays(12)],
            ['omar', 16000, 'bank_transfer', 'PAY-OMAR-001', now()->subWeeks(4)],
            ['sara', 8000, 'cash', 'PAY-SARA-001', now()->subDays(10)],
            ['nour', 28000, 'card', 'PAY-NOUR-001', now()->subWeeks(2)],
        ];

        foreach ($payments as [$studentKey, $amount, $method, $reference, $paidOn]) {
            TuitionPayment::query()->updateOrCreate(
                ['reference' => $reference],
                [
                    'student_id' => $students[$studentKey]->id,
                    'parent_id' => $students[$studentKey]->parent_id,
                    'recorded_by' => $secretary->id,
                    'amount' => $amount,
                    'paid_on' => $paidOn->toDateString(),
                    'method' => $method,
                    'notes' => 'Seeded presentation payment.',
                ]
            );
        }
    }

    /**
     * @param  array<string, User>  $teachers
     */
    private function seedEmployeePayments(array $teachers, User $secretary, User $admin): void
    {
        $payments = [
            [$teachers['sofia'], 50000, 50000, 'Presentation demo: April salary fully paid.'],
            [$teachers['karim'], 50000, 20000, 'Presentation demo: partial April salary payment.'],
            [$secretary, 40000, 25000, 'Presentation demo: partial April salary payment.'],
        ];

        foreach ($payments as [$employee, $expectedSalary, $amountPaid, $notes]) {
            EmployeePayment::query()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'recorded_by' => $admin->id,
                    'expected_salary' => $expectedSalary,
                    'amount_paid' => $amountPaid,
                    'notes' => $notes,
                ]
            );
        }
    }

    /**
     * @param  array<string, User>  $students
     */
    private function linkedChildrenCount(array $students, User $parent): int
    {
        return collect($students)
            ->filter(fn (User $student): bool => (int) $student->parent_id === (int) $parent->id)
            ->count();
    }

    /**
     * @param  array<string, CourseClass>  $groups
     * @param  array<string, User>  $students
     */
    private function seedAttendance(array $groups, array $students): void
    {
        $records = [
            ['ielts', 'alex', 92, 'present', 'Strong essay structure and vocabulary control.'],
            ['ielts', 'lina', 84, 'present', 'Good listening accuracy; keep practicing timing.'],
            ['ielts', 'omar', 76, 'late', 'Solid participation after arrival.'],
            ['french', 'alex', 88, 'present', 'Confident speaking and accurate verb forms.'],
            ['french', 'yacine', 81, 'present', 'Good reading comprehension.'],
            ['spanish', 'sara', 74, 'present', 'Great pronunciation progress.'],
            ['german', 'nour', 90, 'present', 'Excellent professional vocabulary.'],
        ];

        foreach ($records as $index => [$groupKey, $studentKey, $grade, $status, $feedback]) {
            $attendanceDate = now()->subDays(12 - $index)->startOfDay();

            AttendanceRecord::query()->updateOrCreate(
                [
                    'class_id' => $groups[$groupKey]->id,
                    'student_id' => $students[$studentKey]->id,
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'status' => $status,
                    'grade' => $grade,
                    'feedback' => $feedback,
                ]
            );
        }
    }

    /**
     * @param  array<string, CourseClass>  $groups
     * @param  array<string, User>  $teachers
     */
    private function seedResources(array $groups, array $teachers): void
    {
        $resources = [
            ['sofia', 'ielts', TeacherResource::CATEGORY_COURSE_MATERIALS, 'IELTS Writing Band 7 Checklist', 'ielts-writing-checklist.pdf'],
            ['sofia', 'english', TeacherResource::CATEGORY_HOMEWORK, 'A2 Speaking Homework Week 1', 'a2-speaking-homework.pdf'],
            ['karim', 'french', TeacherResource::CATEGORY_COURSE_MATERIALS, 'French B1 Verb Map', 'french-b1-verb-map.pdf'],
            ['karim', 'spanish', TeacherResource::CATEGORY_HOMEWORK, 'Spanish A1 Daily Phrases', 'spanish-a1-phrases.pdf'],
            ['nadia', 'german', TeacherResource::CATEGORY_COURSE_MATERIALS, 'German B1 Workplace Vocabulary', 'german-b1-workplace.pdf'],
        ];

        foreach ($resources as [$teacherKey, $groupKey, $category, $name, $filename]) {
            $path = 'teacher-resources/demo/'.$filename;
            Storage::disk('public')->put($path, "Demo resource: {$name}\nPrepared for Lumina School presentation.\n");

            TeacherResource::query()->updateOrCreate(
                ['file_path' => $path],
                [
                    'teacher_id' => $teachers[$teacherKey]->id,
                    'class_id' => $groups[$groupKey]->id,
                    'category' => $category,
                    'name' => $name,
                    'description' => 'Presentation-ready classroom resource.',
                    'original_filename' => $filename,
                    'mime_type' => 'application/pdf',
                    'file_size' => strlen(Storage::disk('public')->get($path)),
                    'download_count' => 2,
                ]
            );
        }
    }

    /**
     * @param  array<string, User>  $students
     * @param  array<string, User>  $parents
     * @param  array<string, User>  $teachers
     */
    private function seedMessages(array $students, array $parents, array $teachers, User $secretary, User $admin): void
    {
        $messages = [
            [$teachers['sofia'], $students['alex'], 'IELTS essay feedback', 'Alex, your task response improved this week. Please review the checklist before Wednesday.'],
            [$students['alex'], $teachers['sofia'], 'Re: IELTS essay feedback', 'Thank you teacher, I will prepare a revised essay before class.'],
            [$teachers['karim'], $parents['maya'], 'Family progress update', 'Lina and Yacine are both active in class and completing homework consistently.'],
            [$secretary, $parents['amine'], 'Payment receipt ready', 'Your latest payment receipt is available from the financial page.'],
            [$admin, $secretary, 'Presentation data check', 'The demo dataset is ready for the final walkthrough.'],
        ];

        foreach ($messages as [$sender, $receiver, $subject, $body]) {
            Message::query()->updateOrCreate(
                [
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'subject' => $subject,
                ],
                [
                    'body' => $body,
                    'read_at' => null,
                    'created_at' => now()->subDays(rand(1, 7)),
                    'updated_at' => now()->subDays(rand(0, 2)),
                ]
            );
        }
    }

    /**
     * @param  array<string, User>  $teachers
     * @param  array<string, User>  $parents
     * @param  array<string, User>  $students
     * @param  array<string, CourseClass>  $groups
     */
    private function seedNotifications(User $admin, User $secretary, array $teachers, array $parents, array $students, array $groups): void
    {
        $users = [$admin, $secretary, ...array_values($teachers), ...array_values($parents), ...array_values($students)];

        $this->deleteDemoNotifications($users);

        foreach ($users as $index => $user) {
            $this->notification($user, [
                'type' => 'secretary_announcement',
                'title' => 'Welcome to the Lumina demo',
                'message' => 'Your dashboard has realistic sample data for the final presentation.',
                'url' => route('role.dashboard', ['role' => $user->roles->first()?->name ?? 'student']),
                'issuer_name' => 'Sarah Secretary',
            ], $index % 3 === 0 ? now()->subDay() : null);
        }

        $this->seedEmployeePaymentNotifications($teachers, $secretary);
        $this->seedTeacherAssignmentNotifications($teachers, $groups, $secretary);
        $this->seedStudentGroupEnrollmentNotifications($admin, $secretary, $students, $parents, $groups);
        $this->seedResourceUploadNotifications($students, $parents, $groups);
    }

    /**
     * @param  array<string, User>  $teachers
     */
    private function seedEmployeePaymentNotifications(array $teachers, User $secretary): void
    {
        $this->notification($teachers['sofia'], [
            'type' => 'employee_payment_recorded',
            'title' => 'Salary fully paid',
            'message' => 'You received a payment of 50,000 DZD. Your salary for this period is now fully paid.',
            'url' => route('teacher.my-payments'),
            'action' => 'recorded',
            'paid_amount' => 50000,
            'remaining_amount' => 0,
            'full_salary' => 50000,
            'status' => 'paid',
        ], null, EmployeePaymentRecordedNotification::class);

        $this->notification($teachers['karim'], [
            'type' => 'employee_payment_recorded',
            'title' => 'Payment recorded',
            'message' => 'You received a payment of 20,000 DZD. Remaining salary: 30,000 DZD.',
            'url' => route('teacher.my-payments'),
            'action' => 'recorded',
            'paid_amount' => 20000,
            'remaining_amount' => 30000,
            'full_salary' => 50000,
            'status' => 'partial',
        ], now()->subHours(6), EmployeePaymentRecordedNotification::class);

        $this->notification($secretary, [
            'type' => 'employee_payment_recorded',
            'title' => 'Payment recorded',
            'message' => 'You received a payment of 25,000 DZD. Remaining salary: 15,000 DZD.',
            'url' => route('secretary.my-payments'),
            'action' => 'recorded',
            'paid_amount' => 25000,
            'remaining_amount' => 15000,
            'full_salary' => 40000,
            'status' => 'partial',
        ], null, EmployeePaymentRecordedNotification::class);
    }

    /**
     * @param  array<string, User>  $teachers
     * @param  array<string, CourseClass>  $groups
     */
    private function seedTeacherAssignmentNotifications(array $teachers, array $groups, User $secretary): void
    {
        $this->notification($teachers['sofia'], [
            'type' => 'teacher_group_assigned',
            'title' => 'New group assignment',
            'message' => 'You have been assigned to teach IELTS Preparation.',
            'url' => route('timetable.teacher'),
            'action' => 'assigned',
            'group_id' => $groups['ielts']->id,
            'group_name' => 'Group #'.$groups['ielts']->id,
            'course_name' => 'IELTS Preparation',
            'issuer_id' => $secretary->id,
            'issuer_name' => $secretary->name,
        ], now()->subHours(5), TeacherGroupAssignedNotification::class);

        $this->notification($teachers['karim'], [
            'type' => 'teacher_group_assigned',
            'title' => 'New group assignment',
            'message' => 'You have been assigned to teach French B1 Intensive.',
            'url' => route('timetable.teacher'),
            'action' => 'assigned',
            'group_id' => $groups['french']->id,
            'group_name' => 'Group #'.$groups['french']->id,
            'course_name' => 'French B1 Intensive',
            'issuer_id' => $secretary->id,
            'issuer_name' => $secretary->name,
        ], null, TeacherGroupAssignedNotification::class);
    }

    /**
     * @param  array<string, User>  $students
     * @param  array<string, User>  $parents
     * @param  array<string, CourseClass>  $groups
     */
    private function seedStudentGroupEnrollmentNotifications(User $admin, User $secretary, array $students, array $parents, array $groups): void
    {
        $this->studentGroupEnrollmentNotification(
            recipient: $students['lina'],
            actor: $secretary,
            group: $groups['english'],
            action: 'enrolled',
            recipientType: 'student',
            readAt: null,
            url: route('student.academic'),
        );

        $this->studentGroupEnrollmentNotification(
            recipient: $parents['maya'],
            actor: $secretary,
            group: $groups['english'],
            action: 'enrolled',
            recipientType: 'parent',
            child: $students['lina'],
            readAt: null,
            url: route('parent.child.academic', ['child' => $students['lina']->id]),
        );

        $this->studentGroupEnrollmentNotification(
            recipient: $students['sara'],
            actor: $admin,
            group: $groups['spanish'],
            action: 'removed',
            recipientType: 'student',
            readAt: now()->subHours(2),
            url: route('student.academic'),
        );

        $this->studentGroupEnrollmentNotification(
            recipient: $parents['amine'],
            actor: $admin,
            group: $groups['spanish'],
            action: 'removed',
            recipientType: 'parent',
            child: $students['sara'],
            readAt: now()->subHours(2),
            url: route('parent.child.academic', ['child' => $students['sara']->id]),
        );
    }

    private function studentGroupEnrollmentNotification(
        User $recipient,
        User $actor,
        CourseClass $group,
        string $action,
        string $recipientType,
        mixed $readAt,
        ?string $url,
        ?User $child = null,
    ): void {
        $group->loadMissing(['course.program']);
        $actorRole = $actor->roles->first()?->name;
        $actorLabel = trim($actor->name.($actorRole ? " ({$actorRole})" : ''));
        $isEnrolled = $action === 'enrolled';
        $title = $isEnrolled ? 'Enrolled in group' : 'Removed from group';
        $groupName = 'Group #'.$group->id;
        $courseName = (string) ($group->course?->name ?? 'the selected course');
        $programName = $group->course?->program?->name;
        $programSuffix = $programName ? " in {$programName}" : '';
        $message = $recipientType === 'parent'
            ? sprintf(
                'Your child %s was %s %s for %s%s by %s.',
                $child?->name ?? 'your child',
                $isEnrolled ? 'enrolled in' : 'removed from',
                $groupName,
                $courseName,
                $programSuffix,
                $actorLabel,
            )
            : sprintf(
                'You were %s %s for %s%s by %s.',
                $isEnrolled ? 'enrolled in' : 'removed from',
                $groupName,
                $courseName,
                $programSuffix,
                $actorLabel,
            );

        $this->notification($recipient, [
            'type' => 'student_group_enrollment_changed',
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'action' => $action,
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'actor_role' => $actorRole,
            'issuer_id' => $actor->id,
            'issuer_name' => $actor->name,
            'related_model' => CourseClass::class,
            'related_model_id' => $group->id,
            'created_at' => now()->toIso8601String(),
            'group_id' => $group->id,
            'group_name' => $groupName,
            'course_name' => $courseName,
            'program_name' => $programName,
            'recipient_type' => $recipientType,
            'child_id' => $child?->id,
            'child_name' => $child?->name,
        ], $readAt, StudentGroupEnrollmentChangedNotification::class);
    }

    /**
     * @param  array<string, User>  $students
     * @param  array<string, User>  $parents
     * @param  array<string, CourseClass>  $groups
     */
    private function seedResourceUploadNotifications(array $students, array $parents, array $groups): void
    {
        $englishHomework = TeacherResource::query()
            ->where('class_id', $groups['english']->id)
            ->where('category', TeacherResource::CATEGORY_HOMEWORK)
            ->first();
        $ieltsResource = TeacherResource::query()
            ->where('class_id', $groups['ielts']->id)
            ->where('category', TeacherResource::CATEGORY_COURSE_MATERIALS)
            ->first();

        if ($englishHomework instanceof TeacherResource) {
            foreach (['alex', 'lina', 'yacine'] as $studentKey) {
                $this->studentResourceNotification(
                    student: $students[$studentKey],
                    resource: $englishHomework,
                    group: $groups['english'],
                    type: 'homework_uploaded',
                    title: 'New homework uploaded',
                    message: 'Your teacher uploaded homework for English A2 Conversation.',
                    readAt: $studentKey === 'alex' ? null : now()->subHours(4),
                );
            }

            $this->parentResourceNotification(
                parent: $parents['maya'],
                child: $students['lina'],
                resource: $englishHomework,
                group: $groups['english'],
                type: 'homework_uploaded',
                title: 'New homework for your child',
                message: 'A homework was uploaded for your child Lina Benali in English A2 Conversation.',
                readAt: null,
            );

            $this->parentResourceNotification(
                parent: $parents['maya'],
                child: $students['yacine'],
                resource: $englishHomework,
                group: $groups['english'],
                type: 'homework_uploaded',
                title: 'New homework for your child',
                message: 'A homework was uploaded for your child Yacine Benali in English A2 Conversation.',
                readAt: now()->subHours(3),
            );
        }

        if (! $ieltsResource instanceof TeacherResource) {
            return;
        }

        foreach (['alex', 'lina', 'omar', 'sara', 'nour'] as $studentKey) {
            $this->studentResourceNotification(
                student: $students[$studentKey],
                resource: $ieltsResource,
                group: $groups['ielts'],
                type: 'class_resource_uploaded',
                title: 'New course resource uploaded',
                message: 'A new course resource was uploaded for IELTS Preparation.',
                readAt: $studentKey === 'lina' ? null : now()->subHours(2),
            );
        }

        $this->parentResourceNotification(
            parent: $parents['maya'],
            child: $students['lina'],
            resource: $ieltsResource,
            group: $groups['ielts'],
            type: 'class_resource_uploaded',
            title: 'New course resource for your child',
            message: 'A new course resource was uploaded for your child Lina Benali in IELTS Preparation.',
            readAt: now()->subHour(),
        );

        $this->parentResourceNotification(
            parent: $parents['amine'],
            child: $students['sara'],
            resource: $ieltsResource,
            group: $groups['ielts'],
            type: 'class_resource_uploaded',
            title: 'New course resource for your child',
            message: 'A new course resource was uploaded for your child Sara Haddad in IELTS Preparation.',
            readAt: null,
        );
    }

    private function studentResourceNotification(
        User $student,
        TeacherResource $resource,
        CourseClass $group,
        string $type,
        string $title,
        string $message,
        mixed $readAt,
    ): void {
        $this->notification($student, [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => route('student.materials'),
            'action' => 'uploaded',
            'resource_id' => $resource->id,
            'class_id' => $group->id,
            'recipient_type' => 'student',
            'course_name' => $group->course?->name,
            'group_name' => 'Group #'.$group->id,
            'category' => $resource->category,
            'deadline' => $resource->deadline?->format('Y-m-d'),
        ], $readAt, ClassResourceUploadedNotification::class);
    }

    private function parentResourceNotification(
        User $parent,
        User $child,
        TeacherResource $resource,
        CourseClass $group,
        string $type,
        string $title,
        string $message,
        mixed $readAt,
    ): void {
        if ((int) $child->parent_id !== (int) $parent->id) {
            return;
        }

        $this->notification($parent, [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => route('parent.child.materials', ['child' => $child->id]),
            'action' => 'uploaded',
            'resource_id' => $resource->id,
            'class_id' => $group->id,
            'recipient_type' => 'parent',
            'course_name' => $group->course?->name,
            'group_name' => 'Group #'.$group->id,
            'category' => $resource->category,
            'deadline' => $resource->deadline?->format('Y-m-d'),
            'child_id' => $child->id,
            'child_name' => $child->name,
        ], $readAt, ClassResourceUploadedNotification::class);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function notification(User $user, array $data, mixed $readAt = null, string $notificationClass = SecretaryAnnouncementNotification::class): void
    {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $notificationClass,
            'data' => $data,
            'read_at' => $readAt,
        ]);
    }

    /**
     * @param  list<User>  $users
     */
    private function deleteDemoNotifications(array $users): void
    {
        $demoNotificationClasses = [
            SecretaryAnnouncementNotification::class,
            EmployeePaymentRecordedNotification::class,
            TeacherGroupAssignedNotification::class,
            StudentGroupEnrollmentChangedNotification::class,
            ClassResourceUploadedNotification::class,
        ];

        foreach ($users as $user) {
            $user->notifications()
                ->whereIn('type', $demoNotificationClasses)
                ->get()
                ->each(function ($notification): void {
                    $data = (array) $notification->data;

                    if (in_array($data['type'] ?? null, [
                        'secretary_announcement',
                        'employee_payment_recorded',
                        'teacher_group_assigned',
                        'student_group_enrollment_changed',
                        'homework_uploaded',
                        'class_resource_uploaded',
                    ], true)) {
                        $notification->delete();
                    }
                });
        }
    }

    /**
     * @param  array<string, User>  $students
     */
    private function seedReviews(array $students): void
    {
        $reviews = [
            ['alex', 'IELTS Preparation', 'The IELTS course helped me organize writing tasks and manage exam timing.', 4.8],
            ['lina', 'English A2 Conversation', 'Classes are friendly and I feel more confident speaking every week.', 4.7],
            ['nour', 'German B1 Professional', 'The vocabulary lessons are practical for interviews and workplace situations.', 4.9],
        ];

        foreach ($reviews as [$studentKey, $group, $text, $rating]) {
            $student = $students[$studentKey];

            Review::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'student_group' => $group,
                ],
                [
                    'student_name' => $student->name,
                    'review_text' => $text,
                    'rating_score' => $rating,
                    'likes_count' => 12,
                    'dislikes_count' => 1,
                    'uploaded_at' => now()->subDays(4),
                ]
            );
        }
    }

    private function seedPendingApproval(Course $requestedCourse): void
    {
        $path = 'registration-documents/demo/amina-birth-certificate.txt';
        Storage::disk('public')->put($path, "Demo birth certificate placeholder for pending approval.\n");

        $pending = User::query()->updateOrCreate(
            ['email' => 'pending.student@lumina.test'],
            [
                'name' => 'Amina Pending',
                'email_verified_at' => now(),
                'password' => $this->password,
                'requested_role' => 'student',
                'date_of_birth' => '2011-05-22',
                'parent_id' => null,
                'requested_course_id' => $requestedCourse->id,
                'approved_at' => null,
                'approved_by' => null,
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
                'registration_document_type' => 'birth_certificate',
                'registration_document_original_filename' => 'amina-birth-certificate.txt',
                'registration_document_path' => $path,
                'registration_document_mime_type' => 'text/plain',
                'registration_document_size' => strlen(Storage::disk('public')->get($path)),
            ]
        );

        $pending->syncRoles([]);
    }
}
