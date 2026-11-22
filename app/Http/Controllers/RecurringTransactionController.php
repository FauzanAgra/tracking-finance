<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Wallet;
use App\Services\RecurringTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringTransactionController extends Controller
{
    public function __construct(
        private readonly RecurringTransactionService $recurringService,
    ) {}

    /**
     * Display a listing of the user's recurring transactions.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $recurringTransactions = RecurringTransaction::query()
            ->forUser($user->id)
            ->with(['wallet', 'destinationWallet', 'category'])
            ->orderBy('is_active', 'desc')
            ->orderBy('next_run_date', 'asc')
            ->get();

        $wallets = Wallet::query()->forUser($user->id)->active()->orderByDesc('is_default')->get();
        $categories = Category::query()->forUser($user->id)->orderBy('name')->get();

        $totalActive = $recurringTransactions->where('is_active', true)->count();
        $monthlyEstimate = (float) $recurringTransactions->where('is_active', true)->where('type', 'expense')->sum(function (RecurringTransaction $r) {
            return match ($r->frequency) {
                'daily' => $r->amount * 30,
                'weekly' => $r->amount * 4,
                'monthly' => $r->amount,
                default => $r->amount,
            };
        });

        return view('recurring.index', [
            'recurringTransactions' => $recurringTransactions,
            'wallets' => $wallets,
            'categories' => $categories,
            'expenseCategories' => $categories->where('type', 'expense')->values(),
            'incomeCategories' => $categories->where('type', 'income')->values(),
            'totalActive' => $totalActive,
            'monthlyEstimate' => $monthlyEstimate,
        ]);
    }

    /**
     * Store a newly created recurring transaction template.
     */
    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        $this->recurringService->create($request->user(), $request->validated());

        return redirect()
            ->route('recurring.index')
            ->with('status', 'Jadwal transaksi rutin berhasil ditambahkan.');
    }

    /**
     * Toggle the active status of the specified recurring transaction.
     */
    public function toggle(Request $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);

        $updated = $this->recurringService->toggleActive($recurringTransaction);
        $statusMsg = $updated->is_active ? 'Transaksi rutin diaktifkan.' : 'Transaksi rutin dinonaktifkan sementara.';

        return redirect()
            ->route('recurring.index')
            ->with('status', $statusMsg);
    }

    /**
     * Remove the specified recurring transaction template.
     */
    public function destroy(Request $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);

        $this->recurringService->delete($recurringTransaction);

        return redirect()
            ->route('recurring.index')
            ->with('status', 'Jadwal transaksi rutin berhasil dihapus.');
    }
}
