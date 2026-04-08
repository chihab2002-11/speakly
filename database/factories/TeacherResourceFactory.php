<?php

namespace Database\Factories;

use App\Models\CourseClass;
use App\Models\TeacherResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherResource>
 */
class TeacherResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $teacher = User::factory();

        return [
            'teacher_id' => $teacher,
            'class_id' => CourseClass::factory()->state(fn (array $attributes) => [
                'teacher_id' => $attributes['teacher_id'],
            ]),
            'category' => fake()->randomElement(TeacherResource::allowedCategories()),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'original_filename' => fake()->word().'.pdf',
            'file_path' => 'teacher-resources/'.fake()->numberBetween(1, 99).'/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 512000),
            'download_count' => fake()->numberBetween(0, 50),
        ];
    }
}
