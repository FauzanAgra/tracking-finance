<?php

namespace App\Models;

use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'type',
    'currency',
    'balance',
    'account_number',
    'color',
    'icon',
    'is_default',
    'is_active',
    'is_shared',
])]
class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory;

    /**
     * Default attribute values for newly instantiated Wallet models.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'IDR',
        'balance' => 0,
        'color' => '#3B82F6',
        'icon' => 'wallet',
        'is_default' => false,
        'is_active' => true,
        'is_shared' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:4',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'is_shared' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get transactions originating from this wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get debt installment payments linked to this wallet.
     */
    public function debtPayments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    /**
     * Scope a query to only include active wallets.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to wallets belonging to a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
