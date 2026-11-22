<x-layouts.mobile title="Beranda" activeNav="dashboard">
    @php
        $user = auth()->user();
        $initials = collect(explode(' ', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
        $budgetPct = $budget_overview['overall_percentage'];
    @endphp

    <div class="flex-1 flex flex-col">
        <!-- Sticky Top App Bar -->
        <header class="sticky top-0 z-30 flex items-center justify-between px-gutter py-space-sm bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md border-b border-border-subtle/50 dark:border-dark-border/40">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="relative shrink-0">
                    <div class="w-10 h-10 rounded-full bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime flex items-center justify-center font-bold text-sm border border-border-subtle dark:border-dark-border">
                        {{ $initials ?: 'U' }}
                    </div>
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-accent-lime rounded-full border-2 border-canvas-base dark:border-dark-canvas"></span>
                </div>
                <div class="min-w-0">
                    <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary truncate leading-tight">
                        Halo, {{ explode(' ', $user->name)[0] }}
                    </h1>
                    <p class="text-label-xs text-text-muted dark:text-dark-text-secondary truncate">
                        1 USD &approx; Rp {{ number_format($usd_idr_rate, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                <a
                    href="{{ route('notifications.index') }}"
                    aria-label="Pusat Notifikasi"
                    title="Notifikasi"
                    class="relative w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">notifications</span>
                    @if(auth()->user()->unreadNotifications()->count() > 0)
                        <span class="absolute -top-0.5 -right-0.5 min-w-4 h-4 px-1 rounded-full bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center ring-2 ring-canvas-base dark:ring-dark-canvas">
                            {{ auth()->user()->unreadNotifications()->count() > 9 ? '9+' : auth()->user()->unreadNotifications()->count() }}
                        </span>
                    @endif
                </a>
                <a
                    href="{{ route('receipts.index') }}"
                    aria-label="Scan Struk AI"
                    title="Scan Struk AI"
                    class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">photo_camera</span>
                </a>
            </div>
        </header>

        <div class="px-gutter py-space-md space-y-4">
            <!-- 1. HERO NET WORTH BENTO CARD (DASH-001) -->
            <section class="bg-forest-primary dark:bg-dark-surface rounded-3xl p-5 text-white dark:text-dark-text-primary border border-forest-primary dark:border-dark-border shadow-md relative overflow-hidden space-y-3.5">
                <div class="absolute -right-10 -bottom-10 w-36 h-36 rounded-full bg-white/10 dark:bg-dark-surface-tint pointer-events-none"></div>

                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-1.5 text-white/80 dark:text-dark-text-secondary">
                        <span class="material-symbols-outlined text-[18px] text-accent-lime dark:text-dark-lime">account_balance</span>
                        <span class="text-label-md font-semibold">Total Net Worth</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-white/15 dark:bg-dark-surface-tint text-accent-lime dark:text-dark-lime text-label-xs font-semibold">
                        IDR Basis
                    </span>
                </div>

                <div class="relative z-10">
                    <h2 class="text-headline-lg-mobile font-bold tabular-nums tracking-tight">
                        Rp {{ number_format($net_worth_idr, 0, ',', '.') }}
                    </h2>
                    <div class="flex flex-wrap gap-x-3 gap-y-1 mt-1.5 text-[11px] text-white/75 dark:text-dark-text-secondary tabular-nums">
                        <span>Kas: Rp {{ number_format($total_cash_idr, 0, ',', '.') }}</span>
                        <span>Aset: Rp {{ number_format($total_investment_idr, 0, ',', '.') }}</span>
                        <span>Piutang: Rp {{ number_format($total_receivable_idr, 0, ',', '.') }}</span>
                        @if ($total_payable_idr > 0)
                            <span class="text-rose-200 dark:text-[#FDA4AF]">Utang: -Rp {{ number_format($total_payable_idr, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5 pt-3 border-t border-white/15 dark:border-dark-border relative z-10">
                    <div class="bg-white/10 dark:bg-dark-surface-tint rounded-xl p-2.5">
                        <div class="flex items-center gap-1 text-[11px] text-white/75 dark:text-dark-text-secondary mb-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-accent-lime"></span>
                            <span>Masuk Bln Ini</span>
                        </div>
                        <span class="text-label-lg font-bold tabular-nums">Rp {{ number_format($month_income, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-white/10 dark:bg-dark-surface-tint rounded-xl p-2.5">
                        <div class="flex items-center gap-1 text-[11px] text-white/75 dark:text-dark-text-secondary mb-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-300"></span>
                            <span>Keluar Bln Ini</span>
                        </div>
                        <span class="text-label-lg font-bold tabular-nums">Rp {{ number_format($month_expense, 0, ',', '.') }}</span>
                    </div>
                </div>
            </section>

            <!-- Quick Shortcuts Strip -->
            <section aria-label="Menu Cepat" class="grid grid-cols-4 gap-2">
                <a href="{{ route('investments.index') }}" class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-2.5 flex flex-col items-center gap-1 text-center hover:border-forest-primary dark:hover:border-dark-lime transition-colors">
                    <span class="material-symbols-outlined text-[20px] text-forest-primary dark:text-dark-lime">trending_up</span>
                    <span class="text-[11px] font-semibold text-text-primary dark:text-dark-text-primary">Portofolio</span>
                </a>
                <a href="{{ route('debts.index') }}" class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-2.5 flex flex-col items-center gap-1 text-center hover:border-forest-primary dark:hover:border-dark-lime transition-colors">
                    <span class="material-symbols-outlined text-[20px] text-forest-primary dark:text-dark-lime">handshake</span>
                    <span class="text-[11px] font-semibold text-text-primary dark:text-dark-text-primary">Utang</span>
                </a>
                <a href="{{ route('budgets.index') }}" class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-2.5 flex flex-col items-center gap-1 text-center hover:border-forest-primary dark:hover:border-dark-lime transition-colors">
                    <span class="material-symbols-outlined text-[20px] text-forest-primary dark:text-dark-lime">pie_chart</span>
                    <span class="text-[11px] font-semibold text-text-primary dark:text-dark-text-primary">Budget</span>
                </a>
                <a href="{{ route('goals.index') }}" class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-2.5 flex flex-col items-center gap-1 text-center hover:border-forest-primary dark:hover:border-dark-lime transition-colors">
                    <span class="material-symbols-outlined text-[20px] text-forest-primary dark:text-dark-lime">savings</span>
                    <span class="text-[11px] font-semibold text-text-primary dark:text-dark-text-primary">Goals</span>
                </a>
            </section>

            <!-- 2. BUDGET HEALTH BAR -->
            @if ($budget_overview['total_budget'] > 0)
                <section class="bg-surface-card dark:bg-dark-surface rounded-2xl p-4 border border-border-subtle dark:border-dark-border space-y-2 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-label-xs text-text-muted dark:text-dark-text-secondary block">Kesehatan Budget Bulan Ini</span>
                            <span class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">
                                Rp {{ number_format($budget_overview['total_spent'], 0, ',', '.') }} / Rp {{ number_format($budget_overview['total_budget'], 0, ',', '.') }}
                            </span>
                        </div>
                        <span class="text-headline-sm font-bold tabular-nums {{ $budgetPct >= 100 ? 'text-rose-600 dark:text-[#FDA4AF]' : ($budgetPct >= 70 ? 'text-amber-600 dark:text-amber-300' : 'text-forest-primary dark:text-dark-lime') }}">
                            {{ $budgetPct }}%
                        </span>
                    </div>
                    <div class="w-full h-2.5 bg-surface-tint dark:bg-dark-canvas rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $budgetPct >= 100 ? 'bg-rose-500' : ($budgetPct >= 70 ? 'bg-amber-500' : 'bg-forest-primary dark:bg-dark-lime') }}" style="width: {{ min(100, $budgetPct) }}%;"></div>
                    </div>
                </section>
            @endif

            <!-- 3. GRAFIK DONAT DISTRIBUSI PENGELUARAN (DASH-002) -->
            <section class="bg-surface-card dark:bg-dark-surface rounded-2xl p-4 border border-border-subtle dark:border-dark-border shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Distribusi Pengeluaran</h3>
                        <p class="text-label-xs text-text-muted dark:text-dark-text-secondary">Bulan {{ now()->translatedFormat('F Y') }}</p>
                    </div>
                    <span class="text-label-lg font-bold text-forest-primary dark:text-dark-lime tabular-nums">
                        Rp {{ number_format($month_expense, 0, ',', '.') }}
                    </span>
                </div>

                @if (empty($expense_distribution['items']))
                    <p class="text-body-sm text-text-muted dark:text-dark-text-muted text-center py-6">
                        Belum ada pengeluaran bulan ini untuk ditampilkan di grafik.
                    </p>
                @else
                    <div class="flex items-center gap-4">
                        <div class="w-32 h-32 shrink-0">
                            <canvas id="expenseDonutChart" aria-label="Grafik donat distribusi pengeluaran"></canvas>
                        </div>
                        <div class="flex-1 space-y-1.5 min-w-0">
                            @foreach (array_slice($expense_distribution['items'], 0, 4) as $item)
                                <div class="flex items-center justify-between gap-2 text-label-xs">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item['color'] }};"></span>
                                        <span class="truncate text-text-primary dark:text-dark-text-primary font-medium">{{ $item['name'] }}</span>
                                    </div>
                                    <span class="tabular-nums font-semibold text-text-secondary dark:text-dark-text-secondary shrink-0">{{ $item['percentage'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            <!-- ADV-001: GRAFIK TREN KEKAYAAN BERSIH (NET WORTH HISTORY) -->
            @if (!empty($net_worth_history['values']) && count($net_worth_history['values']) > 0)
                <section class="bg-surface-card dark:bg-dark-surface rounded-2xl p-4 border border-border-subtle dark:border-dark-border shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Tren Kekayaan Bersih</h3>
                            <p class="text-label-xs text-text-muted dark:text-dark-text-secondary">Riwayat snapshot harian (IDR)</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-accent-lime-soft dark:bg-dark-surface-tint text-forest-primary dark:text-dark-lime">
                            Snapshot
                        </span>
                    </div>
                    <div class="h-44">
                        <canvas id="netWorthTrendChart" aria-label="Grafik tren kekayaan bersih"></canvas>
                    </div>
                </section>
            @endif

            <!-- 4. GRAFIK ARUS KAS BULANAN 6 BULAN (DASH-003) -->
            <section class="bg-surface-card dark:bg-dark-surface rounded-2xl p-4 border border-border-subtle dark:border-dark-border shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Tren Arus Kas (6 Bulan)</h3>
                        <p class="text-label-xs text-text-muted dark:text-dark-text-secondary">Pemasukan vs Pengeluaran</p>
                    </div>
                    <a href="{{ route('transactions.index') }}" class="text-label-md font-semibold text-forest-primary dark:text-dark-lime">Detail</a>
                </div>
                <div class="h-44">
                    <canvas id="cashflowTrendChart" aria-label="Grafik tren arus kas bulanan"></canvas>
                </div>
            </section>

            <!-- 5. HORIZONTAL WALLET CARDS -->
            @if ($wallets->isNotEmpty())
                <section class="space-y-2.5">
                    <div class="flex items-center justify-between px-1">
                        <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Dompet Saya</h3>
                        <a href="{{ route('wallets.index') }}" class="text-label-md font-semibold text-forest-primary dark:text-dark-lime">
                            Semua ({{ $wallets->count() }}) &rarr;
                        </a>
                    </div>
                    <div class="flex gap-3 overflow-x-auto no-scrollbar snap-x snap-mandatory pb-1">
                        @foreach ($wallets as $wallet)
                            <div class="min-w-[210px] snap-start bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-3.5 shadow-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-label-md font-semibold text-text-primary dark:text-dark-text-primary truncate">{{ $wallet->name }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-surface-tint dark:bg-dark-surface-tint text-text-secondary dark:text-dark-lime">
                                        {{ $wallet->currency }}
                                    </span>
                                </div>
                                <div class="text-title-md font-bold tabular-nums text-text-primary dark:text-dark-text-primary">
                                    {{ $wallet->currency }} {{ number_format((float) $wallet->balance, $wallet->currency === 'IDR' ? 0 : 2, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 6. RECENT TRANSACTIONS PREVIEW -->
            @if ($recent_transactions->isNotEmpty())
                <section class="bg-surface-card dark:bg-dark-surface rounded-2xl p-4 border border-border-subtle dark:border-dark-border shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Aktivitas Terkini</h3>
                        <a href="{{ route('transactions.index') }}" class="text-label-xs font-semibold text-forest-primary dark:text-dark-lime">Lihat Semua</a>
                    </div>
                    <div class="divide-y divide-border-subtle dark:divide-dark-border">
                        @foreach ($recent_transactions as $txn)
                            @php
                                $isInc = $txn->type === 'income';
                                $isTrf = in_array($txn->type, ['transfer', 'currency_exchange'], true);
                            @endphp
                            <div class="py-2.5 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="text-body-sm font-semibold text-text-primary dark:text-dark-text-primary truncate">
                                        {{ $txn->description ?: ($txn->category?->name ?? 'Transfer') }}
                                    </div>
                                    <div class="text-label-xs text-text-muted dark:text-dark-text-muted">
                                        {{ $txn->wallet?->name }} &bull; {{ $txn->transaction_date->translatedFormat('d M') }}
                                    </div>
                                </div>
                                <span class="text-body-sm font-bold tabular-nums shrink-0 {{ $isInc ? 'text-emerald-600 dark:text-dark-lime' : ($isTrf ? 'text-sky-600 dark:text-sky-300' : 'text-rose-600 dark:text-[#FDA4AF]') }}">
                                    {{ $isInc ? '+' : ($isTrf ? '' : '-') }}Rp {{ number_format((float) $txn->amount, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Chart === 'undefined') return;

            const isDark = document.documentElement.classList.contains('dark');
            const tickColor = isDark ? '#96ABA0' : '#47584F';
            const gridColor = isDark ? 'rgba(36, 59, 48, 0.5)' : 'rgba(227, 236, 225, 0.7)';

            const nwEl = document.getElementById('netWorthTrendChart');
            const nwData = @json($net_worth_history ?? ['labels' => [], 'values' => []]);
            if (nwEl && nwData.values && nwData.values.length > 0) {
                new Chart(nwEl, {
                    type: 'line',
                    data: {
                        labels: nwData.labels,
                        datasets: [{
                            label: 'Net Worth',
                            data: nwData.values,
                            borderColor: '#34D399',
                            backgroundColor: isDark ? 'rgba(52, 211, 153, 0.15)' : 'rgba(52, 211, 153, 0.1)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: nwData.values.length <= 10 ? 3 : 1.5,
                            pointBackgroundColor: '#34D399',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => 'Rp ' + Number(ctx.raw).toLocaleString('id-ID'),
                                },
                            },
                        },
                        scales: {
                            x: { ticks: { color: tickColor, maxTicksLimit: 6 }, grid: { display: false } },
                            y: {
                                ticks: {
                                    color: tickColor,
                                    callback: (v) => v >= 1000000 ? (v / 1000000) + 'M' : (v >= 1000 ? (v / 1000) + 'K' : v),
                                },
                                grid: { color: gridColor },
                            },
                        },
                    },
                });
            }

            const donutEl = document.getElementById('expenseDonutChart');
            const expenseData = @json($expense_distribution);
            if (donutEl && expenseData.values.length > 0) {
                new Chart(donutEl, {
                    type: 'doughnut',
                    data: {
                        labels: expenseData.labels,
                        datasets: [{
                            data: expenseData.values,
                            backgroundColor: expenseData.colors,
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '66%',
                        plugins: { legend: { display: false } },
                    },
                });
            }

            const trendEl = document.getElementById('cashflowTrendChart');
            const trendData = @json($cashflow_trend);
            if (trendEl) {
                new Chart(trendEl, {
                    type: 'bar',
                    data: {
                        labels: trendData.labels,
                        datasets: [
                            {
                                label: 'Pemasukan',
                                data: trendData.income,
                                backgroundColor: '#A8E874',
                                borderRadius: 6,
                            },
                            {
                                label: 'Pengeluaran',
                                data: trendData.expense,
                                backgroundColor: '#FDA4AF',
                                borderRadius: 6,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: tickColor, boxWidth: 10, font: { size: 11 } },
                            },
                        },
                        scales: {
                            x: { ticks: { color: tickColor }, grid: { display: false } },
                            y: {
                                ticks: {
                                    color: tickColor,
                                    callback: (v) => v >= 1000000 ? (v / 1000000) + 'M' : (v >= 1000 ? (v / 1000) + 'K' : v),
                                },
                                grid: { color: gridColor },
                            },
                        },
                    },
                });
            }
        });
    </script>
</x-layouts.mobile>
