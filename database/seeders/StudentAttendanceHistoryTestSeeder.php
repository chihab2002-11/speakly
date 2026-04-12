<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;

class StudentAttendanceHistoryTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            RoomSeeder::class,
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

        $rooms = [
            'A101' => Room::query()->where('name', 'A101')->firstOrFail(),
            'B203' => Room::query()->where('name', 'B203')->firstOrFail(),
            'Lab 2' => Room::query()->where('name', 'Lab 2')->firstOrFail(),
        ];

        $english = Course::query()->updateOrCreate(
            ['code' => 'ENG-B2-TT'],
            ['name' => 'English B2', 'description' => 'Attendance history test course.']
        );
        $spanish = Course::query()->updateOrCreate(
            ['code' => 'SPA-A2-TT'],
            ['name' => 'Spanish A2', 'description' => 'Attendance history test course.']
        );
        $french = Course::query()->updateOrCreate(
            ['code' => 'FRE-B1-TT'],
            ['name' => 'French B1', 'description' => 'Attendance history test course.']
        );

        $englishClass = $this->resolveClassForStudent($student, $english, $teacher);
        $spanishClass = $this->resolveClassForStudent($student, $spanish, $teacher);
        $frenchClass = $this->resolveClassForStudent($student, $french, $teacher);

        $this->upsertSchedule($englishClass, 'sunday', '11:00:00', '12:30:00', $rooms['A101']);
        $this->upsertSchedule($spanishClass, 'tuesday', '14:00:00', '15:30:00', $rooms['B203']);
        $this->upsertSchedule($frenchClass, 'thursday', '09:30:00', '11:00:00', $rooms['Lab 2']);

        $englishClass->students()->syncWithoutDetaching([
            $student->id => ['enrolled_at' => now()->subWeeks(6)],
        ]);
        $spanishClass->students()->syncWithoutDetaching([
            $student->id => ['enrolled_at' => now()->subWeeks(6)],
        ]);
        $frenchClass->students()->syncWithoutDetaching([
            $student->id => ['enrolled_at' => now()->subWeeks(6)],
        ]);

        // Seed 3 weeks of teacher-marked attendance so week selector grows dynamically.
        $currentWeekStart = now()->startOfWeek();
        $weekStarts = [
            $currentWeekStart->copy()->subWeeks(2),
            $currentWeekStart->copy()->subWeeks(1),
            $currentWeekStart->copy(),
        ];

        foreach ($weekStarts as $index => $weekStart) {
            $this->upsertAttendance(
                classId: $englishClass->id,
                studentId: $student->id,
                date: $weekStart->copy()->next(Carbon::SUNDAY),
                status: $index === 1 ? 'late' : 'present',
                grade: $index === 1 ? 78 : 88,
                feedback: $index === 1 ? 'Late arrival. Participate from start next class.' : 'Good participation.'
            );

            $this->upsertAttendance(
                classId: $spanishClass->id,
                studentId: $student->id,
                date: $weekStart->copy()->next(Carbon::TUESDAY),
                status: $index === 2 ? 'absent' : 'present',
                grade: $index === 2 ? null : 84,
                feedback: $index === 2 ? 'Absent.' : 'Solid effort in listening tasks.'
            );

            $this->upsertAttendance(
                classId: $frenchClass->id,
                studentId: $student->id,
                date: $weekStart->copy()->next(Carbon::THURSDAY),
                status: 'present',
                grade: 90,
                feedback: 'Excellent progress on grammar drills.'
            );
        }

        if ($this->command !== null) {
            $this->command->info('Student attendance history test data seeded for salahkedja1@gmail.com');
            $this->command->line('Password: password');
            $this->command->line('Seeded weeks: 3');
            $this->command->line('Teacher: Nadia Hassan');
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

    private function resolveClassForStudent(User $student, Course $course, User $fallbackTeacher): CourseClass
    {
        $existingEnrolledClass = $student->enrolledClasses()
            ->where('classes.course_id', $course->id)
            ->first();

        if ($existingEnrolledClass instanceof CourseClass) {
            return $existingEnrolledClass;
        }

        return CourseClass::query()->updateOrCreate(
            ['course_id' => $course->id, 'teacher_id' => $fallbackTeacher->id],
            ['capacity' => 25]
        );
    }

    private function upsertAttendance(int $classId, int $studentId, CarbonInterface $date, string $status, ?int $grade, ?string $feedback): void
    {
        AttendanceRecord::query()->updateOrCreate(
            [
                'class_id' => $classId,
                'student_id' => $studentId,
                'attendance_date' => $date->toDateString(),
            ],
            [
                'status' => $status,
                'grade' => $grade,
                'feedback' => $feedback,
            ]
        );
    }
}
