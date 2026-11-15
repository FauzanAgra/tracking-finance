<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtPaymentRequest;
use App\Http\Requests\StoreDebtRequest;
use App\Models\Debt;
use App\Models\Wallet;
use App\Services\DebtService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebtController extends Controller
{
    public function __construct(
        private readonly DebtService $debtService,
    ) {}

    /**
     * Display the user's debts (payables) and receivables with installment logs.
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $debts = Debt::query()
            ->forUser($userId)
            ->with(['payments' => fn ($q) => $q->with('wallet')->orderByDesc('payment_date')->orderByDesc('id')])
            ->orderByRaw("CASE status WHEN 'unpaid' THEN 1 WHEN 'partially_paid' THEN 2 WHEN 'paid' THEN 3 WHEN 'written_off' THEN 4 ELSE 5 END")
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->get();

        $activeReceivables = $debts->where('type', 'receivable')->whereIn('status', ['unpaid', 'partially_paid']);
        $activePayables = $debts->where('type', 'payable')->whereIn('status', ['unpaid', 'partially_paid']);

        $totalReceivableRemaining = $activeReceivables->sum(
            fn (Debt $d): float => max(0, (float) $d->total_amount - (float) $d->payments->sum('amount') - (float) $d->written_off_amount)
        );

        $totalPayableRemaining = $activePayables->sum(
            fn (Debt $d): float => max(0, (float) $d->total_amount - (float) $d->payments->sum('amount') - (float) $d->written_off_amount)
        );

        $wallets = Wallet::query()
            ->forUser($userId)
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $defaultWallet = $wallets->firstWhere('is_default', true) ?? $wallets->first();

        return view('debts.index', [
            'debts' => $debts,
            'totalReceivableRemaining' => $totalReceivableRemaining,
            'totalPayableRemaining' => $totalPayableRemaining,
            'activeReceivableCount' => $activeReceivables->count(),
            'activePayableCount' => $activePayables->count(),
            'wallets' => $wallets,
            'defaultWallet' => $defaultWallet,
        ]);
    }

    /**
     * Store a newly created debt or receivable record.
     */
    public function store(StoreDebtRequest $request): RedirectResponse
    {
        $this->debtService->createDebt($request->user(), $request->validated());

        return redirect()
            ->route('debts.index')
            ->with('status', 'Catatan utang/piutang berhasil ditambahkan.');
    }

    /**
     * Record an installment payment for the given debt or receivable.
     */
    public function storePayment(StoreDebtPaymentRequest $request, Debt $debt): RedirectResponse
    {
        abort_if($debt->user_id !== $request->user()->id, 403);

        try {
            $this->debtService->recordPayment($debt, $request->validated());
        } catch (DomainException $e) {
            return redirect()
                ->route('debts.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('debts.index')
            ->with('status', 'Cicilan berhasil dicatat dan saldo dompet diperbarui.');
    }

    /**
     * Write off the remaining balance of a debt or receivable cleanly.
     */
    public function writeOff(Request $request, Debt $debt): RedirectResponse
    {
        abort_if($debt->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'writeoff_notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->debtService->writeOffDebt($debt, $validated['writeoff_notes'] ?? null);
        } catch (DomainException $e) {
            return redirect()
                ->route('debts.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('debts.index')
            ->with('status', 'Sisa pinjaman berhasil diikhlaskan (written off).');
    }

    /**
     * Delete a debt record that has no installment history.
     */
    public function destroy(Request $request, Debt $debt): RedirectResponse
    {
        abort_if($debt->user_id !== $request->user()->id, 403);

        try {
            $this->debtService->deleteDebt($debt);
        } catch (DomainException $e) {
            return redirect()
                ->route('debts.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('debts.index')
            ->with('status', 'Catatan utang/piutang berhasil dihapus.');
    }
}
