<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BudgetService
{
    /**
     * Build the monthly budget overview with spent totals and visual health statuses.
     *
     * @return array{
     *     month_year: string,
     *     items: Collection<int, array<string, mixed>>,
     *     total_budget: float,
     *     total_spent: float,
     *     total_remaining: float,
     *     overall_percentage: float,
     *     overall_health: string
     * }
     */
    public function getMonthlyOverview(User $user, string $monthYear): array
    {
        $period = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();

        $budgets = Budget::query()
            ->forUser($user->id)
            ->forMonth($monthYear)
            ->with('category')
            ->get();

        $spentByCategory = Transaction::query()
            ->forUser($user->id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $period->year)
            ->whereMonth('transaction_date', $period->month)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as total_spent')
            ->groupBy('category_id')
            ->pluck('total_spent', 'category_id');

        $items = $budgets->map(function (Budget $budget) use ($spentByCategory): array {
            $limit = (float) $budget->amount;
            $spent = (float) ($spentByCategory[$budget->category_id] ?? 0);
            $percentage = $limit > 0 ? round(($spent / $limit) * 100, 1) : 0.0;

            return [
                'budget' => $budget,
                'limit' => $limit,
                'spent' => $spent,
                'remaining' => max(0.0, $limit - $spent),
                'over_amount' => max(0.0, $spent - $limit),
                'percentage' => $percentage,
                'health' => $this->resolveHealthStatus($percentage),
            ];
        })->sortByDesc('percentage')->values();

        $totalBudget = (float) $items->sum('limit');
        $totalSpent = (float) $items->sum('spent');
        $overallPercentage = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0.0;

        return [
            'month_year' => $monthYear,
            'items' => $items,
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'total_remaining' => max(0.0, $totalBudget - $totalSpent),
            'overall_percentage' => $overallPercentage,
            'overall_health' => $this->resolveHealthStatus($overallPercentage),
        ];
    }

    /**
     * Create or update a monthly budget for an expense category.
     *
     * @param  array<string, mixed>  $data
     */
    public function upsertBudget(User $user, array $data): Budget
    {
        return Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'month_year' => $data['month_year'],
            ],
            [
                'amount' => $data['amount'],
            ]
        );
    }

    /**
     * Delete a budget record.
     */
    public function deleteBudget(Budget $budget): void
    {
        $budget->delete();
    }

    /**
     * Determine visual indicator health status:
     * - safe (< 70%): Green
     * - warning (70% - 99.9%): Yellow/Amber
     * - over (>= 100%): Red
     */
    public function resolveHealthStatus(float $percentage): string
    {
        if ($percentage >= 100.0) {
            return 'over';
        }

        if ($percentage >= 70.0) {
            return 'warning';
        }

        return 'safe';
    }
}
