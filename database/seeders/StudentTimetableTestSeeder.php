<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class StudentTimetableTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureRole('admin');
        $this->ensureRole('teacher');
        $this->ensureRole('student');

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

        $teacher = User::query()->firstOrCreate(
            ['email' => 'teacher.nadia@speakly.com'],
            [
                'name' => 'Nadia Hassan',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $teacher->forceFill([
            'requested_role' => 'teacher',
            'approved_at' => now(),
            'approved_by' => $admin->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();
        $teacher->syncRoles(['teacher']);

        $student = User::query()->firstOrCreate(
            ['email' => 'salahkedja1@gmail.com'],
            [
                'name' => 'salah lgoat',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $student->forceFill([
            'name' => 'salah lgoat',
            'requested_role' => 'student',
            'date_of_birth' => '2000-09-09',
            'approved_at' => $student->approved_at ?? now(),
            'approved_by' => $student->approved_by ?? $admin->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();
        $student->syncRoles(['student']);

        $this->call(RoomSeeder::class);

        $rooms = [
            'A101' => Room::query()->where('name', 'A101')->firstOrFail(),
            'B203' => Room::query()->where('name', 'B203')->firstOrFail(),
            'Lab 2' => Room::query()->where('name', 'Lab 2')->firstOrFail(),
        ];

        $courses = [
            Course::query()->updateOrCreate(
                ['code' => 'ENG-B2-TT'],
                ['name' => 'English B2', 'price' => 18000, 'description' => 'Student timetable test course.']
            ),
            Course::query()->updateOrCreate(
                ['code' => 'SPA-A2-TT'],
                ['name' => 'Spanish A2', 'price' => 15000, 'description' => 'Student timetable test course.']
            ),
            Course::query()->updateOrCreate(
                ['code' => 'FRE-B1-TT'],
                ['name' => 'French B1', 'price' => 17000, 'description' => 'Student timetable test course.']
            ),
        ];

        $classOne = CourseClass::query()->updateOrCreate(
            ['course_id' => $courses[0]->id, 'teacher_id' => $teacher->id],
            ['capacity' => 25]
        );

        $classTwo = CourseClass::query()->updateOrCreate(
            ['course_id' => $courses[1]->id, 'teacher_id' => $teacher->id],
            ['capacity' => 25]
        );

        $classThree = CourseClass::query()->updateOrCreate(
            ['course_id' => $courses[2]->id, 'teacher_id' => $teacher->id],
            ['capacity' => 25]
        );

        $this->upsertSchedule($classOne, 'sunday', '11:00:00', '12:30:00', $rooms['A101']);
        $this->upsertSchedule($classTwo, 'tuesday', '14:00:00', '15:30:00', $rooms['B203']);
        $this->upsertSchedule($classThree, 'thursday', '09:30:00', '11:00:00', $rooms['Lab 2']);

        $classOne->students()->syncWithoutDetaching([
            $student->id => ['enrolled_at' => now()->subWeeks(2)],
        ]);
        $classTwo->students()->syncWithoutDetaching([
            $student->id => ['enrolled_at' => now()->subWeeks(2)],
        ]);
        $classThree->students()->syncWithoutDetaching([
            $student->id => ['enrolled_at' => now()->subWeeks(2)],
        ]);

        if ($this->command !== null) {
            $this->command->info('Student timetable test data seeded for salahkedja1@gmail.com');
            $this->command->line('Login password: password');
            $this->command->line('Timetable slots: Sunday 11:00, Tuesday 14:00, Thursday 09:30');
        }
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

    private function ensureRole(string $role): void
    {
        Role::query()->firstOrCreate([
            'name' => $role,
            'guard_name' => 'web',
        ]);
    }
}
