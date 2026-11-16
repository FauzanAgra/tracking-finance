<?php

namespace Tests\Feature;

use App\Models\InvestmentAsset;
use App\Models\User;
use App\Models\Wallet;
use App\Services\InvestmentService;
use App\Services\MarketPriceService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InvestmentServiceAndControllerTest extends TestCase
{
    use RefreshDatabase;

    private InvestmentService $investmentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->investmentService = app(InvestmentService::class);
    }

    public function test_dca_math_lot_conversion_wallet_mutation_and_partial_sell(): void
    {
        $user = User::factory()->create();

        $rdn = Wallet::create([
            'user_id' => $user->id,
            'name' => 'RDN BCA',
            'type' => 'investment_cash',
            'currency' => 'IDR',
            'balance' => 10000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        // Buy 2 lots (200 shares) of BBCA at 10,000 + fee 3,000 => total 2,003,000
        $this->actingAs($user)
            ->post(route('investments.trades.store'), [
                'asset_type' => 'idx_stock',
                'symbol' => 'BBCA',
                'name' => 'Bank Central Asia',
                'type' => 'buy',
                'lots' => 2,
                'price_per_unit' => 10000,
                'fee' => 3000,
                'wallet_id' => $rdn->id,
                'transaction_date' => '2026-11-10',
            ])
            ->assertRedirect(route('investments.index'));

        $this->assertEquals('7997000.0000', $rdn->fresh()->balance);

        // Average down: Buy 2 more lots (200 shares) at 9,000 + fee 2,700 => total 1,802,700
        $this->investmentService->recordTrade($user, [
            'asset_type' => 'idx_stock',
            'symbol' => 'BBCA.JK',
            'type' => 'buy',
            'lots' => 2,
            'price_per_unit' => 9000,
            'fee' => 2700,
            'wallet_id' => $rdn->id,
            'transaction_date' => '2026-11-12',
        ]);

        $asset = InvestmentAsset::where('user_id', $user->id)->where('symbol', 'BBCA.JK')->firstOrFail();
        $position = $this->investmentService->calculatePosition($asset->load('transactions'));

        // Total qty = 400 shares (4 lots), total cost = 3,805,700 => avg = 9514.25
        $this->assertEquals(400.0, $position['quantity']);
        $this->assertEquals(4.0, $position['lots']);
        $this->assertEqualsWithDelta(9514.25, $position['average_price'], 0.01);

        // Sell 1 lot (100 shares) at 11,000 - fee 2,750 => proceeds 1,097,250
        $this->investmentService->recordTrade($user, [
            'asset_type' => 'idx_stock',
            'symbol' => 'BBCA.JK',
            'type' => 'sell',
            'lots' => 1,
            'price_per_unit' => 11000,
            'fee' => 2750,
            'wallet_id' => $rdn->id,
            'transaction_date' => '2026-11-15',
        ]);

        $positionAfterSell = $this->investmentService->calculatePosition($asset->fresh('transactions'));
        $this->assertEquals(300.0, $positionAfterSell['quantity']);
        $this->assertEqualsWithDelta(9514.25, $positionAfterSell['average_price'], 0.01);
    }

    public function test_market_price_refresh_and_manual_price_override_with_authorization(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $btc = InvestmentAsset::create([
            'user_id' => $owner->id,
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'asset_type' => 'crypto',
            'currency' => 'USDT',
            'current_price' => 90000,
            'is_manual_price' => false,
        ]);

        $manualStock = InvestmentAsset::create([
            'user_id' => $owner->id,
            'symbol' => 'BBCA.JK',
            'name' => 'Bank Central Asia',
            'asset_type' => 'idx_stock',
            'currency' => 'IDR',
            'current_price' => 10500,
            'is_manual_price' => true,
        ]);

        Http::fake([
            'api.coingecko.com/*' => Http::response([
                'bitcoin' => ['usd' => 95000, 'idr' => 1520000000],
            ], 200),
            'query1.finance.yahoo.com/*' => Http::response([
                'chart' => ['result' => [['meta' => ['regularMarketPrice' => 16100]]]],
            ], 200),
        ]);

        $result = app(MarketPriceService::class)->refreshAll($owner);

        $this->assertEquals(1, $result['updated']);
        $this->assertEquals(1, $result['skipped_manual']);
        $this->assertEquals('95000.000000', $btc->fresh()->current_price);
        $this->assertEquals('10500.000000', $manualStock->fresh()->current_price);

        // Manual price override via controller
        $this->actingAs($owner)
            ->patch(route('investments.manual-price', $btc), ['current_price' => 98000])
            ->assertRedirect(route('investments.index'));

        $this->assertTrue((bool) $btc->fresh()->is_manual_price);
        $this->assertEquals('98000.000000', $btc->fresh()->current_price);

        // Authorization check
        $this->actingAs($otherUser)
            ->patch(route('investments.manual-price', $btc), ['current_price' => 1])
            ->assertForbidden();

        // Artisan command works cleanly
        $this->artisan('investments:refresh-prices')->assertSuccessful();

        // Portfolio view renders
        $this->actingAs($owner)
            ->get(route('investments.index'))
            ->assertOk()
            ->assertSee('BTC')
            ->assertSee('BBCA.JK');
    }

    public function test_selling_more_than_owned_throws_domain_exception(): void
    {
        $user = User::factory()->create();

        $this->investmentService->recordTrade($user, [
            'asset_type' => 'us_stock',
            'symbol' => 'AAPL',
            'type' => 'buy',
            'quantity' => 2,
            'price_per_unit' => 200,
            'fee' => 0,
            'transaction_date' => '2026-11-15',
        ]);

        $this->expectException(DomainException::class);
        $this->investmentService->recordTrade($user, [
            'asset_type' => 'us_stock',
            'symbol' => 'AAPL',
            'type' => 'sell',
            'quantity' => 5,
            'price_per_unit' => 220,
            'fee' => 0,
            'transaction_date' => '2026-11-16',
        ]);
    }
}
