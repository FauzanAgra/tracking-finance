<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Goal;
use App\Models\SystemExchangeRate;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        private readonly InvestmentService $investmentService,
        private readonly BudgetService $budgetService,
    ) {}

    /**
     * Aggregate Total Net Worth (IDR), Expense Distribution, Cashflow Trend, and recent activity.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(User $user): array
    {
        $now = Carbon::now();
        $usdIdrRate = (float) (SystemExchangeRate::query()->where('pair', 'USD/IDR')->value('rate') ?? InvestmentService::DEFAULT_USD_IDR_RATE);
        $usdtIdrRate = (float) (SystemExchangeRate::query()->where('pair', 'USDT/IDR')->value('rate') ?? $usdIdrRate);

        $toIdr = static function (float $amount, string $currency) use ($usdIdrRate, $usdtIdrRate): float {
            return match ($currency) {
                'USD' => $amount * $usdIdrRate,
                'USDT' => $amount * $usdtIdrRate,
                default => $amount,
            };
        };

        // 1. Wallets (Cash & Valas converted to IDR)
        $wallets = Wallet::query()
            ->forUser($user->id)
            ->active()
            ->orderByDesc('is_default')
            ->orderByDesc('balance')
            ->get();

        $totalCashIdr = (float) $wallets->sum(fn (Wallet $w) => $toIdr((float) $w->balance, $w->currency));

        // 2. Investments (already converted to IDR)
        $investmentSummary = $this->investmentService->getPortfolioSummary($user);
        $totalInvestmentIdr = (float) $investmentSummary['total_market_value_idr'];

        // 3. Debts & Receivables (remaining balances converted to IDR)
        $debts = Debt::query()
            ->forUser($user->id)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->with('payments')
            ->get();

        $totalReceivableIdr = 0.0;
        $totalPayableIdr = 0.0;

        foreach ($debts as $debt) {
            $paid = (float) $debt->payments->sum('amount');
            $remaining = max(0.0, (float) $debt->total_amount - $paid - (float) $debt->written_off_amount);
            $remainingIdr = $toIdr($remaining, $debt->currency);

            if ($debt->type === 'receivable') {
                $totalReceivableIdr += $remainingIdr;
            } else {
                $totalPayableIdr += $remainingIdr;
            }
        }

        // Net Worth Formula: Cash + Investments + Receivables - Payables
        $netWorthIdr = $totalCashIdr + $totalInvestmentIdr + $totalReceivableIdr - $totalPayableIdr;

        // 4. Current Month Income & Expense + Budget Overview
        $monthIncome = (float) Transaction::query()
            ->forUser($user->id)
            ->where('type', 'income')
            ->whereYear('transaction_date', $now->year)
            ->whereMonth('transaction_date', $now->month)
            ->sum('amount');

        $monthExpense = (float) Transaction::query()
            ->forUser($user->id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $now->year)
            ->whereMonth('transaction_date', $now->month)
            ->sum('amount');

        $budgetOverview = $this->budgetService->getMonthlyOverview($user, $now->format('Y-m'));

        // 5. DASH-002: Expense Distribution by Category (Current Month)
        $expenseRows = Transaction::query()
            ->forUser($user->id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $now->year)
            ->whereMonth('transaction_date', $now->month)
            ->with('category')
            ->get()
            ->groupBy(fn (Transaction $t) => $t->category?->name ?? 'Lainnya');

        $expenseDistribution = [
            'labels' => [],
            'values' => [],
            'colors' => [],
            'items' => [],
        ];

        $palette = ['#A8E874', '#34D399', '#3B82F6', '#F59E0B', '#F43F5E', '#8B5CF6'];
        $colorIndex = 0;

        foreach ($expenseRows as $categoryName => $txns) {
            $sum = (float) $txns->sum('amount');
            $firstCat = $txns->first()?->category;
            $color = $firstCat?->color ?: $palette[$colorIndex % count($palette)];
            $pct = $monthExpense > 0 ? round(($sum / $monthExpense) * 100, 1) : 0.0;

            $expenseDistribution['labels'][] = $categoryName;
            $expenseDistribution['values'][] = $sum;
            $expenseDistribution['colors'][] = $color;
            $expenseDistribution['items'][] = [
                'name' => $categoryName,
                'amount' => $sum,
                'percentage' => $pct,
                'color' => $color,
            ];
            $colorIndex++;
        }

        // 6. DASH-003: Cashflow Trend (Last 6 Months)
        $cashflowTrend = [
            'labels' => [],
            'income' => [],
            'expense' => [],
        ];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->startOfMonth()->subMonths($i);

            $inc = (float) Transaction::query()
                ->forUser($user->id)
                ->where('type', 'income')
                ->whereYear('transaction_date', $monthDate->year)
                ->whereMonth('transaction_date', $monthDate->month)
                ->sum('amount');

            $exp = (float) Transaction::query()
                ->forUser($user->id)
                ->where('type', 'expense')
                ->whereYear('transaction_date', $monthDate->year)
                ->whereMonth('transaction_date', $monthDate->month)
                ->sum('amount');

            $cashflowTrend['labels'][] = $monthDate->translatedFormat('M');
            $cashflowTrend['income'][] = $inc;
            $cashflowTrend['expense'][] = $exp;
        }

        // 7. Recent Transactions & Active Goals Preview
        $recentTransactions = Transaction::query()
            ->forUser($user->id)
            ->with(['wallet', 'category', 'destinationWallet'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $activeGoals = Goal::query()
            ->forUser($user->id)
            ->where('status', 'in_progress')
            ->orderByDesc('id')
            ->limit(2)
            ->get();

        return [
            'net_worth_idr' => $netWorthIdr,
            'total_cash_idr' => $totalCashIdr,
            'total_investment_idr' => $totalInvestmentIdr,
            'total_receivable_idr' => $totalReceivableIdr,
            'total_payable_idr' => $totalPayableIdr,
            'usd_idr_rate' => $usdIdrRate,
            'month_income' => $monthIncome,
            'month_expense' => $monthExpense,
            'budget_overview' => $budgetOverview,
            'wallets' => $wallets,
            'expense_distribution' => $expenseDistribution,
            'cashflow_trend' => $cashflowTrend,
            'recent_transactions' => $recentTransactions,
            'active_goals' => $activeGoals,
        ];
    }
}
