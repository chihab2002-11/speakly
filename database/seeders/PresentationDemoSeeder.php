<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\CourseClass;
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
            'yacine' => $this->createStudent('Yacine Benali', 'student.yacine@lumina.test', '2008-02-05', $parents['maya'], $courses['french'], $admin),
            'omar' => $this->createStudent('Omar Haddad', 'student.omar@lumina.test', '2002-11-28', $parents['amine'], $courses['french'], $admin),
            'sara' => $this->createStudent('Sara Haddad', 'student.sara@lumina.test', '2009-07-09', $parents['amine'], $courses['spanish'], $admin),
            'nour' => $this->createStudent('Nour Bensaid', 'student.nour@lumina.test', '2001-01-21', null, $courses['german'], $admin),
        ];

        $this->seedEnrollments($groups, $students);
        $this->seedFinancials($students, $parents, $secretary);
        $this->seedAttendance($groups, $students);
        $this->seedResources($groups, $teachers);
        $this->seedMessages($students, $parents, $teachers, $secretary, $admin);
        $this->seedNotifications([$admin, $secretary, ...array_values($teachers), ...array_values($parents), ...array_values($students)]);
        $this->seedReviews($students);
        $this->seedPendingApproval($courses['english']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(string $name, string $email, string $role, array $attributes = [], ?User $approver = null): User
    {
        $user = User::query()->create(array_merge([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => $this->password,
            'requested_role' => $role,
            'approved_at' => now()->subDays(45),
            'approved_by' => $approver?->id,
        ], $attributes));

        $user->syncRoles([$role]);

        return $user;
    }

    private function createStudent(string $name, string $email, string $birthDate, ?User $parent, Course $requestedCourse, User $approver): User
    {
        $student = $this->createUser($name, $email, 'student', [
            'date_of_birth' => $birthDate,
            'parent_id' => $parent?->id,
            'requested_course_id' => $requestedCourse->id,
        ], $approver);

        StudentCard::query()->create([
            'user_id' => $student->id,
            'card_number' => 'LUM-2026-'.str_pad((string) $student->id, 4, '0', STR_PAD_LEFT),
            'valid_from' => now()->subDays(45)->toDateString(),
            'valid_to' => now()->addMonths(4)->toDateString(),
            'academic_year' => now()->year.'/'.(now()->year + 1),
            'status' => 'active',
        ]);

        StudentTuition::query()->create([
            'student_id' => $student->id,
            'course_id' => $requestedCourse->id,
            'course_price' => (int) $requestedCourse->price,
        ]);

        return $student;
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
            ->map(fn (array $program): LanguageProgram => LanguageProgram::query()->create(array_merge($program, [
                'is_active' => true,
            ])))
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
            ->map(fn (array $course): Course => Course::query()->create([
                'name' => $course[0],
                'code' => $course[1],
                'price' => $course[2],
                'description' => $course[3],
                'program_id' => $course[4]->id,
            ]))
            ->all();
    }

    /**
     * @return array<string, Room>
     */
    private function seedRooms(): array
    {
        return [
            'orchid' => Room::query()->create(['name' => 'Room Orchid', 'capacity' => 24, 'location' => 'First floor']),
            'cedar' => Room::query()->create(['name' => 'Room Cedar', 'capacity' => 30, 'location' => 'Second floor']),
            'atlas' => Room::query()->create(['name' => 'Room Atlas', 'capacity' => 18, 'location' => 'Ground floor']),
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
            'ielts' => CourseClass::query()->create(['course_id' => $courses['ielts']->id, 'teacher_id' => $teachers['sofia']->id, 'capacity' => 30]),
            'english' => CourseClass::query()->create(['course_id' => $courses['english']->id, 'teacher_id' => $teachers['sofia']->id, 'capacity' => 24]),
            'french' => CourseClass::query()->create(['course_id' => $courses['french']->id, 'teacher_id' => $teachers['karim']->id, 'capacity' => 24]),
            'spanish' => CourseClass::query()->create(['course_id' => $courses['spanish']->id, 'teacher_id' => $teachers['karim']->id, 'capacity' => 18]),
            'german' => CourseClass::query()->create(['course_id' => $courses['german']->id, 'teacher_id' => $teachers['nadia']->id, 'capacity' => 18]),
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
            Schedule::query()->create([
                'class_id' => $groups[$groupKey]->id,
                'day_of_week' => $day,
                'start_time' => $start,
                'end_time' => $end,
                'room_id' => $rooms[$roomKey]->id,
            ]);
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
        ScholarshipActivation::query()->create([
            'parent_id' => $parents['maya']->id,
            'student_id' => null,
            'offer_key' => 'family_3_children',
            'discount_percent' => 12,
            'activated_at' => now()->subWeeks(2),
            'meta' => ['reason' => 'Three linked children enrolled for the presentation demo.'],
        ]);

        ScholarshipActivation::query()->create([
            'parent_id' => $parents['maya']->id,
            'student_id' => $students['alex']->id,
            'offer_key' => 'multi_course_4_plus',
            'discount_percent' => 10,
            'activated_at' => now()->subWeek(),
            'meta' => ['reason' => 'Alex is enrolled in four active course groups.'],
        ]);

        $payments = [
            ['alex', $parents['maya'], 42000, 'cash', 'PAY-ALEX-001', now()->subWeeks(5)],
            ['alex', $parents['maya'], 18000, 'bank_transfer', 'PAY-ALEX-002', now()->subWeeks(2)],
            ['lina', $parents['maya'], 12000, 'card', 'PAY-LINA-001', now()->subWeeks(3)],
            ['yacine', $parents['maya'], 10000, 'cash', 'PAY-YACINE-001', now()->subDays(12)],
            ['omar', $parents['amine'], 16000, 'bank_transfer', 'PAY-OMAR-001', now()->subWeeks(4)],
            ['sara', $parents['amine'], 8000, 'cash', 'PAY-SARA-001', now()->subDays(10)],
            ['nour', null, 28000, 'card', 'PAY-NOUR-001', now()->subWeeks(2)],
        ];

        foreach ($payments as [$studentKey, $parent, $amount, $method, $reference, $paidOn]) {
            TuitionPayment::query()->create([
                'student_id' => $students[$studentKey]->id,
                'parent_id' => $parent?->id,
                'recorded_by' => $secretary->id,
                'amount' => $amount,
                'paid_on' => $paidOn->toDateString(),
                'method' => $method,
                'reference' => $reference,
                'notes' => 'Seeded presentation payment.',
            ]);
        }
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
            AttendanceRecord::query()->create([
                'class_id' => $groups[$groupKey]->id,
                'student_id' => $students[$studentKey]->id,
                'attendance_date' => now()->subDays(12 - $index)->toDateString(),
                'status' => $status,
                'grade' => $grade,
                'feedback' => $feedback,
            ]);
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

            TeacherResource::query()->create([
                'teacher_id' => $teachers[$teacherKey]->id,
                'class_id' => $groups[$groupKey]->id,
                'category' => $category,
                'name' => $name,
                'description' => 'Presentation-ready classroom resource.',
                'original_filename' => $filename,
                'file_path' => $path,
                'mime_type' => 'application/pdf',
                'file_size' => strlen(Storage::disk('public')->get($path)),
                'download_count' => 2,
            ]);
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
            Message::query()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'subject' => $subject,
                'body' => $body,
                'read_at' => null,
                'created_at' => now()->subDays(rand(1, 7)),
                'updated_at' => now()->subDays(rand(0, 2)),
            ]);
        }
    }

    /**
     * @param  list<User>  $users
     */
    private function seedNotifications(array $users): void
    {
        foreach ($users as $index => $user) {
            $this->notification($user, [
                'type' => 'secretary_announcement',
                'title' => 'Welcome to the Lumina demo',
                'message' => 'Your dashboard has realistic sample data for the final presentation.',
                'url' => route('role.dashboard', ['role' => $user->roles->first()?->name ?? 'student']),
                'issuer_name' => 'Sarah Secretary',
            ], $index % 3 === 0 ? now()->subDay() : null);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function notification(User $user, array $data, mixed $readAt = null): void
    {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\SecretaryAnnouncementNotification',
            'data' => $data,
            'read_at' => $readAt,
        ]);
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

            Review::query()->create([
                'student_id' => $student->id,
                'student_name' => $student->name,
                'student_group' => $group,
                'review_text' => $text,
                'rating_score' => $rating,
                'likes_count' => 12,
                'dislikes_count' => 1,
                'uploaded_at' => now()->subDays(4),
            ]);
        }
    }

    private function seedPendingApproval(Course $requestedCourse): void
    {
        $path = 'registration-documents/demo/amina-birth-certificate.txt';
        Storage::disk('public')->put($path, "Demo birth certificate placeholder for pending approval.\n");

        User::query()->create([
            'name' => 'Amina Pending',
            'email' => 'pending.student@lumina.test',
            'email_verified_at' => now(),
            'password' => $this->password,
            'requested_role' => 'student',
            'date_of_birth' => '2011-05-22',
            'requested_course_id' => $requestedCourse->id,
            'registration_document_type' => 'birth_certificate',
            'registration_document_original_filename' => 'amina-birth-certificate.txt',
            'registration_document_path' => $path,
            'registration_document_mime_type' => 'text/plain',
            'registration_document_size' => strlen(Storage::disk('public')->get($path)),
        ]);
    }
}
