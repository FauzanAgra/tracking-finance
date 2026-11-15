<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_supports_income_expense_and_fx_transfer_relations(): void
    {
        $user = User::factory()->create();
        $idrWallet = Wallet::factory()->create(['user_id' => $user->id, 'currency' => 'IDR']);
        $usdtWallet = Wallet::factory()->create(['user_id' => $user->id, 'currency' => 'USDT']);
        $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);

        $expense = Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $idrWallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 75000,
            'transaction_date' => '2026-11-15',
            'description' => 'Makan siang',
        ]);

        $fxTransfer = Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $idrWallet->id,
            'destination_wallet_id' => $usdtWallet->id,
            'type' => 'currency_exchange',
            'amount' => 500000,
            'destination_amount' => 31.64,
            'exchange_rate' => 15802.781289,
            'fee' => 2500,
            'transaction_date' => '2026-11-15',
            'description' => 'Top up Binance USDT',
        ]);

        $this->assertTrue($expense->user->is($user));
        $this->assertTrue($expense->wallet->is($idrWallet));
        $this->assertTrue($expense->category->is($category));
        $this->assertSame('0.00', $expense->fresh()->fee);

        $this->assertTrue($fxTransfer->destinationWallet->is($usdtWallet));
        $this->assertNull($fxTransfer->category);
        $this->assertSame('15802.781289', $fxTransfer->exchange_rate);
        $this->assertCount(2, $user->transactions);
    }
}
