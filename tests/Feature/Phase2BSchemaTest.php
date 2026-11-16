<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\InvestmentAsset;
use App\Models\InvestmentTransaction;
use App\Models\SystemExchangeRate;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase2BSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_2b_tables_and_models_work_according_to_erd(): void
    {
        $this->assertTrue(Schema::hasTable('budgets'));
        $this->assertTrue(Schema::hasTable('system_exchange_rates'));
        $this->assertTrue(Schema::hasTable('goals'));
        $this->assertTrue(Schema::hasTable('goal_contributions'));
        $this->assertTrue(Schema::hasTable('investment_assets'));
        $this->assertTrue(Schema::hasTable('investment_transactions'));

        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 10000000,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Makanan',
            'type' => 'expense',
        ]);

        $budget = Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 1500000,
            'month_year' => '2026-11',
        ]);
        $this->assertEquals('1500000.00', $budget->amount);
        $this->assertEquals($category->id, $budget->category->id);

        $rate = SystemExchangeRate::create([
            'pair' => 'USD/IDR',
            'rate' => 16000.5,
        ]);
        $this->assertEquals('16000.500000', $rate->rate);

        $goal = Goal::create([
            'user_id' => $user->id,
            'title' => 'Dana Darurat',
            'target_amount' => 20000000,
        ]);
        $contribution = GoalContribution::create([
            'goal_id' => $goal->id,
            'wallet_id' => $wallet->id,
            'amount' => 1000000,
            'contribution_date' => '2026-11-16',
        ]);
        $this->assertEquals($goal->id, $contribution->goal->id);
        $this->assertCount(1, $goal->contributions);

        $asset = InvestmentAsset::create([
            'user_id' => $user->id,
            'symbol' => 'BBCA.JK',
            'name' => 'Bank Central Asia',
            'asset_type' => 'idx_stock',
            'currency' => 'IDR',
            'current_price' => 10250,
        ]);
        $trade = InvestmentTransaction::create([
            'user_id' => $user->id,
            'investment_asset_id' => $asset->id,
            'wallet_id' => $wallet->id,
            'type' => 'buy',
            'lots' => 2,
            'quantity' => 200,
            'price_per_unit' => 10000,
            'fee' => 3000,
            'total_cost' => 2003000,
            'transaction_date' => '2026-11-16',
        ]);
        $this->assertEquals($asset->id, $trade->asset->id);
        $this->assertCount(1, $user->investmentAssets);
        $this->assertCount(1, $user->budgets);
        $this->assertCount(1, $user->goals);
    }
}
