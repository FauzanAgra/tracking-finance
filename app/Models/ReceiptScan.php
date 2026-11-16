<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'file_path',
    'raw_gemini_response',
    'status',
    'generated_transaction_id',
])]
class ReceiptScan extends Model
{
    use HasFactory;

    /**
     * Default attribute values for newly instantiated ReceiptScan models.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'raw_gemini_response' => 'array',
        ];
    }

    /**
     * Get the user who uploaded the receipt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transaction generated after confirming the scan.
     */
    public function generatedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'generated_transaction_id');
    }

    /**
     * Scope a query to receipt scans belonging to a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
