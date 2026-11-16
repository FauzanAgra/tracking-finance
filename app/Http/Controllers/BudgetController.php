<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Models\Budget;
use App\Models\Category;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(
        private readonly BudgetService $budgetService,
    ) {}

    /**
     * Display monthly budgets with visual usage indicators.
     */
    public function index(Request $request): View
    {
        $monthParam = (string) $request->query('month', now()->format('Y-m'));
        $monthYear = preg_match('/^\d{4}-\d{2}$/', $monthParam) ? $monthParam : now()->format('Y-m');
        $period = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();

        $overview = $this->budgetService->getMonthlyOverview($request->user(), $monthYear);

        $expenseCategories = Category::query()
            ->forUser($request->user()->id)
            ->ofType('expense')
            ->orderBy('name')
            ->get();

        return view('budgets.index', [
            'overview' => $overview,
            'period' => $period,
            'prevMonth' => $period->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $period->copy()->addMonth()->format('Y-m'),
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * Store or update a monthly category budget.
     */
    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->budgetService->upsertBudget($request->user(), $validated);

        return redirect()
            ->route('budgets.index', ['month' => $validated['month_year']])
            ->with('status', 'Anggaran bulanan berhasil disimpan.');
    }

    /**
     * Delete the specified monthly budget.
     */
    public function destroy(Request $request, Budget $budget): RedirectResponse
    {
        abort_if($budget->user_id !== $request->user()->id, 403);

        $monthYear = $budget->month_year;
        $this->budgetService->deleteBudget($budget);

        return redirect()
            ->route('budgets.index', ['month' => $monthYear])
            ->with('status', 'Anggaran bulanan berhasil dihapus.');
    }
}
