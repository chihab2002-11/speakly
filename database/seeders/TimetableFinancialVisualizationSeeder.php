<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\TuitionPayment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class TimetableFinancialVisualizationSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureRoles();
        $this->call(RoomSeeder::class);

        $admin = $this->upsertApprovedUser('demo.admin@speakly.com', 'Demo Admin', 'admin');
        $secretary = $this->upsertApprovedUser('demo.secretary@speakly.com', 'Demo Secretary', 'secretary', $admin);

        $teacherA = $this->upsertApprovedUser('demo.teacher.aya@speakly.com', 'Aya Benali', 'teacher', $admin);
        $teacherB = $this->upsertApprovedUser('demo.teacher.samir@speakly.com', 'Samir Boulahcen', 'teacher', $admin);
        $teacherC = $this->upsertApprovedUser('demo.teacher.leila@speakly.com', 'Leila Nouiri', 'teacher', $admin);

        $parentA = $this->upsertApprovedUser('demo.parent.yasmine@speakly.com', 'Yasmine Farouk', 'parent', $admin);
        $parentB = $this->upsertApprovedUser('demo.parent.karim@speakly.com', 'Karim Ouali', 'parent', $admin);

        $studentA = $this->upsertApprovedStudent('demo.student.amine@speakly.com', 'Amine Farouk', '2008-04-11', $parentA, $admin);
        $studentB = $this->upsertApprovedStudent('demo.student.meriem@speakly.com', 'Meriem Farouk', '2009-01-20', $parentA, $admin);
        $studentC = $this->upsertApprovedStudent('demo.student.ilyes@speakly.com', 'Ilyes Ouali', '2008-09-02', $parentB, $admin);
        $studentD = $this->upsertApprovedStudent('demo.student.chaima@speakly.com', 'Chaima Ouali', '2010-07-30', $parentB, $admin);

        $courses = [
            $this->upsertCourse('ENG-B1-VIS', 'English B1', 18000, 'Intermediate English communication.'),
            $this->upsertCourse('FRE-A2-VIS', 'French A2', 14000, 'Beginner to pre-intermediate French.'),
            $this->upsertCourse('IELTS-VIS', 'IELTS Preparation', 26000, 'Preparation for IELTS exam sections.'),
        ];

        $groups = [
            $this->upsertGroup($courses[0], $teacherA, 22),
            $this->upsertGroup($courses[0], $teacherB, 20),
            $this->upsertGroup($courses[0], null, 18),
            $this->upsertGroup($courses[1], $teacherC, 24),
            $this->upsertGroup($courses[1], $teacherA, 20),
            $this->upsertGroup($courses[1], null, 18),
            $this->upsertGroup($courses[2], $teacherB, 16),
            $this->upsertGroup($courses[2], $teacherC, 16),
            $this->upsertGroup($courses[2], null, 14),
        ];

        $rooms = [
            Room::query()->where('name', 'A101')->firstOrFail(),
            Room::query()->where('name', 'B203')->firstOrFail(),
            Room::query()->where('name', 'Lab 2')->firstOrFail(),
            Room::query()->where('name', 'C301')->firstOrFail(),
        ];

        $this->upsertSchedule($groups[0], 'monday', '09:00:00', '10:30:00', $rooms[0]);
        $this->upsertSchedule($groups[0], 'wednesday', '09:00:00', '10:30:00', $rooms[0]);
        $this->upsertSchedule($groups[3], 'tuesday', '11:00:00', '12:30:00', $rooms[1]);
        $this->upsertSchedule($groups[3], 'thursday', '11:00:00', '12:30:00', $rooms[1]);
        $this->upsertSchedule($groups[6], 'sunday', '14:00:00', '15:30:00', $rooms[2]);
        $this->upsertSchedule($groups[6], 'tuesday', '14:00:00', '15:30:00', $rooms[2]);
        $this->upsertSchedule($groups[1], 'monday', '11:00:00', '12:30:00', $rooms[3]);
        $this->upsertSchedule($groups[4], 'wednesday', '14:00:00', '15:30:00', $rooms[3]);

        $this->enroll($groups[0], $studentA);
        $this->enroll($groups[3], $studentA);
        $this->enroll($groups[6], $studentA);

        $this->enroll($groups[0], $studentB);
        $this->enroll($groups[4], $studentB);

        $this->enroll($groups[1], $studentC);
        $this->enroll($groups[6], $studentC);

        $this->enroll($groups[3], $studentD);

        if (Schema::hasTable('tuition_payments')) {
            $this->upsertPayment('PAY-VIS-0001', $studentA, $parentA, $secretary, 18000, 'bank_transfer', now()->subDays(20)->toDateString());
            $this->upsertPayment('PAY-VIS-0002', $studentA, $parentA, $secretary, 22000, 'card', now()->subDays(8)->toDateString());
            $this->upsertPayment('PAY-VIS-0003', $studentB, $parentA, $secretary, 12000, 'cash', now()->subDays(5)->toDateString());
            $this->upsertPayment('PAY-VIS-0004', $studentC, $parentB, $secretary, 5000, 'online', now()->subDays(2)->toDateString());
        }

        $this->upsertPendingUser('demo.pending.student@speakly.com', 'Pending Student Demo', 'student');
        $this->upsertPendingUser('demo.pending.teacher@speakly.com', 'Pending Teacher Demo', 'teacher');
        $this->upsertPendingUser('demo.pending.parent@speakly.com', 'Pending Parent Demo', 'parent');

        if ($this->command !== null) {
            $this->command->info('Timetable + financial visualization data seeded.');
            $this->command->line('Demo login accounts (password: password):');
            $this->command->line(' - demo.admin@speakly.com');
            $this->command->line(' - demo.secretary@speakly.com');
            $this->command->line(' - demo.parent.yasmine@speakly.com');
            $this->command->line(' - demo.parent.karim@speakly.com');
            $this->command->line(' - demo.student.amine@speakly.com');
            $this->command->line(' - demo.student.meriem@speakly.com');
            $this->command->line(' - demo.student.ilyes@speakly.com');
            $this->command->line(' - demo.student.chaima@speakly.com');

            if (! Schema::hasTable('tuition_payments')) {
                $this->command->warn('tuition_payments table does not exist yet; payment records were skipped.');
            }

            if (! Schema::hasColumn('courses', 'price')) {
                $this->command->warn('courses.price column does not exist yet; prices were skipped.');
            }
        }
    }

    private function ensureRoles(): void
    {
        foreach (['admin', 'secretary', 'teacher', 'parent', 'student'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function upsertApprovedUser(string $email, string $name, string $role, ?User $approver = null): User
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $user->forceFill([
            'name' => $name,
            'requested_role' => $role === 'admin' ? null : $role,
            'approved_at' => now(),
            'approved_by' => $approver?->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();

        $user->syncRoles([$role]);

        return $user;
    }

    private function upsertApprovedStudent(string $email, string $name, string $dob, User $parent, User $approver): User
    {
        $student = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $student->forceFill([
            'name' => $name,
            'requested_role' => 'student',
            'date_of_birth' => $dob,
            'parent_id' => $parent->id,
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();

        $student->syncRoles(['student']);

        return $student;
    }

    private function upsertCourse(string $code, string $name, int $price, string $description): Course
    {
        $attributes = [
            'name' => $name,
            'description' => $description,
        ];

        if (Schema::hasColumn('courses', 'price')) {
            $attributes['price'] = $price;
        }

        return Course::query()->updateOrCreate(
            ['code' => $code],
            $attributes
        );
    }

    private function upsertGroup(Course $course, ?User $teacher, int $capacity): CourseClass
    {
        return CourseClass::query()->firstOrCreate(
            [
                'course_id' => $course->id,
                'teacher_id' => $teacher?->id,
            ],
            [
                'capacity' => $capacity,
            ]
        );
    }

    private function upsertSchedule(CourseClass $group, string $day, string $start, string $end, Room $room): void
    {
        Schedule::query()->updateOrCreate(
            [
                'class_id' => $group->id,
                'day_of_week' => $day,
                'start_time' => $start,
            ],
            [
                'end_time' => $end,
                'room_id' => $room->id,
            ]
        );
    }

    private function enroll(CourseClass $group, User $student): void
    {
        $group->students()->syncWithoutDetaching([
            $student->id => ['enrolled_at' => now()->subWeeks(3)],
        ]);
    }

    private function upsertPayment(string $reference, User $student, User $parent, User $recorder, int $amount, string $method, string $paidOn): void
    {
        TuitionPayment::query()->updateOrCreate(
            ['reference' => $reference],
            [
                'student_id' => $student->id,
                'parent_id' => $parent->id,
                'recorded_by' => $recorder->id,
                'amount' => $amount,
                'paid_on' => $paidOn,
                'method' => $method,
                'notes' => 'Visualization payment seed',
            ]
        );
    }

    private function upsertPendingUser(string $email, string $name, string $requestedRole): void
    {
        $pending = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $pending->forceFill([
            'name' => $name,
            'requested_role' => $requestedRole,
            'approved_at' => null,
            'approved_by' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();

        $pending->syncRoles([]);
    }
}
