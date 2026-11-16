<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Debt;
use App\Models\InvestmentAsset;
use App\Models\InvestmentTransaction;
use App\Models\SystemExchangeRate;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceAndControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_calculates_net_worth_expense_distribution_and_cashflow_trend(): void
    {
        $user = User::factory()->create(['name' => 'Agra Pratama']);

        SystemExchangeRate::create(['pair' => 'USD/IDR', 'rate' => 16000]);
        SystemExchangeRate::create(['pair' => 'USDT/IDR', 'rate' => 16000]);

        // Cash: 5,000,000 IDR + 100 USD (1,600,000 IDR) = 6,600,000 IDR
        $idrWallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 5000000,
            'is_default' => true,
            'is_active' => true,
        ]);
        Wallet::create([
            'user_id' => $user->id,
            'name' => 'Wise USD',
            'type' => 'bank',
            'currency' => 'USD',
            'balance' => 100,
            'is_active' => true,
        ]);

        // Investments: 100 BBCA @ 10,000 = 1,000,000 IDR
        $bbca = InvestmentAsset::create([
            'user_id' => $user->id,
            'symbol' => 'BBCA.JK',
            'name' => 'Bank Central Asia',
            'asset_type' => 'idx_stock',
            'currency' => 'IDR',
            'current_price' => 10000,
        ]);
        InvestmentTransaction::create([
            'user_id' => $user->id,
            'investment_asset_id' => $bbca->id,
            'type' => 'buy',
            'lots' => 1,
            'quantity' => 100,
            'price_per_unit' => 9500,
            'fee' => 0,
            'total_cost' => 950000,
            'transaction_date' => now()->toDateString(),
        ]);

        // Receivable +400,000, Payable -150,000 => +250,000
        Debt::create([
            'user_id' => $user->id,
            'person_name' => 'Jamal',
            'type' => 'receivable',
            'currency' => 'IDR',
            'total_amount' => 400000,
            'issue_date' => now()->toDateString(),
            'status' => 'unpaid',
        ]);
        Debt::create([
            'user_id' => $user->id,
            'person_name' => 'Budi',
            'type' => 'payable',
            'currency' => 'IDR',
            'total_amount' => 150000,
            'issue_date' => now()->toDateString(),
            'status' => 'unpaid',
        ]);

        // Current month income & expense
        $food = Category::create(['user_id' => $user->id, 'name' => 'Makanan', 'type' => 'expense', 'color' => '#F43F5E']);
        $salary = Category::create(['user_id' => $user->id, 'name' => 'Gaji', 'type' => 'income', 'color' => '#10B981']);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $idrWallet->id,
            'category_id' => $salary->id,
            'type' => 'income',
            'amount' => 8000000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Gaji Bulanan',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $idrWallet->id,
            'category_id' => $food->id,
            'type' => 'expense',
            'amount' => 300000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Makan Malam',
        ]);

        $data = app(DashboardService::class)->getDashboardData($user);

        // Net Worth = 6,600,000 + 1,000,000 + 400,000 - 150,000 = 7,850,000
        $this->assertEquals(7850000.0, $data['net_worth_idr']);
        $this->assertEquals(8000000.0, $data['month_income']);
        $this->assertEquals(300000.0, $data['month_expense']);
        $this->assertEquals(['Makanan'], $data['expense_distribution']['labels']);
        $this->assertCount(6, $data['cashflow_trend']['labels']);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Rp 7.850.000');
        $response->assertSee('expenseDonutChart');
        $response->assertSee('cashflowTrendChart');
    }
}
