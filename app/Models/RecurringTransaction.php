<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'destination_wallet_id',
        'category_id',
        'type',
        'amount',
        'fee',
        'frequency',
        'start_date',
        'next_run_date',
        'last_run_date',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'fee' => 'float',
            'start_date' => 'date',
            'next_run_date' => 'date',
            'last_run_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function destinationWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'destination_wallet_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDue(Builder $query, ?Carbon $date = null): Builder
    {
        $target = $date ?? Carbon::today();

        return $query->where('is_active', true)
            ->whereDate('next_run_date', '<=', $target->toDateString());
    }
}
