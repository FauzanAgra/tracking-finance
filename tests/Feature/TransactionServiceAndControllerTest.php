<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TransactionService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceAndControllerTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionService = app(TransactionService::class);
    }

    public function test_transaction_service_records_and_reverses_income_expense_and_transfer(): void
    {
        $user = User::factory()->create();

        $bca = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $gopay = Wallet::create([
            'user_id' => $user->id,
            'name' => 'GoPay',
            'type' => 'e_wallet',
            'currency' => 'IDR',
            'balance' => 100000,
            'is_default' => false,
            'is_active' => true,
        ]);

        $incomeCat = Category::create([
            'user_id' => $user->id,
            'name' => 'Bonus',
            'type' => 'income',
        ]);

        $expenseCat = Category::create([
            'user_id' => $user->id,
            'name' => 'Makan',
            'type' => 'expense',
        ]);

        // Income
        $incomeTxn = $this->transactionService->recordIncome($user, [
            'wallet_id' => $bca->id,
            'category_id' => $incomeCat->id,
            'amount' => 500000,
            'transaction_date' => '2026-11-15',
            'description' => 'Bonus Proyek',
        ]);
        $this->assertEquals('1500000.0000', $bca->fresh()->balance);

        // Expense
        $expenseTxn = $this->transactionService->recordExpense($user, [
            'wallet_id' => $bca->id,
            'category_id' => $expenseCat->id,
            'amount' => 200000,
            'transaction_date' => '2026-11-15',
            'description' => 'Makan malam',
        ]);
        $this->assertEquals('1300000.0000', $bca->fresh()->balance);

        // Transfer with fee
        $transferTxn = $this->transactionService->recordTransfer($user, [
            'wallet_id' => $bca->id,
            'destination_wallet_id' => $gopay->id,
            'amount' => 300000,
            'fee' => 2500,
            'transaction_date' => '2026-11-15',
            'description' => 'Topup GoPay',
        ]);
        $this->assertEquals('997500.0000', $bca->fresh()->balance);
        $this->assertEquals('400000.0000', $gopay->fresh()->balance);

        // Reverse transfer
        $this->transactionService->deleteTransaction($transferTxn);
        $this->assertEquals('1300000.0000', $bca->fresh()->balance);
        $this->assertEquals('100000.0000', $gopay->fresh()->balance);

        // Reverse expense & income
        $this->transactionService->deleteTransaction($expenseTxn);
        $this->assertEquals('1500000.0000', $bca->fresh()->balance);

        $this->transactionService->deleteTransaction($incomeTxn);
        $this->assertEquals('1000000.0000', $bca->fresh()->balance);
    }

    public function test_insufficient_balance_throws_domain_exception_without_mutating_balance(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Dompet Tunai',
            'type' => 'cash',
            'currency' => 'IDR',
            'balance' => 50000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Belanja',
            'type' => 'expense',
        ]);

        $this->expectException(DomainException::class);

        try {
            $this->transactionService->recordExpense($user, [
                'wallet_id' => $wallet->id,
                'category_id' => $category->id,
                'amount' => 100000,
                'transaction_date' => '2026-11-15',
            ]);
        } finally {
            $this->assertEquals('50000.0000', $wallet->fresh()->balance);
            $this->assertDatabaseCount('transactions', 0);
        }
    }

    public function test_controller_can_store_filter_and_delete_transactions_with_authorization(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $expenseCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Makanan',
            'type' => 'expense',
            'icon' => 'restaurant',
            'color' => '#F43F5E',
        ]);

        $this->actingAs($user)
            ->post(route('transactions.store'), [
                'type' => 'expense',
                'wallet_id' => $wallet->id,
                'category_id' => $expenseCategory->id,
                'amount' => 45000,
                'transaction_date' => '2026-11-15',
                'description' => 'Kopi Susu Aren',
            ])
            ->assertRedirect(route('transactions.index'));

        $this->assertEquals('955000.0000', $wallet->fresh()->balance);

        $response = $this->actingAs($user)->get(route('transactions.index', [
            'search' => 'Kopi',
            'type' => 'expense',
        ]));

        $response->assertOk();
        $response->assertSee('Kopi Susu Aren');
        $response->assertDontSee('modalMode: "create"', false);

        $transaction = Transaction::where('user_id', $user->id)->firstOrFail();

        // Another user cannot delete
        $this->actingAs($otherUser)
            ->delete(route('transactions.destroy', $transaction))
            ->assertForbidden();

        // Owner deletes and balance is restored
        $this->actingAs($user)
            ->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.index'));

        $this->assertEquals('1000000.0000', $wallet->fresh()->balance);
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }
}
