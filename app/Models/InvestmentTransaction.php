<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'investment_asset_id',
    'wallet_id',
    'type',
    'lots',
    'quantity',
    'price_per_unit',
    'fee',
    'total_cost',
    'transaction_date',
    'notes',
])]
class InvestmentTransaction extends Model
{
    use HasFactory;

    /**
     * Default attribute values for newly instantiated InvestmentTransaction models.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'fee' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lots' => 'decimal:2',
            'quantity' => 'decimal:8',
            'price_per_unit' => 'decimal:6',
            'fee' => 'decimal:2',
            'total_cost' => 'decimal:4',
            'transaction_date' => 'date',
        ];
    }

    /**
     * Get the investor user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investment asset traded in this transaction.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(InvestmentAsset::class, 'investment_asset_id');
    }

    /**
     * Get the wallet linked to this trade (if any).
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Scope a query to investment transactions belonging to a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
