<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Message;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\TeacherResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ParentDashboardLinkedChildrenSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            RoomSeeder::class,
            LanguageProgramSeeder::class,
        ]);

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

        $parent = User::query()->firstOrCreate(
            ['email' => 'salahkedja1parent@gmail.com'],
            [
                'name' => 'Salah Kedja Parent',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $parent->forceFill([
            'requested_role' => 'parent',
            'approved_at' => now(),
            'approved_by' => $admin->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();
        $parent->syncRoles(['parent']);

        $teacherA = $this->upsertTeacher('teacher.parentdash.a@speakly.com', 'Meryem Chouaki', $admin->id);
        $teacherB = $this->upsertTeacher('teacher.parentdash.b@speakly.com', 'Karim Benyahia', $admin->id);

        $childA = $this->upsertChild(
            email: 'salahkedja1.child.a@gmail.com',
            name: 'Yanis Kedja',
            parentId: $parent->id,
            approvedBy: $admin->id,
            dob: '2010-05-14'
        );

        $childB = $this->upsertChild(
            email: 'salahkedja1.child.b@gmail.com',
            name: 'Lina Kedja',
            parentId: $parent->id,
            approvedBy: $admin->id,
            dob: '2012-09-02'
        );

        $courseEnglish = Course::query()->updateOrCreate(
            ['code' => 'PARENT-ENG-B2'],
            ['name' => 'English B2 Program', 'description' => 'Parent dashboard testing program']
        );

        $courseFrench = Course::query()->updateOrCreate(
            ['code' => 'PARENT-FRE-B1'],
            ['name' => 'French B1 Program', 'description' => 'Parent dashboard testing program']
        );

        $courseSpanish = Course::query()->updateOrCreate(
            ['code' => 'PARENT-SPA-A2'],
            ['name' => 'Spanish A2 Program', 'description' => 'Parent dashboard testing program']
        );

        $classEnglish = CourseClass::query()->updateOrCreate(
            ['course_id' => $courseEnglish->id, 'teacher_id' => $teacherA->id],
            ['capacity' => 24]
        );

        $classFrench = CourseClass::query()->updateOrCreate(
            ['course_id' => $courseFrench->id, 'teacher_id' => $teacherB->id],
            ['capacity' => 24]
        );

        $classSpanish = CourseClass::query()->updateOrCreate(
            ['course_id' => $courseSpanish->id, 'teacher_id' => $teacherA->id],
            ['capacity' => 24]
        );

        $roomA101 = Room::query()->where('name', 'A101')->firstOrFail();
        $roomB203 = Room::query()->where('name', 'B203')->firstOrFail();
        $roomLab2 = Room::query()->where('name', 'Lab 2')->firstOrFail();

        $this->upsertSchedule($classEnglish, 'sunday', '09:30:00', '11:00:00', $roomA101);
        $this->upsertSchedule($classFrench, 'tuesday', '11:00:00', '12:30:00', $roomB203);
        $this->upsertSchedule($classSpanish, 'thursday', '14:00:00', '15:30:00', $roomLab2);

        $classEnglish->students()->syncWithoutDetaching([
            $childA->id => ['enrolled_at' => now()->subWeeks(6)],
            $childB->id => ['enrolled_at' => now()->subWeeks(6)],
        ]);
        $classFrench->students()->syncWithoutDetaching([
            $childA->id => ['enrolled_at' => now()->subWeeks(6)],
        ]);
        $classSpanish->students()->syncWithoutDetaching([
            $childB->id => ['enrolled_at' => now()->subWeeks(6)],
        ]);

        $currentWeekStart = now()->startOfWeek(Carbon::SATURDAY);
        $weekStarts = [
            $currentWeekStart->copy()->subWeeks(3),
            $currentWeekStart->copy()->subWeeks(2),
            $currentWeekStart->copy()->subWeeks(1),
            $currentWeekStart->copy(),
        ];

        foreach ($weekStarts as $index => $weekStart) {
            $this->seedAttendanceForChild($childA, [
                [
                    'class' => $classEnglish,
                    'date' => $weekStart->copy()->next(Carbon::SUNDAY),
                    'status' => $index === 1 ? 'late' : 'present',
                    'grade' => $index === 1 ? 12 : 14 + $index,
                    'feedback' => $index === 1 ? 'Late start this week.' : 'Consistent English progress.',
                ],
                [
                    'class' => $classFrench,
                    'date' => $weekStart->copy()->next(Carbon::TUESDAY),
                    'status' => $index === 2 ? 'absent' : 'present',
                    'grade' => $index === 2 ? null : 11 + $index,
                    'feedback' => $index === 2 ? 'Absent from French session.' : 'French class participation recorded.',
                ],
                [
                    'class' => $classEnglish,
                    'date' => $weekStart->copy()->next(Carbon::THURSDAY),
                    'status' => $index === 3 ? 'late' : 'present',
                    'grade' => 13 + $index,
                    'feedback' => 'Follow-up class evaluation.',
                ],
            ]);

            $this->seedAttendanceForChild($childB, [
                [
                    'class' => $classEnglish,
                    'date' => $weekStart->copy()->next(Carbon::SUNDAY),
                    'status' => 'present',
                    'grade' => 16 + $index,
                    'feedback' => 'Excellent engagement.',
                ],
                [
                    'class' => $classSpanish,
                    'date' => $weekStart->copy()->next(Carbon::TUESDAY),
                    'status' => $index === 0 ? 'late' : 'present',
                    'grade' => 15 + $index,
                    'feedback' => 'Spanish speaking evaluation.',
                ],
                [
                    'class' => $classSpanish,
                    'date' => $weekStart->copy()->next(Carbon::THURSDAY),
                    'status' => $index === 2 ? 'absent' : 'present',
                    'grade' => $index === 2 ? null : 14 + $index,
                    'feedback' => $index === 2 ? 'Absent entry for test scenario.' : 'Steady weekly performance.',
                ],
            ]);
        }

        $this->seedHomeworkResource($teacherA, $classEnglish, 'Homework Pack - English B2');
        $this->seedHomeworkResource($teacherB, $classFrench, 'Homework Pack - French B1');
        $this->seedHomeworkResource($teacherA, $classSpanish, 'Homework Pack - Spanish A2');

        Message::query()->updateOrCreate(
            ['sender_id' => $teacherA->id, 'receiver_id' => $parent->id, 'subject' => 'Parent Dashboard Seed A'],
            ['body' => 'Unread test message from teacher A', 'read_at' => null]
        );

        Message::query()->updateOrCreate(
            ['sender_id' => $teacherB->id, 'receiver_id' => $parent->id, 'subject' => 'Parent Dashboard Seed B'],
            ['body' => 'Unread test message from teacher B', 'read_at' => null]
        );

        if ($this->command !== null) {
            $this->command->info('Parent dashboard linked data seeded.');
            $this->command->line('Parent email: salahkedja1parent@gmail.com');
            $this->command->line('Password: password');
            $this->command->line('Children linked: 2 (with schedules, attendance, grades, homework, teacher messages)');
        }
    }

    private function upsertTeacher(string $email, string $name, int $adminId): User
    {
        $teacher = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $teacher->forceFill([
            'requested_role' => 'teacher',
            'approved_at' => now(),
            'approved_by' => $adminId,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();
        $teacher->syncRoles(['teacher']);

        return $teacher;
    }

    private function upsertChild(string $email, string $name, int $parentId, int $approvedBy, string $dob): User
    {
        $child = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $child->forceFill([
            'name' => $name,
            'parent_id' => $parentId,
            'date_of_birth' => $dob,
            'requested_role' => 'student',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();
        $child->syncRoles(['student']);

        return $child;
    }

    private function upsertSchedule(CourseClass $class, string $day, string $start, string $end, Room $room): void
    {
        Schedule::query()->updateOrCreate(
            [
                'class_id' => $class->id,
                'day_of_week' => $day,
                'start_time' => $start,
            ],
            [
                'end_time' => $end,
                'room_id' => $room->id,
            ]
        );
    }

    /**
     * @param  array<int, array{class:CourseClass,date:Carbon,status:string,grade:int|null,feedback:string}>  $rows
     */
    private function seedAttendanceForChild(User $child, array $rows): void
    {
        foreach ($rows as $row) {
            AttendanceRecord::query()->updateOrCreate(
                [
                    'class_id' => $row['class']->id,
                    'student_id' => $child->id,
                    'attendance_date' => $row['date']->toDateString(),
                ],
                [
                    'status' => $row['status'],
                    'grade' => $row['grade'],
                    'feedback' => $row['feedback'],
                ]
            );
        }
    }

    private function seedHomeworkResource(User $teacher, CourseClass $class, string $title): void
    {
        TeacherResource::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'class_id' => $class->id,
                'name' => $title,
            ],
            [
                'category' => TeacherResource::CATEGORY_HOMEWORK,
                'description' => 'Seeded homework for parent dashboard testing.',
                'original_filename' => str_replace(' ', '_', strtolower($title)).'.pdf',
                'file_path' => 'teacher-resources/seed/'.str_replace(' ', '-', strtolower($title)).'.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 153600,
                'download_count' => 0,
            ]
        );
    }
}
