<?php

namespace Database\Factories;

use App\Models\Debt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'person_name' => fake()->name(),
            'phone_number' => fake()->optional()->phoneNumber(),
            'type' => 'receivable',
            'currency' => 'IDR',
            'total_amount' => 500000,
            'written_off_amount' => 0,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => 'unpaid',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
