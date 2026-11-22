<?php

namespace App\Http\Controllers;

use App\Services\FinancialAdvisorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvisorController extends Controller
{
    public function __construct(
        private readonly FinancialAdvisorService $advisorService
    ) {}

    /**
     * Display the AI Financial Advisor interactive chat screen.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $messages = session('advisor_chat', []);

        if (empty($messages)) {
            $messages = [
                [
                    'role' => 'model',
                    'text' => "Halo! Saya **Si Jamal AI Advisor** 🤖\nSaya sudah menganalisis saldo dompet, pos pengeluaran bulan ini, dan impian tabunganmu. Ada yang ingin kamu konsultasikan atau evaluasi hari ini?",
                    'time' => now()->format('H:i'),
                ],
            ];
            session(['advisor_chat' => $messages]);
        }

        $context = $this->advisorService->compileUserFinancialContext($user);

        return view('advisor.index', [
            'messages' => $messages,
            'context' => $context,
        ]);
    }

    /**
     * Send user message to AI Advisor and obtain personalized response.
     */
    public function ask(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $userText = trim($validated['message']);
        $time = now()->format('H:i');

        $messages = session('advisor_chat', []);

        $messages[] = [
            'role' => 'user',
            'text' => $userText,
            'time' => $time,
        ];

        $replyText = $this->advisorService->generateAdvice($user, $userText, $messages);

        $messages[] = [
            'role' => 'model',
            'text' => $replyText,
            'time' => now()->format('H:i'),
        ];

        session(['advisor_chat' => $messages]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'user_message' => $userText,
                'reply' => $replyText,
                'time' => now()->format('H:i'),
            ]);
        }

        return redirect()->route('advisor.index');
    }

    /**
     * Clear active chat session history.
     */
    public function reset(): RedirectResponse
    {
        session()->forget('advisor_chat');

        return redirect()->route('advisor.index')->with('success', 'Riwayat konsultasi AI berhasil direset.');
    }
}
