<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4BCurrencyAndBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_currency_exchange_transfer_records_rate_updates_both_wallets_and_reverses_on_delete(): void
    {
        $user = User::factory()->create();

        $idrWallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA IDR',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 20000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $usdtWallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Binance USDT',
            'type' => 'investment_cash',
            'currency' => 'USDT',
            'balance' => 100,
            'is_default' => false,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('transactions.store'), [
                'type' => 'currency_exchange',
                'wallet_id' => $idrWallet->id,
                'destination_wallet_id' => $usdtWallet->id,
                'amount' => 16000000,
                'destination_amount' => 1000,
                'fee' => 25000,
                'transaction_date' => '2026-11-16',
                'description' => 'Konversi ke USDT',
            ])
            ->assertRedirect(route('transactions.index'));

        $this->assertEquals('3975000.0000', $idrWallet->fresh()->balance);
        $this->assertEquals('1100.0000', $usdtWallet->fresh()->balance);

        $txn = Transaction::where('user_id', $user->id)->where('type', 'currency_exchange')->firstOrFail();
        $this->assertEquals('16000.000000', $txn->exchange_rate);

        // Delete reverses both wallets atomically
        $this->actingAs($user)
            ->delete(route('transactions.destroy', $txn))
            ->assertRedirect(route('transactions.index'));

        $this->assertEquals('20000000.0000', $idrWallet->fresh()->balance);
        $this->assertEquals('100.0000', $usdtWallet->fresh()->balance);
    }

    public function test_budget_service_and_controller_compute_safe_warning_and_over_health_statuses(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 10000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $food = Category::create(['user_id' => $user->id, 'name' => 'Makanan', 'type' => 'expense']);
        $transport = Category::create(['user_id' => $user->id, 'name' => 'Transport', 'type' => 'expense']);
        $shopping = Category::create(['user_id' => $user->id, 'name' => 'Belanja', 'type' => 'expense']);

        // Create/update budget via controller
        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $food->id,
            'amount' => 1000000,
            'month_year' => '2026-11',
        ])->assertRedirect(route('budgets.index', ['month' => '2026-11']));

        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $transport->id,
            'amount' => 1000000,
            'month_year' => '2026-11',
        ]);

        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $shopping->id,
            'amount' => 1000000,
            'month_year' => '2026-11',
        ]);

        // Spent: Food 500k (50% -> safe), Transport 850k (85% -> warning), Shopping 1.2M (120% -> over)
        Transaction::create(['user_id' => $user->id, 'wallet_id' => $wallet->id, 'category_id' => $food->id, 'type' => 'expense', 'amount' => 500000, 'transaction_date' => '2026-11-10']);
        Transaction::create(['user_id' => $user->id, 'wallet_id' => $wallet->id, 'category_id' => $transport->id, 'type' => 'expense', 'amount' => 850000, 'transaction_date' => '2026-11-12']);
        Transaction::create(['user_id' => $user->id, 'wallet_id' => $wallet->id, 'category_id' => $shopping->id, 'type' => 'expense', 'amount' => 1200000, 'transaction_date' => '2026-11-15']);

        $overview = app(BudgetService::class)->getMonthlyOverview($user, '2026-11');
        $byCategory = $overview['items']->keyBy(fn (array $item) => $item['budget']->category_id);

        $this->assertEquals('safe', $byCategory[$food->id]['health']);
        $this->assertEquals('warning', $byCategory[$transport->id]['health']);
        $this->assertEquals('over', $byCategory[$shopping->id]['health']);

        $response = $this->actingAs($user)->get(route('budgets.index', ['month' => '2026-11']));
        $response->assertOk();
        $response->assertSee('Aman 50%');
        $response->assertSee('Waspada 85%');
        $response->assertSee('Over 120%');

        $budget = Budget::where('user_id', $user->id)->firstOrFail();

        $this->actingAs($otherUser)
            ->delete(route('budgets.destroy', $budget))
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('budgets.destroy', $budget))
            ->assertRedirect(route('budgets.index', ['month' => '2026-11']));

        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }
}
