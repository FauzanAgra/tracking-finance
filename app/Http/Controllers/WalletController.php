<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Models\Wallet;
use App\Services\WalletService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService,
    ) {}

    /**
     * Display the user's wallets grouped and summarized by currency.
     */
    public function index(Request $request): View
    {
        $wallets = $request->user()
            ->wallets()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        $currencyTotals = [
            'IDR' => (float) $wallets->where('currency', 'IDR')->sum('balance'),
            'USD' => (float) $wallets->where('currency', 'USD')->sum('balance'),
            'USDT' => (float) $wallets->where('currency', 'USDT')->sum('balance'),
        ];

        $currencyCounts = [
            'ALL' => $wallets->count(),
            'IDR' => $wallets->where('currency', 'IDR')->count(),
            'USD' => $wallets->where('currency', 'USD')->count(),
            'USDT' => $wallets->where('currency', 'USDT')->count(),
        ];

        return view('wallets.index', [
            'wallets' => $wallets,
            'currencyTotals' => $currencyTotals,
            'currencyCounts' => $currencyCounts,
        ]);
    }

    /**
     * Store a newly created wallet.
     */
    public function store(StoreWalletRequest $request): RedirectResponse
    {
        $this->walletService->createWallet($request->user(), $request->validated());

        return redirect()->route('wallets.index')->with('status', 'Dompet baru berhasil ditambahkan.');
    }

    /**
     * Update the specified wallet.
     */
    public function update(UpdateWalletRequest $request, Wallet $wallet): RedirectResponse
    {
        abort_if($wallet->user_id !== $request->user()->id, 403);

        $this->walletService->updateWallet($wallet, $request->validated());

        return redirect()->route('wallets.index')->with('status', 'Data dompet berhasil diperbarui.');
    }

    /**
     * Set the specified wallet as the user's default wallet.
     */
    public function setDefault(Request $request, Wallet $wallet): RedirectResponse
    {
        abort_if($wallet->user_id !== $request->user()->id, 403);

        $this->walletService->setDefaultWallet($wallet);

        return redirect()->route('wallets.index')->with('status', "{$wallet->name} dijadikan dompet utama.");
    }

    /**
     * Remove the specified wallet if it has no linked records.
     */
    public function destroy(Request $request, Wallet $wallet): RedirectResponse
    {
        abort_if($wallet->user_id !== $request->user()->id, 403);

        try {
            $this->walletService->deleteWallet($wallet);
        } catch (DomainException $e) {
            return redirect()->route('wallets.index')->with('error', $e->getMessage());
        }

        return redirect()->route('wallets.index')->with('status', 'Dompet berhasil dihapus.');
    }
}
