<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Message;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\TeacherResource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TeacherWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = $this->resolveAdmin();
        $users = $this->seedUsers($admin);
        $courses = $this->seedCourses();
        $rooms = $this->seedRooms();
        $classes = $this->seedClasses($courses, $users);

        $this->seedEnrollments($classes, $users);
        $this->seedSchedules($classes, $rooms);
        $this->seedTeacherResources($classes, $users);
        $this->seedMessages($users);

        if ($this->command !== null) {
            $this->command->info('Teacher workflow development data seeded.');
            $this->command->line('Login accounts (password: password):');
            $this->command->line(' - admin@speakly.com');
            $this->command->line(' - secretary@speakly.com');
            $this->command->line(' - teacher.nadia@speakly.com');
            $this->command->line(' - teacher.omer@speakly.com');
            $this->command->line(' - parent.layla@speakly.com');
            $this->command->line(' - parent.karim@speakly.com');
            $this->command->line(' - student.amir@speakly.com');
            $this->command->line(' - student.sara@speakly.com');
            $this->command->line(' - student.zayn@speakly.com');
        }
    }

    private function resolveAdmin(): User
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@speakly.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $admin->forceFill([
            'requested_role' => null,
            'approved_at' => now(),
            'approved_by' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();

        $admin->syncRoles(['admin']);

        return $admin;
    }

    /**
     * @return array{
     *   admin:User,
     *   secretary:User,
     *   teacher_primary:User,
     *   teacher_secondary:User,
     *   parent_layla:User,
     *   parent_karim:User,
     *   student_amir:User,
     *   student_sara:User,
     *   student_zayn:User,
     *   pending_teacher:User
     * }
     */
    private function seedUsers(User $admin): array
    {
        $secretary = $this->upsertApprovedUser(
            email: 'secretary@speakly.com',
            attributes: [
                'name' => 'Sarah Secretary',
            ],
            role: 'secretary',
            approver: $admin,
        );

        $teacherPrimary = $this->upsertApprovedUser(
            email: 'teacher.nadia@speakly.com',
            attributes: [
                'name' => 'Nadia Hassan',
            ],
            role: 'teacher',
            approver: $admin,
        );

        $teacherSecondary = $this->upsertApprovedUser(
            email: 'teacher.omer@speakly.com',
            attributes: [
                'name' => 'Omer Khaled',
            ],
            role: 'teacher',
            approver: $admin,
        );

        $parentLayla = $this->upsertApprovedUser(
            email: 'parent.layla@speakly.com',
            attributes: [
                'name' => 'Layla Rahman',
            ],
            role: 'parent',
            approver: $admin,
        );

        $parentKarim = $this->upsertApprovedUser(
            email: 'parent.karim@speakly.com',
            attributes: [
                'name' => 'Karim Mansour',
            ],
            role: 'parent',
            approver: $admin,
        );

        $studentAmir = $this->upsertApprovedUser(
            email: 'student.amir@speakly.com',
            attributes: [
                'name' => 'Amir Rahman',
                'date_of_birth' => '2008-03-14',
                'parent_id' => $parentLayla->id,
            ],
            role: 'student',
            approver: $admin,
        );

        $studentSara = $this->upsertApprovedUser(
            email: 'student.sara@speakly.com',
            attributes: [
                'name' => 'Sara Rahman',
                'date_of_birth' => '2009-09-03',
                'parent_id' => $parentLayla->id,
            ],
            role: 'student',
            approver: $admin,
        );

        $studentZayn = $this->upsertApprovedUser(
            email: 'student.zayn@speakly.com',
            attributes: [
                'name' => 'Zayn Mansour',
                'date_of_birth' => '2008-11-21',
                'parent_id' => $parentKarim->id,
            ],
            role: 'student',
            approver: $admin,
        );

        $pendingTeacher = User::query()->firstOrCreate(
            ['email' => 'pending.teacher@speakly.com'],
            [
                'name' => 'Pending Teacher',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $pendingTeacher->forceFill([
            'requested_role' => 'teacher',
            'approved_at' => null,
            'approved_by' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();

        $pendingTeacher->syncRoles([]);

        return [
            'admin' => $admin,
            'secretary' => $secretary,
            'teacher_primary' => $teacherPrimary,
            'teacher_secondary' => $teacherSecondary,
            'parent_layla' => $parentLayla,
            'parent_karim' => $parentKarim,
            'student_amir' => $studentAmir,
            'student_sara' => $studentSara,
            'student_zayn' => $studentZayn,
            'pending_teacher' => $pendingTeacher,
        ];
    }

    /**
     * @param  array{name:string,date_of_birth?:string,parent_id?:int}  $attributes
     */
    private function upsertApprovedUser(string $email, array $attributes, string $role, User $approver): User
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $attributes['name'],
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $user->forceFill([
            'name' => $attributes['name'],
            'requested_role' => $role,
            'date_of_birth' => $attributes['date_of_birth'] ?? null,
            'parent_id' => $attributes['parent_id'] ?? null,
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();

        $user->syncRoles([$role]);

        return $user;
    }

    /**
     * @return array{eng_b2:Course,eng_a2:Course,ielts:Course}
     */
    private function seedCourses(): array
    {
        $engB2 = Course::query()->updateOrCreate(
            ['code' => 'ENG-B2'],
            [
                'name' => 'English B2 - Grammar',
                'price' => 22000,
                'description' => 'Upper-intermediate grammar and writing skills.',
            ]
        );

        $engA2 = Course::query()->updateOrCreate(
            ['code' => 'ENG-A2'],
            [
                'name' => 'English A2 - Conversation',
                'price' => 16000,
                'description' => 'Beginner conversation and vocabulary practice.',
            ]
        );

        $ielts = Course::query()->updateOrCreate(
            ['code' => 'IELTS-PREP'],
            [
                'name' => 'IELTS Preparation',
                'price' => 26000,
                'description' => 'Comprehensive preparation for IELTS reading, writing, and speaking.',
            ]
        );

        return [
            'eng_b2' => $engB2,
            'eng_a2' => $engA2,
            'ielts' => $ielts,
        ];
    }

    /**
     * @return array{a101:Room,b203:Room,lab2:Room}
     */
    private function seedRooms(): array
    {
        $this->call(RoomSeeder::class);

        return [
            'a101' => Room::query()->where('name', 'A101')->firstOrFail(),
            'b203' => Room::query()->where('name', 'B203')->firstOrFail(),
            'lab2' => Room::query()->where('name', 'Lab 2')->firstOrFail(),
        ];
    }

    /**
     * @param  array{eng_b2:Course,eng_a2:Course,ielts:Course}  $courses
     * @param  array{
     *   teacher_primary:User,
     *   teacher_secondary:User
     * }  $users
     * @return array{grammar:CourseClass,conversation:CourseClass,ielts:CourseClass}
     */
    private function seedClasses(array $courses, array $users): array
    {
        $grammar = $this->upsertClass($courses['eng_b2'], $users['teacher_primary'], 24);
        $conversation = $this->upsertClass($courses['eng_a2'], $users['teacher_primary'], 20);
        $ielts = $this->upsertClass($courses['ielts'], $users['teacher_secondary'], 18);

        return [
            'grammar' => $grammar,
            'conversation' => $conversation,
            'ielts' => $ielts,
        ];
    }

    private function upsertClass(Course $course, User $teacher, int $capacity): CourseClass
    {
        return CourseClass::query()->updateOrCreate(
            [
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
            ],
            [
                'capacity' => $capacity,
            ]
        );
    }

    /**
     * @param  array{grammar:CourseClass,conversation:CourseClass,ielts:CourseClass}  $classes
     * @param  array{student_amir:User,student_sara:User,student_zayn:User}  $users
     */
    private function seedEnrollments(array $classes, array $users): void
    {
        $this->attachStudent($classes['grammar'], $users['student_amir'], now()->subWeeks(7));
        $this->attachStudent($classes['grammar'], $users['student_sara'], now()->subWeeks(6));

        $this->attachStudent($classes['conversation'], $users['student_sara'], now()->subWeeks(7));
        $this->attachStudent($classes['conversation'], $users['student_zayn'], now()->subWeeks(6));

        $this->attachStudent($classes['ielts'], $users['student_amir'], now()->subWeeks(5));
        $this->attachStudent($classes['ielts'], $users['student_zayn'], now()->subWeeks(5));
    }

    private function attachStudent(CourseClass $courseClass, User $student, \DateTimeInterface $enrolledAt): void
    {
        $alreadyEnrolled = $courseClass->students()
            ->where('users.id', $student->id)
            ->exists();

        if (! $alreadyEnrolled) {
            $courseClass->students()->attach($student->id, [
                'enrolled_at' => $enrolledAt,
            ]);
        }
    }

    /**
     * @param  array{grammar:CourseClass,conversation:CourseClass,ielts:CourseClass}  $classes
     * @param  array{a101:Room,b203:Room,lab2:Room}  $rooms
     */
    private function seedSchedules(array $classes, array $rooms): void
    {
        $this->upsertSchedule($classes['grammar'], 'monday', '09:00:00', '10:30:00', $rooms['a101']);
        $this->upsertSchedule($classes['grammar'], 'wednesday', '09:00:00', '10:30:00', $rooms['a101']);

        $this->upsertSchedule($classes['conversation'], 'tuesday', '11:00:00', '12:30:00', $rooms['b203']);
        $this->upsertSchedule($classes['conversation'], 'thursday', '11:00:00', '12:30:00', $rooms['b203']);

        $this->upsertSchedule($classes['ielts'], 'sunday', '14:00:00', '15:30:00', $rooms['lab2']);
        $this->upsertSchedule($classes['ielts'], 'tuesday', '14:00:00', '15:30:00', $rooms['lab2']);
    }

    private function upsertSchedule(CourseClass $courseClass, string $day, string $startTime, string $endTime, Room $room): void
    {
        Schedule::query()->updateOrCreate(
            [
                'class_id' => $courseClass->id,
                'day_of_week' => $day,
                'start_time' => $startTime,
            ],
            [
                'end_time' => $endTime,
                'room_id' => $room->id,
            ]
        );
    }

    /**
     * @param  array{grammar:CourseClass,conversation:CourseClass,ielts:CourseClass}  $classes
     * @param  array{teacher_primary:User,teacher_secondary:User}  $users
     */
    private function seedTeacherResources(array $classes, array $users): void
    {
        $definitions = [
            [
                'teacher' => $users['teacher_primary'],
                'class' => $classes['grammar'],
                'category' => TeacherResource::CATEGORY_HOMEWORK,
                'name' => 'B2 Grammar Worksheet - Week 1',
                'description' => 'Practice sheet covering conditionals and modal verbs.',
                'filename' => 'b2-grammar-week-1.pdf',
                'content' => 'B2 grammar worksheet content',
                'downloads' => 19,
            ],
            [
                'teacher' => $users['teacher_primary'],
                'class' => $classes['conversation'],
                'category' => TeacherResource::CATEGORY_COURSE_MATERIALS,
                'name' => 'A2 Conversation Prompts',
                'description' => 'Printable prompts for pair speaking activities.',
                'filename' => 'a2-conversation-prompts.docx',
                'content' => 'A2 conversation prompts content',
                'downloads' => 12,
            ],
            [
                'teacher' => $users['teacher_secondary'],
                'class' => $classes['ielts'],
                'category' => TeacherResource::CATEGORY_COURSE_MATERIALS,
                'name' => 'IELTS Writing Band Descriptors',
                'description' => 'Band score descriptors used in writing assessment.',
                'filename' => 'ielts-writing-band-descriptors.pdf',
                'content' => 'IELTS writing descriptors content',
                'downloads' => 27,
            ],
        ];

        foreach ($definitions as $definition) {
            $filePath = 'teacher-resources/'.$definition['teacher']->id.'/'.$definition['filename'];

            Storage::disk('public')->put($filePath, $definition['content']);

            TeacherResource::query()->updateOrCreate(
                [
                    'teacher_id' => $definition['teacher']->id,
                    'class_id' => $definition['class']->id,
                    'name' => $definition['name'],
                ],
                [
                    'category' => $definition['category'],
                    'description' => $definition['description'],
                    'original_filename' => $definition['filename'],
                    'file_path' => $filePath,
                    'mime_type' => $this->mimeTypeForFilename($definition['filename']),
                    'file_size' => strlen($definition['content']),
                    'download_count' => $definition['downloads'],
                ]
            );
        }
    }

    private function mimeTypeForFilename(string $filename): string
    {
        $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'zip' => 'application/zip',
            default => 'application/octet-stream',
        };
    }

    /**
     * @param  array{admin:User,teacher_primary:User,parent_layla:User,student_amir:User}  $users
     */
    private function seedMessages(array $users): void
    {
        $this->upsertMessage(
            sender: $users['student_amir'],
            receiver: $users['teacher_primary'],
            subject: 'Homework Clarification',
            body: 'Could you explain question 4 from the B2 worksheet?',
            readAt: null,
        );

        $this->upsertMessage(
            sender: $users['parent_layla'],
            receiver: $users['teacher_primary'],
            subject: 'Attendance Follow-up',
            body: 'Amir will miss class on Thursday for a medical appointment.',
            readAt: null,
        );

        $this->upsertMessage(
            sender: $users['teacher_primary'],
            receiver: $users['student_amir'],
            subject: 'Re: Homework Clarification',
            body: 'Absolutely. We will review question 4 at the start of class tomorrow.',
            readAt: now()->subDay(),
        );

        $this->upsertMessage(
            sender: $users['admin'],
            receiver: $users['teacher_primary'],
            subject: 'Weekly Coordination',
            body: 'Please share this week\'s classroom resource summary by 4 PM.',
            readAt: null,
        );
    }

    private function upsertMessage(User $sender, User $receiver, ?string $subject, string $body, ?\DateTimeInterface $readAt): void
    {
        Message::query()->updateOrCreate(
            [
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'subject' => $subject,
                'body' => $body,
            ],
            [
                'read_at' => $readAt,
            ]
        );
    }
}
