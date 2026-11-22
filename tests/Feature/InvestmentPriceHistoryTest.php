<?php

namespace Tests\Feature;

use App\Models\InvestmentAsset;
use App\Models\InvestmentPriceHistory;
use App\Models\User;
use App\Services\InvestmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentPriceHistoryTest extends TestCase
{
    use RefreshDatabase;

    private InvestmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(InvestmentService::class);
    }

    public function test_manual_price_update_creates_price_history_record(): void
    {
        $user = User::factory()->create();

        $asset = InvestmentAsset::create([
            'user_id' => $user->id,
            'symbol' => 'BBCA.JK',
            'name' => 'Bank Central Asia',
            'asset_type' => 'idx_stock',
            'currency' => 'IDR',
            'current_price' => 9500,
            'is_manual_price' => false,
        ]);

        $this->service->updateManualPrice($asset, 10200);

        $this->assertDatabaseHas('investment_price_histories', [
            'investment_asset_id' => $asset->id,
            'price' => 10200,
        ]);

        $this->assertEquals(1, $asset->priceHistories()->count());
    }

    public function test_portfolio_summary_calculates_sparkline_and_view_renders_svg(): void
    {
        $user = User::factory()->create();

        $asset = InvestmentAsset::create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'asset_type' => 'crypto',
            'currency' => 'USDT',
            'current_price' => 95000,
            'is_manual_price' => false,
        ]);

        // Add 3 days of historical prices
        InvestmentPriceHistory::create([
            'investment_asset_id' => $asset->id,
            'price' => 90000,
            'recorded_date' => now()->subDays(2)->toDateString(),
        ]);
        InvestmentPriceHistory::create([
            'investment_asset_id' => $asset->id,
            'price' => 92000,
            'recorded_date' => now()->subDays(1)->toDateString(),
        ]);
        InvestmentPriceHistory::create([
            'investment_asset_id' => $asset->id,
            'price' => 95000,
            'recorded_date' => now()->toDateString(),
        ]);

        $summary = $this->service->getPortfolioSummary($user);
        $btcPos = collect($summary['positions'])->firstWhere('asset.symbol', 'BTC');

        $this->assertNotNull($btcPos);
        $this->assertNotEmpty($btcPos['sparkline_points']);
        $this->assertTrue($btcPos['is_trend_up']);

        // Check view response
        $response = $this->actingAs($user)->get(route('investments.index'));
        $response->assertOk();
        $response->assertSee('polyline');
        $response->assertSee('BTC');
    }
}
