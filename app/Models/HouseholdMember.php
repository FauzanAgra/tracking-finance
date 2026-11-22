<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdMember extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'household_id',
        'user_id',
        'role',
    ];

    /**
     * The household associated with this membership.
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * The user associated with this membership.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
