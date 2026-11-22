<x-layouts.mobile :title="$wallet->name" activeNav="wallets">
    @php
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $isIdr = $wallet->currency === 'IDR';
        $balanceFormatted = number_format((float) $wallet->balance, $isIdr ? 0 : 2, ',', '.');
    @endphp

    <div class="flex-1 flex flex-col">
        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm border-b border-border-subtle/50 dark:border-dark-border/40 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('wallets.index') }}"
                    aria-label="Kembali ke daftar dompet"
                    class="w-8 h-8 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime transition-colors"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight truncate">
                    {{ $wallet->name }}
                </h1>
            </div>

            <div class="flex items-center gap-1.5">
                <a
                    href="{{ route('transactions.index', ['wallet_id' => $wallet->id]) }}"
                    class="px-2.5 py-1.5 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-label-md font-semibold text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime transition-colors"
                >
                    Filter Riwayat
                </a>
            </div>
        </header>

        <div class="px-gutter py-space-md space-y-4">
            <!-- Hero Wallet Card -->
            <section
                class="rounded-3xl p-5 text-white shadow-md relative overflow-hidden space-y-3.5"
                style="background-color: {{ $wallet->color }};"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                            <span class="material-symbols-outlined text-[20px]">{{ $wallet->icon }}</span>
                        </div>
                        <div>
                            <span class="text-label-md font-bold block leading-tight">{{ $wallet->name }}</span>
                            <span class="text-[11px] opacity-80 uppercase tracking-wider">{{ $wallet->type }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        @if ($wallet->is_default)
                            <span class="px-2 py-0.5 rounded-full bg-white/20 text-accent-lime text-[11px] font-bold">
                                Utama ★
                            </span>
                        @endif
                        <span class="px-2 py-0.5 rounded-full bg-black/20 text-white text-[11px] font-bold">
                            {{ $wallet->currency }}
                        </span>
                    </div>
                </div>

                <div>
                    <span class="text-label-xs opacity-75 uppercase tracking-wider block mb-0.5">Saldo Saat Ini</span>
                    <h2 class="text-headline-lg-mobile font-bold tabular-nums tracking-tight">
                        {{ $wallet->currency }} {{ $balanceFormatted }}
                    </h2>
                    @if ($wallet->account_number)
                        <p class="text-[11px] opacity-80 mt-1">No. Rek: {{ $wallet->account_number }}</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2.5 pt-3 border-t border-white/20">
                    <div class="bg-black/15 rounded-xl p-2.5">
                        <span class="text-[11px] opacity-80 block mb-0.5">Masuk Bln Ini</span>
                        <span class="text-label-md font-bold tabular-nums">
                            +Rp {{ number_format($monthIncome, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="bg-black/15 rounded-xl p-2.5">
                        <span class="text-[11px] opacity-80 block mb-0.5">Keluar Bln Ini</span>
                        <span class="text-label-md font-bold tabular-nums">
                            -Rp {{ number_format($monthExpense, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Wallet Transaction History -->
            <section class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">
                        Riwayat Mutasi Dompet
                    </h3>
                    <span class="text-label-xs text-text-muted dark:text-dark-text-muted">
                        Total {{ $totalTransactions }} transaksi
                    </span>
                </div>

                @if ($groupedTransactions->isEmpty())
                    <div class="bg-surface-card dark:bg-dark-surface border border-dashed border-border-subtle dark:border-dark-border rounded-2xl p-6 text-center space-y-2">
                        <span class="material-symbols-outlined text-[28px] text-text-muted">receipt_long</span>
                        <p class="text-body-sm text-text-muted dark:text-dark-text-muted">
                            Belum ada riwayat transaksi di dompet ini.
                        </p>
                    </div>
                @else
                    <div class="space-y-3.5">
                        @foreach ($groupedTransactions as $dateKey => $dayTransactions)
                            @php
                                $dateLabel = match ($dateKey) {
                                    $today => 'Hari Ini — '.\Carbon\Carbon::parse($dateKey)->translatedFormat('d M Y'),
                                    $yesterday => 'Kemarin — '.\Carbon\Carbon::parse($dateKey)->translatedFormat('d M Y'),
                                    default => \Carbon\Carbon::parse($dateKey)->translatedFormat('d M Y'),
                                };
                            @endphp

                            <div class="space-y-1.5">
                                <span class="text-label-xs font-bold text-text-muted dark:text-dark-text-secondary uppercase tracking-wider px-1">
                                    {{ $dateLabel }}
                                </span>

                                <div class="bg-surface-card dark:bg-dark-surface rounded-2xl border border-border-subtle dark:border-dark-border divide-y divide-border-subtle dark:divide-dark-border overflow-hidden shadow-xs">
                                    @foreach ($dayTransactions as $txn)
                                        @php
                                            $isSource = $txn->wallet_id === $wallet->id;
                                            $isIncome = ($txn->type === 'income') || (! $isSource && in_array($txn->type, ['transfer', 'currency_exchange'], true));
                                            $title = $txn->description
                                                ?: ($txn->type === 'transfer'
                                                    ? ($isSource ? 'Transfer ke '.$txn->destinationWallet?->name : 'Transfer dari '.$txn->wallet?->name)
                                                    : ($txn->type === 'currency_exchange'
                                                        ? ($isSource ? 'Tukar ke '.$txn->destinationWallet?->name : 'Tukar dari '.$txn->wallet?->name)
                                                        : ($txn->category?->name ?? 'Transaksi')));
                                            $badgeColor = $txn->category?->color ?? ($isIncome ? '#10B981' : '#F43F5E');
                                            $icon = $txn->category?->icon ?? ($isIncome ? 'payments' : ($txn->type === 'transfer' ? 'swap_horiz' : 'receipt_long'));
                                            $amountVal = (! $isSource && $txn->destination_amount !== null) ? (float) $txn->destination_amount : (float) $txn->amount;
                                        @endphp

                                        <article class="p-3.5 flex items-center justify-between gap-2.5">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div
                                                    class="w-9 h-9 rounded-xl flex items-center justify-center text-white shrink-0 shadow-xs"
                                                    style="background-color: {{ $badgeColor }};"
                                                >
                                                    <span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="text-body-md font-semibold text-text-primary dark:text-dark-text-primary truncate leading-tight">
                                                        {{ $title }}
                                                    </h4>
                                                    <span class="text-label-xs text-text-muted dark:text-dark-text-muted truncate block mt-0.5">
                                                        {{ $txn->category?->name ?? 'Umum' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="text-body-md font-bold tabular-nums {{ $isIncome ? 'text-emerald-600 dark:text-dark-lime' : 'text-rose-600 dark:text-[#FDA4AF]' }}">
                                                    {{ $isIncome ? '+' : '-' }}{{ $wallet->currency }} {{ number_format($amountVal, $isIdr ? 0 : 2, ',', '.') }}
                                                </span>

                                                <button
                                                    type="button"
                                                    @click="$dispatch('confirm-delete', { label: '{{ addslashes($title) }}', action: '{{ route('transactions.destroy', $txn) }}' })"
                                                    aria-label="Hapus transaksi"
                                                    class="w-7 h-7 rounded-full hover:bg-rose-50 dark:hover:bg-rose-950/50 flex items-center justify-center text-text-muted hover:text-rose-600 cursor-pointer transition-colors"
                                                >
                                                    <span class="material-symbols-outlined text-[15px]">delete</span>
                                                </button>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @if ($paginator->hasPages())
                            <div class="flex items-center justify-between gap-2 pt-2 pb-1 px-1">
                                @if ($paginator->onFirstPage())
                                    <span class="px-3 py-1.5 rounded-full bg-surface-tint dark:bg-dark-surface-tint text-label-xs font-semibold text-text-muted cursor-not-allowed opacity-50">&larr; Sebelumnya</span>
                                @else
                                    <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded-full bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border text-label-xs font-semibold text-text-primary dark:text-dark-text-primary">&larr; Sebelumnya</a>
                                @endif

                                <span class="text-label-xs text-text-muted dark:text-dark-text-secondary tabular-nums">
                                    Hal. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                                </span>

                                @if ($paginator->hasMorePages())
                                    <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-xs font-semibold">Berikutnya &rarr;</a>
                                @else
                                    <span class="px-3 py-1.5 rounded-full bg-surface-tint dark:bg-dark-surface-tint text-label-xs font-semibold text-text-muted cursor-not-allowed opacity-50">Berikutnya &rarr;</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts.mobile>
