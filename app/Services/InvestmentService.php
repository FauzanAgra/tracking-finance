<?php

namespace App\Services;

use App\Models\InvestmentAsset;
use App\Models\InvestmentPriceHistory;
use App\Models\InvestmentTransaction;
use App\Models\SystemExchangeRate;
use App\Models\User;
use App\Models\Wallet;
use DomainException;
use Illuminate\Support\Facades\DB;

class InvestmentService
{
    public const DEFAULT_USD_IDR_RATE = 16000.0;

    public function __construct(
        private readonly WalletService $walletService,
    ) {}

    /**
     * Record a buy, sell, or dividend trade and mutate the linked wallet atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordTrade(User $user, array $data): InvestmentTransaction
    {
        return DB::transaction(function () use ($user, $data): InvestmentTransaction {
            $assetType = $data['asset_type'];
            $symbol = strtoupper(trim((string) $data['symbol']));
            if ($assetType === 'idx_stock' && ! str_ends_with($symbol, '.JK')) {
                $symbol .= '.JK';
            }

            $defaultCurrency = match ($assetType) {
                'idx_stock' => 'IDR',
                'us_stock' => 'USD',
                'crypto' => $data['currency'] ?? 'USDT',
                default => 'IDR',
            };

            $pricePerUnit = (float) $data['price_per_unit'];
            $lots = $assetType === 'idx_stock' ? (float) ($data['lots'] ?? 0) : null;
            $quantity = $assetType === 'idx_stock'
                ? ($lots > 0 ? $lots * 100 : (float) $data['quantity'])
                : (float) $data['quantity'];

            if ($quantity <= 0) {
                throw new DomainException('Jumlah unit/lot harus lebih besar dari nol.');
            }

            $asset = InvestmentAsset::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'symbol' => $symbol,
                    'asset_type' => $assetType,
                ],
                [
                    'name' => $data['name'] ?? $symbol,
                    'currency' => $defaultCurrency,
                    'current_price' => $pricePerUnit,
                    'is_manual_price' => false,
                    'last_price_updated_at' => now(),
                ]
            );

            $asset->load('transactions');
            $position = $this->calculatePosition($asset);
            $tradeType = $data['type'];

            if ($tradeType === 'sell' && $quantity - $position['quantity'] > 0.00000001) {
                throw new DomainException('Jumlah jual melebihi total kepemilikan aset saat ini.');
            }

            $fee = (float) ($data['fee'] ?? 0);
            $grossAmount = $quantity * $pricePerUnit;
            $totalCost = $tradeType === 'buy'
                ? $grossAmount + $fee
                : max(0.0, $grossAmount - $fee);

            $walletId = $data['wallet_id'] ?? null;
            if ($walletId) {
                $wallet = Wallet::query()
                    ->where('user_id', $user->id)
                    ->findOrFail($walletId);

                if ($wallet->currency !== $asset->currency) {
                    throw new DomainException("Mata uang dompet ({$wallet->currency}) harus sama dengan aset ({$asset->currency}).");
                }

                if ($tradeType === 'buy') {
                    $this->walletService->debit($wallet, $totalCost);
                } else {
                    $this->walletService->credit($wallet, $totalCost);
                }
            }

            return InvestmentTransaction::create([
                'user_id' => $user->id,
                'investment_asset_id' => $asset->id,
                'wallet_id' => $walletId ?: null,
                'type' => $tradeType,
                'lots' => $lots ?: null,
                'quantity' => $quantity,
                'price_per_unit' => $pricePerUnit,
                'fee' => $fee,
                'total_cost' => $totalCost,
                'transaction_date' => $data['transaction_date'],
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Calculate Weighted Average Cost (DCA) and Unrealized PnL for a single asset.
     *
     * @return array<string, mixed>
     */
    public function calculatePosition(InvestmentAsset $asset): array
    {
        $transactions = $asset->transactions
            ->sortBy([
                ['transaction_date', 'asc'],
                ['id', 'asc'],
            ]);

        $quantity = 0.0;
        $totalCost = 0.0;
        $averagePrice = 0.0;
        $totalDividend = 0.0;

        foreach ($transactions as $txn) {
            $qty = (float) $txn->quantity;
            $price = (float) $txn->price_per_unit;
            $fee = (float) $txn->fee;

            if ($txn->type === 'buy') {
                $totalCost = ($quantity * $averagePrice) + ($qty * $price) + $fee;
                $quantity += $qty;
                $averagePrice = $quantity > 0 ? $totalCost / $quantity : 0.0;
            } elseif ($txn->type === 'sell') {
                $soldQty = min($quantity, $qty);
                $quantity -= $soldQty;
                $totalCost = $quantity * $averagePrice;
                if ($quantity <= 0.00000001) {
                    $quantity = 0.0;
                    $totalCost = 0.0;
                    $averagePrice = 0.0;
                }
            } elseif ($txn->type === 'dividend') {
                $totalDividend += max(0.0, ($qty * $price) - $fee);
            }
        }

        $currentPrice = (float) $asset->current_price;
        $marketValue = $quantity * $currentPrice;
        $investedCapital = $quantity * $averagePrice;
        $unrealizedPnl = $marketValue - $investedCapital;
        $unrealizedPnlPct = $investedCapital > 0
            ? round(($unrealizedPnl / $investedCapital) * 100, 2)
            : 0.0;

        return [
            'asset' => $asset,
            'quantity' => $quantity,
            'lots' => $asset->asset_type === 'idx_stock' ? round($quantity / 100, 2) : null,
            'average_price' => $averagePrice,
            'current_price' => $currentPrice,
            'invested_capital' => $investedCapital,
            'market_value' => $marketValue,
            'unrealized_pnl' => $unrealizedPnl,
            'unrealized_pnl_pct' => $unrealizedPnlPct,
            'total_dividend' => $totalDividend,
        ];
    }

    /**
     * Build the full user portfolio summary converted to IDR.
     *
     * @return array<string, mixed>
     */
    public function getPortfolioSummary(User $user): array
    {
        $usdIdrRate = (float) (SystemExchangeRate::query()->where('pair', 'USD/IDR')->value('rate') ?? self::DEFAULT_USD_IDR_RATE);
        $usdtIdrRate = (float) (SystemExchangeRate::query()->where('pair', 'USDT/IDR')->value('rate') ?? $usdIdrRate);

        $assets = InvestmentAsset::query()
            ->forUser($user->id)
            ->with(['transactions', 'priceHistories'])
            ->orderBy('asset_type')
            ->orderBy('symbol')
            ->get();

        $positions = $assets->map(function (InvestmentAsset $asset) use ($usdIdrRate, $usdtIdrRate): array {
            $pos = $this->calculatePosition($asset);
            $fxRate = match ($asset->currency) {
                'USD' => $usdIdrRate,
                'USDT' => $usdtIdrRate,
                default => 1.0,
            };

            $pos['fx_rate'] = $fxRate;
            $pos['market_value_idr'] = $pos['market_value'] * $fxRate;
            $pos['invested_capital_idr'] = $pos['invested_capital'] * $fxRate;
            $pos['unrealized_pnl_idr'] = $pos['unrealized_pnl'] * $fxRate;

            // ADV-004: Sparkline points calculation
            $histories = $asset->priceHistories->take(-7);
            $prices = $histories->pluck('price')->all();
            if (empty($prices)) {
                $prices = [(float) $pos['current_price'], (float) $pos['current_price']];
            } elseif (count($prices) === 1) {
                $prices = [$prices[0], $prices[0]];
            }

            $min = min($prices);
            $max = max($prices);
            $range = $max - $min;
            $n = count($prices);
            $width = 54;
            $height = 20;

            $pts = [];
            foreach ($prices as $i => $p) {
                $x = $n > 1 ? round(($i / ($n - 1)) * $width, 1) : 0;
                $y = $range > 0 ? round($height - (($p - $min) / $range) * ($height - 4) - 2, 1) : round($height / 2, 1);
                $pts[] = "{$x},{$y}";
            }

            $pos['sparkline_points'] = implode(' ', $pts);
            $pos['is_trend_up'] = end($prices) >= reset($prices);

            return $pos;
        });

        $totalMarketValueIdr = (float) $positions->sum('market_value_idr');
        $totalInvestedIdr = (float) $positions->sum('invested_capital_idr');
        $totalUnrealizedPnlIdr = $totalMarketValueIdr - $totalInvestedIdr;
        $totalUnrealizedPnlPct = $totalInvestedIdr > 0
            ? round(($totalUnrealizedPnlIdr / $totalInvestedIdr) * 100, 2)
            : 0.0;

        return [
            'positions' => $positions,
            'total_market_value_idr' => $totalMarketValueIdr,
            'total_invested_idr' => $totalInvestedIdr,
            'total_unrealized_pnl_idr' => $totalUnrealizedPnlIdr,
            'total_unrealized_pnl_pct' => $totalUnrealizedPnlPct,
            'usd_idr_rate' => $usdIdrRate,
        ];
    }

    /**
     * Override an asset's market price manually (API fallback).
     */
    public function updateManualPrice(InvestmentAsset $asset, float $price): InvestmentAsset
    {
        if ($price <= 0) {
            throw new DomainException('Harga aset harus lebih besar dari nol.');
        }

        $asset->update([
            'current_price' => $price,
            'is_manual_price' => true,
            'last_price_updated_at' => now(),
        ]);

        InvestmentPriceHistory::recordPrice($asset->id, (float) $price);

        return $asset->refresh();
    }
}
