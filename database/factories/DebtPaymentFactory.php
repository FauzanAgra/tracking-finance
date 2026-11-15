<?php

namespace Database\Factories;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DebtPayment>
 */
class DebtPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'debt_id' => Debt::factory(),
            'wallet_id' => Wallet::factory(),
            'amount' => 200000,
            'payment_date' => now()->toDateString(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
