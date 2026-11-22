<?php

namespace App\Services;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use App\Models\Wallet;
use DomainException;
use Illuminate\Support\Facades\DB;

class HouseholdService
{
    /**
     * Create a new household and assign creator as owner.
     */
    public function createHousehold(User $owner, string $name): Household
    {
        return DB::transaction(function () use ($owner, $name): Household {
            if ($owner->householdMember()->exists()) {
                throw new DomainException('Kamu sudah terdaftar dalam sebuah rumah tangga. Tinggalkan keluarga saat ini sebelum membuat yang baru.');
            }

            $household = Household::create([
                'owner_id' => $owner->id,
                'name' => trim($name),
                'invite_code' => Household::generateInviteCode(),
            ]);

            HouseholdMember::create([
                'household_id' => $household->id,
                'user_id' => $owner->id,
                'role' => 'owner',
            ]);

            return $household;
        });
    }

    /**
     * Join an existing household using an invitation code.
     */
    public function joinHousehold(User $user, string $inviteCode): Household
    {
        return DB::transaction(function () use ($user, $inviteCode): Household {
            if ($user->householdMember()->exists()) {
                throw new DomainException('Kamu sudah terdaftar dalam sebuah rumah tangga.');
            }

            $cleanCode = trim(strtoupper($inviteCode));
            $household = Household::where('invite_code', $cleanCode)->first();

            if (! $household) {
                throw new DomainException('Kode undangan rumah tangga tidak valid atau tidak ditemukan.');
            }

            HouseholdMember::create([
                'household_id' => $household->id,
                'user_id' => $user->id,
                'role' => 'member',
            ]);

            return $household;
        });
    }

    /**
     * Leave the current household or dissolve it if the owner is alone.
     */
    public function leaveHousehold(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $membership = $user->householdMember;
            if (! $membership) {
                throw new DomainException('Kamu tidak terdaftar di rumah tangga mana pun.');
            }

            $household = $membership->household;

            if ($membership->role === 'owner') {
                $otherMembersCount = $household->members()->where('user_id', '!=', $user->id)->count();
                if ($otherMembersCount > 0) {
                    throw new DomainException('Sebagai kepala/pemilik keluarga, kamu harus membubarkan rumah tangga untuk keluar.');
                }

                $household->delete();
            } else {
                $membership->delete();
            }
        });
    }

    /**
     * Dissolve/delete the household completely (owner only).
     */
    public function deleteHousehold(Household $household, User $user): void
    {
        if ($household->owner_id !== $user->id) {
            throw new DomainException('Hanya kepala/pemilik keluarga yang dapat membubarkan rumah tangga ini.');
        }

        $household->delete();
    }

    /**
     * Generate a fresh invitation code for the household (owner only).
     */
    public function regenerateInviteCode(Household $household, User $user): string
    {
        if ($household->owner_id !== $user->id) {
            throw new DomainException('Hanya pemilik rumah tangga yang dapat memperbarui kode undangan.');
        }

        $newCode = Household::generateInviteCode();
        $household->update(['invite_code' => $newCode]);

        return $newCode;
    }

    /**
     * Remove a member from the household (owner only).
     */
    public function removeMember(Household $household, User $owner, int $targetUserId): void
    {
        if ($household->owner_id !== $owner->id) {
            throw new DomainException('Hanya pemilik rumah tangga yang dapat mengeluarkan anggota.');
        }

        if ($targetUserId === $owner->id) {
            throw new DomainException('Pemilik tidak dapat mengeluarkan diri sendiri.');
        }

        $membership = $household->members()->where('user_id', $targetUserId)->first();
        if ($membership) {
            $membership->delete();
        }
    }

    /**
     * Compile statistical summary of household wealth and shared wallets.
     *
     * @return array<string, mixed>
     */
    public function getHouseholdSummary(Household $household): array
    {
        $members = $household->members()->with('user')->get();
        $memberUserIds = $members->pluck('user_id');

        $sharedWallets = Wallet::query()
            ->whereIn('user_id', $memberUserIds)
            ->where('is_shared', true)
            ->where('is_active', true)
            ->with('user')
            ->get();

        $totalSharedWealth = (float) $sharedWallets->sum('balance');

        $perMemberSummary = $members->map(function (HouseholdMember $member) use ($sharedWallets) {
            $userWallets = $sharedWallets->where('user_id', $member->user_id);
            $userSharedBalance = (float) $userWallets->sum('balance');

            return [
                'user' => $member->user,
                'role' => $member->role,
                'joined_at' => $member->created_at,
                'shared_balance' => $userSharedBalance,
                'shared_wallets_count' => $userWallets->count(),
            ];
        });

        return [
            'household' => $household,
            'members' => $members,
            'per_member_summary' => $perMemberSummary,
            'shared_wallets' => $sharedWallets,
            'total_shared_wealth' => $totalSharedWealth,
        ];
    }
}
