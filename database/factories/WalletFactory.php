<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
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
            'name' => fake()->randomElement(['BCA Utama', 'Cash Dompet', 'GoPay', 'Binance USDT']),
            'type' => fake()->randomElement(['bank', 'cash', 'e_wallet', 'investment_cash']),
            'currency' => 'IDR',
            'balance' => 0,
            'account_number' => fake()->optional()->numerify('##########'),
            'color' => '#3B82F6',
            'icon' => 'wallet',
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
