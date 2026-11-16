<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvestmentTradeRequest;
use App\Http\Requests\UpdateManualPriceRequest;
use App\Models\InvestmentAsset;
use App\Models\Wallet;
use App\Services\InvestmentService;
use App\Services\MarketPriceService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function __construct(
        private readonly InvestmentService $investmentService,
        private readonly MarketPriceService $marketPriceService,
    ) {}

    /**
     * Display the multi-asset investment portfolio and floating PnL.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $summary = $this->investmentService->getPortfolioSummary($user);

        $wallets = Wallet::query()
            ->forUser($user->id)
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('investments.index', [
            'summary' => $summary,
            'wallets' => $wallets,
        ]);
    }

    /**
     * Record a buy, sell, or dividend investment trade.
     */
    public function storeTrade(StoreInvestmentTradeRequest $request): RedirectResponse
    {
        try {
            $this->investmentService->recordTrade($request->user(), $request->validated());
        } catch (DomainException $e) {
            return redirect()
                ->route('investments.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('investments.index')
            ->with('status', 'Transaksi investasi berhasil dicatat.');
    }

    /**
     * Refresh market prices for the authenticated user's assets via external APIs.
     */
    public function refreshPrices(Request $request): RedirectResponse
    {
        $result = $this->marketPriceService->refreshAll($request->user());

        return redirect()
            ->route('investments.index')
            ->with('status', "Harga diperbarui: {$result['updated']} aset berhasil, {$result['failed']} gagal, {$result['skipped_manual']} manual dilewati.");
    }

    /**
     * Manually override an asset's current market price (API fallback).
     */
    public function updateManualPrice(UpdateManualPriceRequest $request, InvestmentAsset $asset): RedirectResponse
    {
        abort_if($asset->user_id !== $request->user()->id, 403);

        try {
            $this->investmentService->updateManualPrice($asset, (float) $request->validated('current_price'));
        } catch (DomainException $e) {
            return redirect()
                ->route('investments.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('investments.index')
            ->with('status', "Harga manual {$asset->symbol} berhasil diperbarui.");
    }
}
