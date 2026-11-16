<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class GoalService
{
    public function __construct(
        private readonly WalletService $walletService,
    ) {}

    /**
     * Create a new financial goal.
     *
     * @param  array<string, mixed>  $data
     */
    public function createGoal(User $user, array $data): Goal
    {
        return Goal::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'currency' => $data['currency'] ?? 'IDR',
            'target_amount' => $data['target_amount'],
            'current_amount' => 0,
            'target_date' => $data['target_date'] ?? null,
            'status' => 'in_progress',
            'icon' => $data['icon'] ?? 'flag',
        ]);
    }

    /**
     * Update an existing financial goal.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateGoal(Goal $goal, array $data): Goal
    {
        $goal->update([
            'title' => $data['title'],
            'currency' => $data['currency'] ?? $goal->currency,
            'target_amount' => $data['target_amount'],
            'target_date' => $data['target_date'] ?? null,
            'icon' => $data['icon'] ?? $goal->icon,
            'status' => $data['status'] ?? $goal->status,
        ]);

        return $goal->refresh();
    }

    /**
     * Allocate funds from a user wallet into a goal vault atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function contribute(Goal $goal, array $data): GoalContribution
    {
        return DB::transaction(function () use ($goal, $data): GoalContribution {
            $lockedGoal = Goal::query()->lockForUpdate()->findOrFail($goal->id);

            if ($lockedGoal->status !== 'in_progress') {
                throw new DomainException('Setoran hanya dapat dilakukan untuk goal yang sedang berjalan.');
            }

            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new DomainException('Nominal setoran harus lebih besar dari nol.');
            }

            $wallet = Wallet::query()
                ->where('user_id', $lockedGoal->user_id)
                ->findOrFail($data['wallet_id']);

            if ($wallet->currency !== $lockedGoal->currency) {
                throw new DomainException("Mata uang dompet ({$wallet->currency}) harus sama dengan mata uang goal ({$lockedGoal->currency}).");
            }

            $this->walletService->debit($wallet, $amount);

            $contribution = GoalContribution::create([
                'goal_id' => $lockedGoal->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'contribution_date' => $data['contribution_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $newAmount = (float) $lockedGoal->current_amount + $amount;
            $lockedGoal->current_amount = $newAmount;

            if ($newAmount >= (float) $lockedGoal->target_amount) {
                $lockedGoal->status = 'achieved';
            }

            $lockedGoal->save();

            return $contribution;
        });
    }

    /**
     * Delete a goal only if it has no recorded contributions.
     */
    public function deleteGoal(Goal $goal): void
    {
        if ($goal->contributions()->exists()) {
            throw new DomainException('Goal yang sudah memiliki riwayat setoran dana tidak dapat dihapus.');
        }

        $goal->delete();
    }

    /**
     * Compute progress percentage, remaining amount, and smart time/saving estimation.
     *
     * @return array{percentage: float, remaining: float, estimate_text: string}
     */
    public function computeGoalInsights(Goal $goal): array
    {
        $target = (float) $goal->target_amount;
        $current = (float) $goal->current_amount;
        $remaining = max(0.0, $target - $current);
        $percentage = $target > 0 ? min(100.0, round(($current / $target) * 100, 1)) : 0.0;

        if ($goal->status === 'achieved' || $remaining <= 0.0) {
            return [
                'percentage' => 100.0,
                'remaining' => 0.0,
                'estimate_text' => 'Selamat! Target tabungan ini sudah tercapai.',
            ];
        }

        if ($goal->status === 'cancelled') {
            return [
                'percentage' => $percentage,
                'remaining' => $remaining,
                'estimate_text' => 'Target ini sedang dibatalkan.',
            ];
        }

        if ($goal->target_date !== null) {
            $today = Carbon::today();
            $targetDate = $goal->target_date->copy()->startOfDay();

            if ($targetDate->lessThanOrEqualTo($today)) {
                return [
                    'percentage' => $percentage,
                    'remaining' => $remaining,
                    'estimate_text' => 'Tenggat waktu sudah tiba — kurang Rp '.number_format($remaining, 0, ',', '.').' lagi.',
                ];
            }

            $monthsLeft = max(1, (int) ceil($today->diffInDays($targetDate) / 30));
            $recommendedPerMonth = ceil($remaining / $monthsLeft);

            return [
                'percentage' => $percentage,
                'remaining' => $remaining,
                'estimate_text' => "Saran nabung Rp ".number_format($recommendedPerMonth, 0, ',', '.')."/bln agar tercapai dalam {$monthsLeft} bulan.",
            ];
        }

        $contributionCount = $goal->contributions->count();

        if ($contributionCount > 0 && $current > 0) {
            $avgPerContribution = $current / $contributionCount;
            $estimatedSteps = (int) ceil($remaining / $avgPerContribution);

            return [
                'percentage' => $percentage,
                'remaining' => $remaining,
                'estimate_text' => "Est. butuh ~{$estimatedSteps}x setoran lagi (rata-rata Rp ".number_format($avgPerContribution, 0, ',', '.').'/setor).',
            ];
        }

        return [
            'percentage' => $percentage,
            'remaining' => $remaining,
            'estimate_text' => 'Mulai sisihkan dana dari dompet untuk melihat estimasi cerdas.',
        ];
    }
}
