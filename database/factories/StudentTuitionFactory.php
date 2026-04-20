<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\StudentTuition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentTuition>
 */
class StudentTuitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'course_id' => Course::factory(),
            'course_price' => fake()->numberBetween(8000, 45000),
        ];
    }
}
