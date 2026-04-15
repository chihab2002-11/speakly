<?php

namespace Database\Factories;

use App\Models\TuitionPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TuitionPayment>
 */
class TuitionPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'parent_id' => null,
            'recorded_by' => null,
            'amount' => fake()->numberBetween(1000, 60000),
            'paid_on' => fake()->dateTimeBetween('-90 days', 'now')->format('Y-m-d'),
            'method' => fake()->randomElement(['cash', 'bank_transfer', 'card', 'online']),
            'reference' => strtoupper(fake()->bothify('PAY-######')),
            'notes' => null,
        ];
    }
}
