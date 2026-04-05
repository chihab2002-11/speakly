<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user (usually admin) as teacher
        $teacher = User::first();
        if ($teacher) {
            $teacher->syncRoles(['teacher']);
        }

        // Create or get a course
        $course = Course::firstOrCreate(
            ['code' => 'MATH101'],
            [
                'name' => 'Mathematics',
                'description' => 'Introduction to algebra and geometry',
            ]
        );

        // Create or get a room
        $room = Room::firstOrCreate(
            ['name' => 'A101'],
            [
                'capacity' => 30,
                'location' => 'Main Building',
            ]
        );

        // Create or get a class
        $class = CourseClass::firstOrCreate(
            ['course_id' => $course->id],
            [
                'teacher_id' => $teacher?->id,
                'capacity' => 30,
            ]
        );

        // Create schedules
        Schedule::firstOrCreate(
            ['class_id' => $class->id, 'day_of_week' => 'monday', 'room_id' => $room->id],
            [
                'start_time' => '09:00:00',
                'end_time' => '10:30:00',
            ]
        );

        Schedule::firstOrCreate(
            ['class_id' => $class->id, 'day_of_week' => 'wednesday', 'room_id' => $room->id],
            [
                'start_time' => '09:00:00',
                'end_time' => '10:30:00',
            ]
        );

        Schedule::firstOrCreate(
            ['class_id' => $class->id, 'day_of_week' => 'friday', 'room_id' => $room->id],
            [
                'start_time' => '09:00:00',
                'end_time' => '10:30:00',
            ]
        );

        // Create or get a student user
        $student = User::firstOrCreate(
            ['email' => 'student@test.com'],
            [
                'name' => 'John Student',
                'password' => bcrypt('password'),
                'approved_at' => now(),
                'approved_by' => $teacher?->id,
                'requested_role' => 'student',
            ]
        );
        $student->syncRoles(['student']);

        // Enroll student in class (if not already enrolled)
        $class->students()->syncWithoutDetaching([$student->id]);

        $this->command->info('✅ Timetable sample data ready!');
        $this->command->info("Course: {$course->name} ({$course->code})");
        $this->command->info("Room: {$room->name}");
        $this->command->info("Student: {$student->name} (password: password)");
        $this->command->info('Schedules: Mon, Wed, Fri 09:00-10:30');
    }
}
