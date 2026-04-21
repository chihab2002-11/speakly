<?php

namespace Database\Factories;

use App\Models\EmployeePayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeePayment>
 */
class EmployeePaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => User::factory(),
            'recorded_by' => null,
            'expected_salary' => fake()->numberBetween(30000, 120000),
            'amount_paid' => fake()->numberBetween(0, 120000),
            'notes' => null,
        ];
    }
}
