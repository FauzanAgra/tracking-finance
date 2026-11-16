<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\TransactionService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    /**
     * Display filtered transaction history and quick-entry modal context.
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $search = trim((string) $request->query('search', ''));
        $type = (string) $request->query('type', 'all');
        $walletId = $request->query('wallet_id');
        $categoryId = $request->query('category_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = Transaction::query()
            ->forUser($userId)
            ->with(['wallet', 'destinationWallet', 'category'])
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($sub) use ($search): void {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($cat) => $cat->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('wallet', fn ($w) => $w->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($type, ['income', 'expense', 'transfer', 'currency_exchange'], true), fn ($q) => $q->where('type', $type))
            ->when($walletId, fn ($q) => $q->where(function ($sub) use ($walletId): void {
                $sub->where('wallet_id', $walletId)
                    ->orWhere('destination_wallet_id', $walletId);
            }))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($dateFrom, fn ($q) => $q->whereDate('transaction_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('transaction_date', '<=', $dateTo))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        $transactions = $query->get();

        $totalIncome = (float) $transactions->where('type', 'income')->sum('amount');
        $totalExpense = (float) $transactions->where('type', 'expense')->sum('amount');
        $incomeCount = $transactions->where('type', 'income')->count();
        $expenseCount = $transactions->where('type', 'expense')->count();

        $groupedTransactions = $transactions->groupBy(fn (Transaction $t) => $t->transaction_date->format('Y-m-d'));

        $wallets = Wallet::query()
            ->forUser($userId)
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $defaultWallet = $wallets->firstWhere('is_default', true) ?? $wallets->first();

        $categories = Category::query()
            ->forUser($userId)
            ->orderBy('name')
            ->get();

        return view('transactions.index', [
            'groupedTransactions' => $groupedTransactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'incomeCount' => $incomeCount,
            'expenseCount' => $expenseCount,
            'wallets' => $wallets,
            'defaultWallet' => $defaultWallet,
            'categories' => $categories,
            'expenseCategories' => $categories->where('type', 'expense')->values(),
            'incomeCategories' => $categories->where('type', 'income')->values(),
            'filters' => [
                'search' => $search,
                'type' => $type,
                'wallet_id' => $walletId,
                'category_id' => $categoryId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'openQuickModal' => $request->boolean('quick') || $request->session()->has('errors') || $request->session()->has('error'),
        ]);
    }

    /**
     * Store a newly recorded income, expense, or same-currency transfer transaction.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        try {
            match ($validated['type']) {
                'income' => $this->transactionService->recordIncome($user, $validated),
                'expense' => $this->transactionService->recordExpense($user, $validated),
                'transfer' => $this->transactionService->recordTransfer($user, $validated),
                'currency_exchange' => $this->transactionService->recordCurrencyExchange($user, $validated),
            };
        } catch (DomainException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi berhasil dicatat.');
    }

    /**
     * Delete a transaction and reverse its wallet balance mutation.
     */
    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        try {
            $this->transactionService->deleteTransaction($transaction);
        } catch (DomainException $e) {
            return redirect()
                ->route('transactions.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi dihapus dan saldo dompet dikembalikan.');
    }
}
