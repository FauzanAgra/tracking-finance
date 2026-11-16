<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGoalContributionRequest;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Models\Goal;
use App\Models\Wallet;
use App\Services\GoalService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function __construct(
        private readonly GoalService $goalService,
    ) {}

    /**
     * Display financial goals with progress bars, smart estimates, and contribution modals.
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $goals = Goal::query()
            ->forUser($userId)
            ->with(['contributions' => fn ($q) => $q->with('wallet')->orderByDesc('contribution_date')->orderByDesc('id')])
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 1 WHEN 'achieved' THEN 2 WHEN 'cancelled' THEN 3 ELSE 4 END")
            ->orderByDesc('id')
            ->get();

        $goalCards = $goals->map(fn (Goal $goal) => [
            'goal' => $goal,
            'insights' => $this->goalService->computeGoalInsights($goal),
        ]);

        $wallets = Wallet::query()
            ->forUser($userId)
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $defaultWallet = $wallets->firstWhere('is_default', true) ?? $wallets->first();

        $achievedGoals = $goals->where('status', 'achieved');
        $inProgressGoals = $goals->where('status', 'in_progress');

        return view('goals.index', [
            'goalCards' => $goalCards,
            'totalSavedIdr' => (float) $goals->where('currency', 'IDR')->sum('current_amount'),
            'achievedCount' => $achievedGoals->count(),
            'achievedSavedIdr' => (float) $achievedGoals->where('currency', 'IDR')->sum('current_amount'),
            'inProgressCount' => $inProgressGoals->count(),
            'inProgressSavedIdr' => (float) $inProgressGoals->where('currency', 'IDR')->sum('current_amount'),
            'wallets' => $wallets,
            'defaultWallet' => $defaultWallet,
        ]);
    }

    /**
     * Store a newly created financial goal.
     */
    public function store(StoreGoalRequest $request): RedirectResponse
    {
        $this->goalService->createGoal($request->user(), $request->validated());

        return redirect()
            ->route('goals.index')
            ->with('status', 'Target tabungan baru berhasil dibuat.');
    }

    /**
     * Update an existing financial goal.
     */
    public function update(UpdateGoalRequest $request, Goal $goal): RedirectResponse
    {
        abort_if($goal->user_id !== $request->user()->id, 403);

        $this->goalService->updateGoal($goal, $request->validated());

        return redirect()
            ->route('goals.index')
            ->with('status', 'Target tabungan berhasil diperbarui.');
    }

    /**
     * Allocate funds from a wallet into a goal vault.
     */
    public function storeContribution(StoreGoalContributionRequest $request, Goal $goal): RedirectResponse
    {
        abort_if($goal->user_id !== $request->user()->id, 403);

        try {
            $this->goalService->contribute($goal, $request->validated());
        } catch (DomainException $e) {
            return redirect()
                ->route('goals.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('goals.index')
            ->with('status', 'Dana berhasil disisihkan ke brankas goal.');
    }

    /**
     * Delete a goal if it has no contribution history.
     */
    public function destroy(Request $request, Goal $goal): RedirectResponse
    {
        abort_if($goal->user_id !== $request->user()->id, 403);

        try {
            $this->goalService->deleteGoal($goal);
        } catch (DomainException $e) {
            return redirect()
                ->route('goals.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('goals.index')
            ->with('status', 'Target tabungan berhasil dihapus.');
    }
}
