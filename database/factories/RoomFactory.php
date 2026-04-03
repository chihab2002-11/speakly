<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->bothify('Room ###'),
            'capacity' => fake()->randomElement([20, 25, 30, 35, 40, 50]),
            'location' => fake()->secondaryAddress(),
        ];
    }
}
