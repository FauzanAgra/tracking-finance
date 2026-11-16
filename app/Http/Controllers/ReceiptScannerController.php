<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmReceiptScanRequest;
use App\Http\Requests\UploadReceiptRequest;
use App\Models\Category;
use App\Models\ReceiptScan;
use App\Models\Wallet;
use App\Services\GeminiReceiptService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptScannerController extends Controller
{
    public function __construct(
        private readonly GeminiReceiptService $geminiReceiptService,
    ) {}

    /**
     * Display receipt upload UI and recent AI scan history.
     */
    public function index(Request $request): View
    {
        $recentScans = ReceiptScan::query()
            ->forUser($request->user()->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('receipts.index', [
            'recentScans' => $recentScans,
        ]);
    }

    /**
     * Upload and scan a receipt image using Gemini Vision API.
     */
    public function store(UploadReceiptRequest $request): RedirectResponse
    {
        try {
            $scan = $this->geminiReceiptService->scanReceipt(
                $request->user(),
                $request->file('receipt'),
            );
        } catch (DomainException $e) {
            return redirect()
                ->route('receipts.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('receipts.show', $scan)
            ->with('status', 'Struk berhasil dipindai oleh AI. Silakan tinjau sebelum disimpan.');
    }

    /**
     * Show extracted receipt data for user verification and confirmation.
     */
    public function show(Request $request, ReceiptScan $receiptScan): View
    {
        abort_if($receiptScan->user_id !== $request->user()->id, 403);

        $userId = $request->user()->id;
        $wallets = Wallet::query()
            ->forUser($userId)
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $defaultWallet = $wallets->firstWhere('is_default', true) ?? $wallets->first();

        $expenseCategories = Category::query()
            ->forUser($userId)
            ->ofType('expense')
            ->orderBy('name')
            ->get();

        $extracted = $receiptScan->raw_gemini_response ?? [];
        $suggestedName = strtolower((string) ($extracted['suggested_category'] ?? ''));
        $matchedCategory = $expenseCategories->first(
            fn (Category $cat) => $suggestedName !== '' && str_contains(strtolower($cat->name), $suggestedName)
        ) ?? $expenseCategories->first();

        return view('receipts.verify', [
            'scan' => $receiptScan,
            'extracted' => $extracted,
            'wallets' => $wallets,
            'defaultWallet' => $defaultWallet,
            'expenseCategories' => $expenseCategories,
            'matchedCategory' => $matchedCategory,
        ]);
    }

    /**
     * Confirm scanned receipt data and save it as an expense transaction.
     */
    public function confirm(ConfirmReceiptScanRequest $request, ReceiptScan $receiptScan): RedirectResponse
    {
        abort_if($receiptScan->user_id !== $request->user()->id, 403);

        try {
            $this->geminiReceiptService->confirmAndCreateTransaction(
                $receiptScan,
                $request->user(),
                $request->validated(),
            );
        } catch (DomainException $e) {
            return redirect()
                ->route('receipts.show', $receiptScan)
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi dari struk AI berhasil disimpan!');
    }
}
