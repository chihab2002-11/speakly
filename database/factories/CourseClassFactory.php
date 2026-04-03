<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseClass>
 */
class CourseClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'teacher_id' => User::factory(),
            'room' => fake()->bothify('Room ###'),
            'capacity' => fake()->randomElement([25, 30, 35, 40, 50]),
        ];
    }
}
