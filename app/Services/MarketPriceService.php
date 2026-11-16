<?php

namespace App\Services;

use App\Models\InvestmentAsset;
use App\Models\SystemExchangeRate;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Throwable;

class MarketPriceService
{
    /**
     * Map common crypto ticker symbols to CoinGecko IDs.
     *
     * @var array<string, string>
     */
    private const COINGECKO_IDS = [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'SOL' => 'solana',
        'BNB' => 'binancecoin',
        'XRP' => 'ripple',
        'ADA' => 'cardano',
        'DOGE' => 'dogecoin',
        'AVAX' => 'avalanche-2',
        'LINK' => 'chainlink',
        'TON' => 'the-open-network',
        'USDT' => 'tether',
        'USDC' => 'usd-coin',
    ];

    /**
     * Refresh market prices for assets (skipping manual overrides unless forced) and USD/IDR rate.
     *
     * @return array{updated: int, failed: int, skipped_manual: int}
     */
    public function refreshAll(?User $user = null, bool $includeManual = false): array
    {
        $this->refreshUsdIdrRate();

        $query = InvestmentAsset::query();
        if ($user !== null) {
            $query->forUser($user->id);
        }

        $assets = $query->get();
        $updated = 0;
        $failed = 0;
        $skippedManual = 0;

        $cryptoAssets = $assets->where('asset_type', 'crypto');
        $stockAssets = $assets->whereIn('asset_type', ['idx_stock', 'us_stock']);

        // Batch CoinGecko query for non-manual crypto assets
        $autoCrypto = $cryptoAssets->filter(function (InvestmentAsset $asset) use ($includeManual, &$skippedManual): bool {
            if ($asset->is_manual_price && ! $includeManual) {
                $skippedManual++;

                return false;
            }

            return true;
        });

        if ($autoCrypto->isNotEmpty()) {
            $coinIds = $autoCrypto->map(fn (InvestmentAsset $a) => $this->resolveCoinGeckoId($a->symbol))->unique()->values()->all();
            $prices = $this->fetchCoinGeckoPrices($coinIds);

            foreach ($autoCrypto as $asset) {
                $coinId = $this->resolveCoinGeckoId($asset->symbol);
                $vsCurrency = strtolower($asset->currency === 'IDR' ? 'idr' : 'usd');
                $price = $prices[$coinId][$vsCurrency] ?? null;

                if ($price !== null && $price > 0) {
                    $asset->update([
                        'current_price' => $price,
                        'is_manual_price' => false,
                        'last_price_updated_at' => now(),
                    ]);
                    $updated++;
                } else {
                    $failed++;
                }
            }
        }

        // Yahoo Finance queries for IDX and US stocks
        foreach ($stockAssets as $asset) {
            if ($asset->is_manual_price && ! $includeManual) {
                $skippedManual++;

                continue;
            }

            $price = $this->fetchYahooPrice($asset->symbol);
            if ($price !== null && $price > 0) {
                $asset->update([
                    'current_price' => $price,
                    'is_manual_price' => false,
                    'last_price_updated_at' => now(),
                ]);
                $updated++;
            } else {
                $failed++;
            }
        }

        return [
            'updated' => $updated,
            'failed' => $failed,
            'skipped_manual' => $skippedManual,
        ];
    }

    /**
     * Refresh USD/IDR and USDT/IDR system exchange rates.
     */
    public function refreshUsdIdrRate(): ?float
    {
        $rate = $this->fetchYahooPrice('USDIDR=X');

        if ($rate === null) {
            $prices = $this->fetchCoinGeckoPrices(['tether']);
            $rate = $prices['tether']['idr'] ?? null;
        }

        if ($rate !== null && $rate > 0) {
            SystemExchangeRate::updateOrCreate(['pair' => 'USD/IDR'], ['rate' => $rate]);
            SystemExchangeRate::updateOrCreate(['pair' => 'USDT/IDR'], ['rate' => $rate]);

            return (float) $rate;
        }

        return null;
    }

    public function resolveCoinGeckoId(string $symbol): string
    {
        $clean = strtoupper(trim($symbol));

        return self::COINGECKO_IDS[$clean] ?? strtolower($clean);
    }

    /**
     * @param  array<int, string>  $coinIds
     * @return array<string, array<string, float>>
     */
    private function fetchCoinGeckoPrices(array $coinIds): array
    {
        if (empty($coinIds)) {
            return [];
        }

        try {
            $response = Http::timeout(8)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => implode(',', $coinIds),
                'vs_currencies' => 'usd,idr',
            ]);

            if ($response->successful()) {
                return $response->json() ?? [];
            }
        } catch (Throwable) {
            // Gracefully fall back when offline or rate-limited
        }

        return [];
    }

    private function fetchYahooPrice(string $symbol): ?float
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get('https://query1.finance.yahoo.com/v8/finance/chart/'.urlencode($symbol), [
                    'interval' => '1d',
                    'range' => '1d',
                ]);

            if ($response->successful()) {
                $price = data_get($response->json(), 'chart.result.0.meta.regularMarketPrice');

                return is_numeric($price) ? (float) $price : null;
            }
        } catch (Throwable) {
            // Gracefully fall back when offline or API unavailable
        }

        return null;
    }
}
