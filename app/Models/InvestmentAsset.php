<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'symbol',
    'name',
    'asset_type',
    'currency',
    'current_price',
    'is_manual_price',
    'last_price_updated_at',
])]
class InvestmentAsset extends Model
{
    use HasFactory;

    /**
     * Default attribute values for newly instantiated InvestmentAsset models.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'current_price' => 0,
        'is_manual_price' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_price' => 'decimal:6',
            'is_manual_price' => 'boolean',
            'last_price_updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the investment asset.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all buy/sell/dividend transactions for this asset.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InvestmentTransaction::class);
    }

    /**
     * Get historical recorded prices for sparkline trend analysis.
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(InvestmentPriceHistory::class)->orderBy('recorded_date');
    }

    /**
     * Scope a query to investment assets belonging to a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
