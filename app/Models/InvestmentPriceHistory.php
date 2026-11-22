<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_asset_id',
        'price',
        'recorded_date',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'recorded_date' => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(InvestmentAsset::class, 'investment_asset_id');
    }

    public function scopeForAsset(Builder $query, int $assetId): Builder
    {
        return $query->where('investment_asset_id', $assetId);
    }

    /**
     * Record or update price history for a given asset and date idempotently.
     */
    public static function recordPrice(int $assetId, float $price, ?\Carbon\Carbon $date = null): self
    {
        $targetDate = ($date ?? \Carbon\Carbon::today())->toDateString();
        $existing = self::query()
            ->where('investment_asset_id', $assetId)
            ->whereDate('recorded_date', $targetDate)
            ->first();

        if ($existing) {
            $existing->update(['price' => $price]);

            return $existing->fresh();
        }

        return self::create([
            'investment_asset_id' => $assetId,
            'price' => $price,
            'recorded_date' => $targetDate,
        ]);
    }
}
