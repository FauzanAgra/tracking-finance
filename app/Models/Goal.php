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
    'title',
    'currency',
    'target_amount',
    'current_amount',
    'target_date',
    'status',
    'icon',
])]
class Goal extends Model
{
    use HasFactory;

    /**
     * Default attribute values for newly instantiated Goal models.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'IDR',
        'current_amount' => 0,
        'status' => 'in_progress',
        'icon' => 'flag',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'target_date' => 'date',
        ];
    }

    /**
     * Get the user that owns the goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all contributions allocated to this goal.
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(GoalContribution::class);
    }

    /**
     * Scope a query to goals belonging to a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
