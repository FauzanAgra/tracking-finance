<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionEditTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionService = app(TransactionService::class);
    }

    public function test_user_can_update_expense_transaction_and_balance_is_recalculated_atomically(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $catFood = Category::create([
            'user_id' => $user->id,
            'name' => 'Makanan',
            'type' => 'expense',
        ]);

        $catTransport = Category::create([
            'user_id' => $user->id,
            'name' => 'Transport',
            'type' => 'expense',
        ]);

        // Initial expense: 200.000 -> balance becomes 800.000
        $txn = $this->transactionService->recordExpense($user, [
            'wallet_id' => $wallet->id,
            'category_id' => $catFood->id,
            'amount' => 200000,
            'transaction_date' => '2026-11-20',
            'description' => 'Makan Siang',
        ]);

        $this->assertEquals('800000.0000', $wallet->fresh()->balance);

        // Update expense to 350.000 under Transport
        $response = $this->actingAs($user)->put(route('transactions.update', $txn), [
            'type' => 'expense',
            'wallet_id' => $wallet->id,
            'category_id' => $catTransport->id,
            'amount' => 350000,
            'transaction_date' => '2026-11-21',
            'description' => 'Bensin & Tol',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('status', 'Transaksi berhasil diperbarui.');

        // Reversal + New Debit: 800.000 + 200.000 - 350.000 = 650.000
        $this->assertEquals('650000.0000', $wallet->fresh()->balance);

        $freshTxn = $txn->fresh();
        $this->assertEquals('350000.0000', $freshTxn->amount);
        $this->assertEquals($catTransport->id, $freshTxn->category_id);
        $this->assertEquals('Bensin & Tol', $freshTxn->description);
        $this->assertEquals('2026-11-21', $freshTxn->transaction_date->format('Y-m-d'));
    }

    public function test_user_can_change_wallet_during_update_and_both_wallets_rebalance_atomically(): void
    {
        $user = User::factory()->create();

        $walletA = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $walletB = Wallet::create([
            'user_id' => $user->id,
            'name' => 'GoPay',
            'type' => 'e_wallet',
            'currency' => 'IDR',
            'balance' => 500000,
            'is_default' => false,
            'is_active' => true,
        ]);

        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'Kebutuhan',
            'type' => 'expense',
        ]);

        // Expense 100.000 on Wallet A -> A: 900.000, B: 500.000
        $txn = $this->transactionService->recordExpense($user, [
            'wallet_id' => $walletA->id,
            'category_id' => $cat->id,
            'amount' => 100000,
            'transaction_date' => '2026-11-20',
        ]);

        $this->assertEquals('900000.0000', $walletA->fresh()->balance);
        $this->assertEquals('500000.0000', $walletB->fresh()->balance);

        // Move to Wallet B with amount 150.000
        $this->actingAs($user)->put(route('transactions.update', $txn), [
            'type' => 'expense',
            'wallet_id' => $walletB->id,
            'category_id' => $cat->id,
            'amount' => 150000,
            'transaction_date' => '2026-11-20',
        ])->assertRedirect(route('transactions.index'));

        // Wallet A should be refunded 100.000 => 1.000.000
        $this->assertEquals('1000000.0000', $walletA->fresh()->balance);
        // Wallet B should be debited 150.000 => 350.000
        $this->assertEquals('350000.0000', $walletB->fresh()->balance);
    }

    public function test_user_can_update_income_transaction(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'Gaji',
            'type' => 'income',
        ]);

        // Income 500.000 -> 1.500.000
        $txn = $this->transactionService->recordIncome($user, [
            'wallet_id' => $wallet->id,
            'category_id' => $cat->id,
            'amount' => 500000,
            'transaction_date' => '2026-11-20',
        ]);

        $this->assertEquals('1500000.0000', $wallet->fresh()->balance);

        // Update income to 750.000 -> balance becomes 1.750.000
        $this->actingAs($user)->put(route('transactions.update', $txn), [
            'type' => 'income',
            'wallet_id' => $wallet->id,
            'category_id' => $cat->id,
            'amount' => 750000,
            'transaction_date' => '2026-11-20',
        ])->assertRedirect(route('transactions.index'));

        $this->assertEquals('1750000.0000', $wallet->fresh()->balance);
    }

    public function test_user_can_update_transfer_transaction(): void
    {
        $user = User::factory()->create();

        $from = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $to = Wallet::create([
            'user_id' => $user->id,
            'name' => 'GoPay',
            'type' => 'e_wallet',
            'currency' => 'IDR',
            'balance' => 100000,
            'is_default' => false,
            'is_active' => true,
        ]);

        // Transfer 200.000 with fee 2.500 -> from: 797.500, to: 300.000
        $txn = $this->transactionService->recordTransfer($user, [
            'wallet_id' => $from->id,
            'destination_wallet_id' => $to->id,
            'amount' => 200000,
            'fee' => 2500,
            'transaction_date' => '2026-11-20',
        ]);

        $this->assertEquals('797500.0000', $from->fresh()->balance);
        $this->assertEquals('300000.0000', $to->fresh()->balance);

        // Update transfer to 100.000 with fee 0 -> from: 900.000, to: 200.000
        $this->actingAs($user)->put(route('transactions.update', $txn), [
            'type' => 'transfer',
            'wallet_id' => $from->id,
            'destination_wallet_id' => $to->id,
            'amount' => 100000,
            'fee' => 0,
            'transaction_date' => '2026-11-20',
        ])->assertRedirect(route('transactions.index'));

        $this->assertEquals('900000.0000', $from->fresh()->balance);
        $this->assertEquals('200000.0000', $to->fresh()->balance);
    }

    public function test_user_cannot_update_another_users_transaction(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $walletA = Wallet::create([
            'user_id' => $userA->id,
            'name' => 'BCA A',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 500000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $catA = Category::create([
            'user_id' => $userA->id,
            'name' => 'Makan A',
            'type' => 'expense',
        ]);

        $txn = $this->transactionService->recordExpense($userA, [
            'wallet_id' => $walletA->id,
            'category_id' => $catA->id,
            'amount' => 50000,
            'transaction_date' => '2026-11-20',
        ]);

        // User B attempts to update User A's transaction
        $this->actingAs($userB)->put(route('transactions.update', $txn), [
            'type' => 'expense',
            'wallet_id' => $walletA->id,
            'category_id' => $catA->id,
            'amount' => 75000,
            'transaction_date' => '2026-11-20',
        ])->assertForbidden();
    }

    public function test_insufficient_balance_on_update_rolls_back_cleanly(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Dompet',
            'type' => 'cash',
            'currency' => 'IDR',
            'balance' => 100000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'Makan',
            'type' => 'expense',
        ]);

        $txn = $this->transactionService->recordExpense($user, [
            'wallet_id' => $wallet->id,
            'category_id' => $cat->id,
            'amount' => 50000,
            'transaction_date' => '2026-11-20',
        ]);

        // Balance is now 50.000. Try updating expense to 200.000 (total wallet only had 100.000 originally)
        $response = $this->actingAs($user)->from(route('transactions.index'))->put(route('transactions.update', $txn), [
            'type' => 'expense',
            'wallet_id' => $wallet->id,
            'category_id' => $cat->id,
            'amount' => 200000,
            'transaction_date' => '2026-11-20',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('error');

        // Original state must be preserved
        $this->assertEquals('50000.0000', $wallet->fresh()->balance);
        $this->assertEquals('50000.0000', $txn->fresh()->amount);
    }
}
