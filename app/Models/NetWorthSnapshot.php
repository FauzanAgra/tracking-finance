<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetWorthSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'snapshot_date',
        'net_worth_idr',
        'cash_idr',
        'investment_idr',
        'receivable_idr',
        'payable_idr',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'net_worth_idr' => 'float',
            'cash_idr' => 'float',
            'investment_idr' => 'float',
            'receivable_idr' => 'float',
            'payable_idr' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
