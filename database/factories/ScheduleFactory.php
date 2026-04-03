<?php

namespace Database\Factories;

use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->randomElement([8, 9, 10, 11, 13, 14, 15, 16]);
        $startTime = sprintf('%02d:00', $startHour);
        $endTime = sprintf('%02d:30', $startHour + 1);

        return [
            'class_id' => CourseClass::factory(),
            'day_of_week' => fake()->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'room_id' => Room::factory(),
        ];
    }
}
