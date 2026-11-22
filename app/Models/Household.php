<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Household extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'invite_code',
    ];

    /**
     * Generate an 8-character unique alphanumeric invite code.
     */
    public static function generateInviteCode(): string
    {
        do {
            $code = 'FAM-' . strtoupper(Str::random(6));
        } while (static::where('invite_code', $code)->exists());

        return $code;
    }

    /**
     * The owner of the household.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The members belonging to the household.
     */
    public function members(): HasMany
    {
        return $this->hasMany(HouseholdMember::class);
    }

    /**
     * The user models of all members in this household.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'household_members')
            ->withPivot('role')
            ->withTimestamps();
    }
}
