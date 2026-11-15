<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
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
            'wallet_id' => Wallet::factory(),
            'destination_wallet_id' => null,
            'category_id' => Category::factory(),
            'type' => 'expense',
            'amount' => 50000,
            'destination_amount' => null,
            'exchange_rate' => null,
            'fee' => 0,
            'transaction_date' => now()->toDateString(),
            'description' => fake()->sentence(4),
            'attachment_path' => null,
        ];
    }
}
