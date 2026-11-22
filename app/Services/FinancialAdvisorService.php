<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Http;
use Throwable;

class FinancialAdvisorService
{
    /**
     * Generate financial advice or forecast using Gemini API with contextual user financial data.
     *
     * @param array<int, array{role: string, text: string}> $history
     */
    public function generateAdvice(User $user, string $userMessage, array $history = []): string
    {
        $context = $this->compileUserFinancialContext($user);

        $apiKey = (string) config('services.gemini.api_key', '');
        if ($apiKey === '') {
            return $this->generateLocalFallbackAdvice($context, $userMessage);
        }

        $systemPrompt = "Kamu adalah 'Si Jamal AI Advisor', asisten penasihat keuangan pribadi yang cerdas, ramah, jujur, dan solutif pada aplikasi Tracking Finance.\n\n"
            . "DATA KEUANGAN PENGGUNA SAAT INI (" . now()->translatedFormat('F Y') . "):\n"
            . "- Mata Uang Utama: {$context['currency']}\n"
            . "- Total Saldo Seluruh Dompet: Rp " . number_format($context['total_balance'], 0, ',', '.') . " ({$context['wallet_count']} akun dompet)\n"
            . "- Total Pemasukan Bulan Ini: Rp " . number_format($context['income_this_month'], 0, ',', '.') . "\n"
            . "- Total Pengeluaran Bulan Ini: Rp " . number_format($context['expense_this_month'], 0, ',', '.') . "\n"
            . "- Arus Kas Bersih (Net Cashflow): Rp " . number_format($context['net_cashflow'], 0, ',', '.') . " (" . ($context['net_cashflow'] >= 0 ? 'Surplus' : 'Defisit') . ")\n"
            . "- Pos Pengeluaran Terbesar: " . ($context['top_categories_str'] ?: 'Belum ada data pengeluaran') . "\n"
            . "- Target Tabungan Impian: " . ($context['goals_str'] ?: 'Belum ada target impian aktif') . "\n"
            . "- Kewajiban Utang / Piutang: " . ($context['debts_str'] ?: 'Tidak ada utang aktif') . "\n\n"
            . "PANDUAN JAWABAN:\n"
            . "1. Gunakan bahasa Indonesia santun, bersahabat, dan memotivasi.\n"
            . "2. Integrasikan data angka riil pengguna di atas agar jawaban terasa sangat personal dan akurat.\n"
            . "3. Berikan saran aksi terukur (1 sampai 3 langkah konkret yang bisa langsung diterapkan).\n"
            . "4. Jika pengguna bertanya proyeksi masa depan, hitung estimasi berdasarkan rata-rata tabungan bersih bulanan saat ini.\n"
            . "5. Format jawaban dengan poin-poin tebal yang ringkas dan mudah dibaca di layar HP.";

        $candidateModels = array_values(array_unique([
            (string) config('services.gemini.model', 'gemini-2.5-flash'),
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
        ]));

        $contents = [];
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]],
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'Halo! Saya Si Jamal AI Advisor siap membantu menganalisis dan merencanakan keuanganmu. Ada yang ingin kamu diskusikan?']],
        ];

        // Append recent chat history (max 6 items)
        $slicedHistory = array_slice($history, -6);
        foreach ($slicedHistory as $item) {
            $role = ($item['role'] ?? 'user') === 'user' ? 'user' : 'model';
            $text = trim((string) ($item['text'] ?? ''));
            if ($text !== '') {
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $text]],
                ];
            }
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
            ],
        ];

        try {
            foreach ($candidateModels as $model) {
                try {
                    $response = Http::withOptions(['force_ip_resolve' => 'v4'])
                        ->timeout(20)
                        ->withHeaders([
                            'x-goog-api-key' => $apiKey,
                            'Content-Type' => 'application/json',
                        ])
                        ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", $payload);

                    if ($response->successful()) {
                        $replyText = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
                        if (trim($replyText) !== '') {
                            return trim($replyText);
                        }
                    }
                } catch (Throwable) {
                    continue;
                }
            }
        } catch (Throwable) {
            // fallback gracefully
        }

        return $this->generateLocalFallbackAdvice($context, $userMessage);
    }

    /**
     * Compile structured financial summary for the user to inject into Gemini context.
     *
     * @return array<string, mixed>
     */
    public function compileUserFinancialContext(User $user): array
    {
        $wallets = Wallet::query()->where('user_id', $user->id)->get();
        $totalBalance = (float) $wallets->sum('balance');

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $income = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $expense = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $netCashflow = $income - $expense;

        $topCategories = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->with('category')
            ->get()
            ->groupBy(fn ($t) => $t->category?->name ?? 'Lain-lain')
            ->map(fn ($g, $name) => [
                'name' => $name,
                'total' => (float) $g->sum('amount'),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $topCategoriesStr = $topCategories->map(fn ($c) => "{$c['name']}: Rp " . number_format($c['total'], 0, ',', '.'))->implode(', ');

        $goals = Goal::query()
            ->where('user_id', $user->id)
            ->get()
            ->map(fn ($g) => "{$g->title} (Terkumpul Rp " . number_format((float) $g->current_amount, 0, ',', '.') . " / Rp " . number_format((float) $g->target_amount, 0, ',', '.') . ")")
            ->implode('; ');

        $debts = Debt::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['paid', 'written_off'])
            ->get()
            ->map(fn ($d) => ($d->type === 'payable' ? 'Utang ke ' : 'Piutang dari ') . "{$d->person_name} (Rp " . number_format((float) $d->total_amount, 0, ',', '.') . ")")
            ->implode('; ');

        return [
            'currency' => $user->base_currency ?? 'IDR',
            'wallet_count' => $wallets->count(),
            'total_balance' => $totalBalance,
            'income_this_month' => $income,
            'expense_this_month' => $expense,
            'net_cashflow' => $netCashflow,
            'top_categories' => $topCategories->toArray(),
            'top_categories_str' => $topCategoriesStr,
            'goals_str' => $goals,
            'debts_str' => $debts,
        ];
    }

    /**
     * Provide a smart, localized rule-based response when external Gemini API is unreachable or unconfigured.
     */
    private function generateLocalFallbackAdvice(array $ctx, string $message): string
    {
        $lower = mb_strtolower($message);
        $currency = $ctx['currency'];
        $formattedBalance = number_format($ctx['total_balance'], 0, ',', '.');
        $formattedIncome = number_format($ctx['income_this_month'], 0, ',', '.');
        $formattedExpense = number_format($ctx['expense_this_month'], 0, ',', '.');
        $formattedNet = number_format(abs($ctx['net_cashflow']), 0, ',', '.');

        if (str_contains($lower, 'boros') || str_contains($lower, 'pengeluaran') || str_contains($lower, 'pos')) {
            $catList = $ctx['top_categories_str'] ?: 'Belum ada catatan pengeluaran bulan ini';

            return "📊 **Analisis Pos Pengeluaran Terbesar Bulan Ini:**\n\n"
                . "Total belanja tercatat sebesar **Rp {$formattedExpense}**.\n\n"
                . "🔍 **Top Kategori Belanjamu:**\n{$catList}\n\n"
                . "💡 **Saran Tindakan:**\n"
                . "1. Tinjau kembali kategori teratas apakah masih dalam batas kebutuhan pokok atau keinginan sesaat.\n"
                . "2. Buat limit anggaran bulanan di menu **Budgeting** agar mendapat pengingat otomatis saat kuota sisa 20%.\n"
                . "3. Alokasikan minimal 10-20% dari pemasukan ke rekening tabungan terpisah di awal bulan.";
        }

        if (str_contains($lower, 'kesehatan') || str_contains($lower, 'arus kas') || str_contains($lower, 'cashflow')) {
            $statusText = $ctx['net_cashflow'] >= 0 ? "Surplus Keuangan (+Rp {$formattedNet})" : "Defisit Keuangan (-Rp {$formattedNet})";

            return "🩺 **Laporan Diagnosa Kesehatan Keuanganmu:**\n\n"
                . "- **Status Arus Kas:** {$statusText}\n"
                . "- **Total Pemasukan:** Rp {$formattedIncome}\n"
                . "- **Total Pengeluaran:** Rp {$formattedExpense}\n"
                . "- **Total Saldo Dompet:** Rp {$formattedBalance} ({$ctx['wallet_count']} dompet aktif)\n\n"
                . ($ctx['net_cashflow'] >= 0
                    ? "🎉 **Kondisi Baik!** Arus kasmu positif bulan ini. Pertahankan pola hidup ini dan pertimbangkan menyalurkan sisa surplus ke instrumen investasi atau target impian."
                    : "⚠️ **Perhatian!** Pengeluaranmu bulan ini melebihi pemasukan. Prioritaskan penghematan pada pos non-esensial untuk mencegah pengikisan tabungan.");
        }

        if (str_contains($lower, 'impian') || str_contains($lower, 'tabungan') || str_contains($lower, 'target') || str_contains($lower, 'proyeksi')) {
            $goals = $ctx['goals_str'] ?: 'Kamu belum mendaftarkan target impian aktif.';
            $monthlySaving = max(0, $ctx['net_cashflow']);
            $sixMonthProjection = number_format($ctx['total_balance'] + ($monthlySaving * 6), 0, ',', '.');

            return "🎯 **Rencana Pencapaian Target & Proyeksi 6 Bulan:**\n\n"
                . "- **Target Aktif:** {$goals}\n"
                . "- **Estimasi Tabungan Bulanan:** Rp " . number_format($monthlySaving, 0, ',', '.') . "/bulan\n"
                . "- **Proyeksi Saldo 6 Bulan ke Depan:** Menjadi sekitar **Rp {$sixMonthProjection}** (dengan asumsi konsistensi arus kas saat ini).\n\n"
                . "🚀 **Langkah Nyata:**\n"
                . "1. Sisihkan tabungan di awal bulan begitu gaji masuk (*Pay Yourself First*).\n"
                . "2. Gunakan fitur *Goal Contribution* untuk mendokumentasikan setiap uang yang kamu tabung.";
        }

        return "👋 **Halo! Saya Si Jamal AI Advisor.**\n\n"
            . "Ringkasan kondisi finansialmu saat ini:\n"
            . "• Saldo Dompet: **Rp {$formattedBalance}**\n"
            . "• Pemasukan Bulan Ini: **Rp {$formattedIncome}**\n"
            . "• Pengeluaran Bulan Ini: **Rp {$formattedExpense}**\n"
            . "• Arus Kas Bersih: **" . ($ctx['net_cashflow'] >= 0 ? '+' : '-') . "Rp {$formattedNet}**\n\n"
            . "Ada yang bisa saya bantu analisis hari ini? Kamu bisa menanyakan pos yang paling boros, simulasi tabungan, atau proyeksi keuangan ke depan!";
    }
}
