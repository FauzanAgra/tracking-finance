<x-layouts.mobile title="Riwayat Transaksi" activeNav="transactions">
    @php
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $defaultWalletId = $defaultWallet?->id ? (string) $defaultWallet->id : '';
    @endphp

    <div
        x-data="{
            quickModalOpen: @js($openQuickModal),
            filterOpen: @js((bool) ($filters['wallet_id'] || $filters['category_id'] || $filters['date_from'] || $filters['date_to'])),
            quickForm: {
                type: @js(old('type', 'expense')),
                amount: @js(old('amount', '')),
                wallet_id: @js((string) old('wallet_id', $defaultWalletId)),
                destination_wallet_id: @js((string) old('destination_wallet_id', '')),
                category_id: @js((string) old('category_id', '')),
                fee: @js(old('fee', '0')),
                transaction_date: @js(old('transaction_date', $today)),
                description: @js(old('description', '')),
            },
            openQuickEntry(type = 'expense') {
                this.quickForm = {
                    type: type,
                    amount: '',
                    wallet_id: @js($defaultWalletId),
                    destination_wallet_id: '',
                    category_id: '',
                    fee: '0',
                    transaction_date: @js($today),
                    description: '',
                };
                this.quickModalOpen = true;
            },
            setQuickType(type) {
                this.quickForm.type = type;
                this.quickForm.category_id = '';
                if (type !== 'transfer') {
                    this.quickForm.destination_wallet_id = '';
                }
            }
        }"
        class="flex-1 flex flex-col"
    >
        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm border-b border-border-subtle/50 dark:border-dark-border/40 space-y-2.5">
            <div class="flex items-center justify-between">
                <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight">Riwayat Transaksi</h1>

                <div class="flex items-center gap-1.5">
                    <a
                        href="{{ route('debts.index') }}"
                        class="px-2.5 py-1.5 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-label-md font-semibold text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime transition-colors"
                    >
                        Utang
                    </a>
                    <a
                        href="{{ route('categories.index') }}"
                        class="px-2.5 py-1.5 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-label-md font-semibold text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime transition-colors"
                    >
                        Kategori
                    </a>

                    <button
                        type="button"
                        x-data="{
                            darkMode: document.documentElement.classList.contains('dark'),
                            toggle() {
                                this.darkMode = !this.darkMode;
                                if (this.darkMode) {
                                    document.documentElement.classList.add('dark');
                                    localStorage.setItem('tf_theme', 'dark');
                                } else {
                                    document.documentElement.classList.remove('dark');
                                    localStorage.setItem('tf_theme', 'light');
                                }
                            }
                        }"
                        @click="toggle()"
                        aria-label="Ganti tema"
                        class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime active:scale-95 transition-all"
                    >
                        <span class="material-symbols-outlined text-[18px]" x-show="darkMode">dark_mode</span>
                        <span class="material-symbols-outlined text-[18px]" x-show="!darkMode" x-cloak>light_mode</span>
                    </button>

                    <button
                        type="button"
                        @click="openQuickEntry('expense')"
                        aria-label="Catat Transaksi"
                        class="w-9 h-9 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas flex items-center justify-center shadow-sm active:scale-95 transition-all cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[20px]">add</span>
                    </button>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <form method="GET" action="{{ route('transactions.index') }}" class="space-y-2">
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined text-[18px] text-text-muted dark:text-dark-text-muted absolute left-3 top-1/2 -translate-y-1/2">search</span>
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] }}"
                            placeholder="Cari catatan, kategori, dompet..."
                            class="w-full pl-9 pr-3 py-2 rounded-xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border text-body-sm text-text-primary dark:text-dark-text-primary placeholder:text-text-muted dark:placeholder:text-dark-text-muted focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                        >
                    </div>
                    <button
                        type="button"
                        @click="filterOpen = !filterOpen"
                        :class="filterOpen ? 'bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas border-forest-primary dark:border-dark-lime' : 'bg-surface-card dark:bg-dark-surface text-text-secondary dark:text-dark-text-secondary border-border-subtle dark:border-dark-border'"
                        aria-label="Filter lanjutan"
                        class="w-9 h-9 rounded-xl border flex items-center justify-center shrink-0 transition-colors cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">tune</span>
                    </button>
                    <button
                        type="submit"
                        class="px-3 py-2 rounded-xl bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime text-label-md font-semibold shrink-0 cursor-pointer"
                    >
                        Cari
                    </button>
                </div>

                <!-- Horizontal Quick Type Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5">
                    @foreach (['all' => 'Semua', 'expense' => 'Pengeluaran', 'income' => 'Pemasukan', 'transfer' => 'Transfer'] as $typeKey => $typeLabel)
                        <a
                            href="{{ route('transactions.index', array_merge(request()->except('type'), $typeKey === 'all' ? [] : ['type' => $typeKey])) }}"
                            class="shrink-0 px-3 py-1 rounded-full text-label-md font-semibold border transition-colors {{ $filters['type'] === $typeKey ? 'bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas border-forest-primary dark:border-dark-lime' : 'bg-surface-card dark:bg-dark-surface text-text-secondary dark:text-dark-text-secondary border-border-subtle dark:border-dark-border' }}"
                        >
                            {{ $typeLabel }}
                        </a>
                    @endforeach
                </div>

                <!-- Collapsible Advanced Filters -->
                <div x-show="filterOpen" x-cloak class="pt-1 grid grid-cols-2 gap-2">
                    <select
                        name="wallet_id"
                        class="rounded-xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border px-2.5 py-2 text-label-md text-text-primary dark:text-dark-text-primary"
                    >
                        <option value="">Semua Dompet</option>
                        @foreach ($wallets as $w)
                            <option value="{{ $w->id }}" @selected((string) $filters['wallet_id'] === (string) $w->id)>
                                {{ $w->name }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="category_id"
                        class="rounded-xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border px-2.5 py-2 text-label-md text-text-primary dark:text-dark-text-primary"
                    >
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) $filters['category_id'] === (string) $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>

                    <input
                        type="date"
                        name="date_from"
                        value="{{ $filters['date_from'] }}"
                        aria-label="Dari tanggal"
                        class="rounded-xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border px-2.5 py-2 text-label-md text-text-primary dark:text-dark-text-primary"
                    >
                    <input
                        type="date"
                        name="date_to"
                        value="{{ $filters['date_to'] }}"
                        aria-label="Sampai tanggal"
                        class="rounded-xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border px-2.5 py-2 text-label-md text-text-primary dark:text-dark-text-primary"
                    >

                    <div class="col-span-2 flex items-center justify-between gap-2">
                        <a href="{{ route('transactions.index') }}" class="text-label-md text-text-muted dark:text-dark-text-muted hover:underline">
                            Reset Filter
                        </a>
                        <button type="submit" class="px-4 py-1.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-md font-semibold cursor-pointer">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </header>

        <div class="px-gutter py-space-md space-y-4">
            @if (session('status'))
                <div class="p-3 rounded-xl bg-semantic-profit-bg/60 dark:bg-dark-surface-tint border border-forest-primary/20 dark:border-dark-lime/30 text-forest-primary dark:text-dark-lime text-body-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            <!-- 2-Column KPI Strip -->
            <section aria-label="Ringkasan Arus Kas" class="grid grid-cols-2 gap-2.5">
                <div class="bg-emerald-50 dark:bg-[#1C3B2A] p-3.5 rounded-2xl border border-emerald-200 dark:border-[#29563E] shadow-xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-label-md text-emerald-700 dark:text-[#96ABA0] font-medium">Total Masuk</span>
                        <div class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-[#122D21] flex items-center justify-center text-emerald-700 dark:text-[#86EFAC]">
                            <span class="material-symbols-outlined text-[15px]">arrow_outward</span>
                        </div>
                    </div>
                    <div class="text-title-md font-bold text-emerald-700 dark:text-[#86EFAC] tabular-nums truncate">
                        +Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </div>
                    <p class="text-label-xs text-emerald-600/80 dark:text-[#96ABA0] mt-0.5">{{ $incomeCount }} transaksi masuk</p>
                </div>

                <div class="bg-rose-50 dark:bg-[#3B1822] p-3.5 rounded-2xl border border-rose-200 dark:border-[#5A2534] shadow-xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-label-md text-rose-700 dark:text-[#96ABA0] font-medium">Total Keluar</span>
                        <div class="w-6 h-6 rounded-full bg-rose-100 dark:bg-[#260F16] flex items-center justify-center text-rose-700 dark:text-[#FDA4AF]">
                            <span class="material-symbols-outlined text-[15px]">arrow_downward</span>
                        </div>
                    </div>
                    <div class="text-title-md font-bold text-rose-700 dark:text-[#FDA4AF] tabular-nums truncate">
                        -Rp {{ number_format($totalExpense, 0, ',', '.') }}
                    </div>
                    <p class="text-label-xs text-rose-600/80 dark:text-[#96ABA0] mt-0.5">{{ $expenseCount }} mutasi keluar</p>
                </div>
            </section>

            <!-- Date-Grouped Transaction List -->
            @if ($groupedTransactions->isEmpty())
                <div class="bg-surface-card dark:bg-dark-surface border border-dashed border-border-subtle dark:border-dark-border rounded-3xl p-8 text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-accent-lime-soft dark:bg-dark-surface-tint flex items-center justify-center text-forest-primary dark:text-dark-lime">
                        <span class="material-symbols-outlined text-[28px]">receipt_long</span>
                    </div>
                    <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Belum Ada Transaksi</h3>
                    <p class="text-body-sm text-text-muted dark:text-dark-text-muted">
                        Mulai catat pengeluaran, pemasukan, atau transfer antar dompet pertamamu.
                    </p>
                    <button
                        type="button"
                        @click="openQuickEntry('expense')"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-semibold active:scale-95 transition-transform cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">bolt</span>
                        <span>Catat Transaksi Pertama</span>
                    </button>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($groupedTransactions as $dateKey => $dayTransactions)
                        @php
                            $dateLabel = match ($dateKey) {
                                $today => 'Hari Ini — '.\Carbon\Carbon::parse($dateKey)->translatedFormat('d M Y'),
                                $yesterday => 'Kemarin — '.\Carbon\Carbon::parse($dateKey)->translatedFormat('d M Y'),
                                default => \Carbon\Carbon::parse($dateKey)->translatedFormat('d M Y'),
                            };
                        @endphp
                        <section class="space-y-1.5">
                            <div class="flex items-center justify-between px-1">
                                <h2 class="text-label-md font-bold text-text-muted dark:text-dark-text-secondary uppercase tracking-wider">
                                    {{ $dateLabel }}
                                </h2>
                                <span class="text-label-xs text-text-muted dark:text-dark-text-muted">
                                    {{ $dayTransactions->count() }} item
                                </span>
                            </div>

                            <div class="bg-surface-card dark:bg-dark-surface rounded-2xl border border-border-subtle dark:border-dark-border divide-y divide-border-subtle dark:divide-dark-border overflow-hidden shadow-xs">
                                @foreach ($dayTransactions as $txn)
                                    @php
                                        $isIncome = $txn->type === 'income';
                                        $isTransfer = $txn->type === 'transfer';
                                        $title = $txn->description
                                            ?: ($isTransfer
                                                ? 'Transfer ke '.($txn->destinationWallet?->name ?? 'Dompet')
                                                : ($txn->category?->name ?? 'Transaksi'));
                                        $icon = $isTransfer
                                            ? 'swap_horiz'
                                            : ($txn->category?->icon ?? ($isIncome ? 'payments' : 'receipt_long'));
                                        $badgeColor = $isTransfer ? '#3B82F6' : ($txn->category?->color ?? '#1E4D38');
                                        $amountFormatted = number_format((float) $txn->amount, 0, ',', '.');
                                    @endphp

                                    <article class="p-3.5 flex items-center justify-between gap-2.5">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div
                                                class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-xs"
                                                style="background-color: {{ $badgeColor }};"
                                            >
                                                <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
                                            </div>

                                            <div class="min-w-0">
                                                <h3 class="text-body-md font-semibold text-text-primary dark:text-dark-text-primary truncate leading-tight">
                                                    {{ $title }}
                                                </h3>
                                                <div class="flex items-center gap-1.5 mt-0.5 text-label-xs text-text-muted dark:text-dark-text-muted truncate">
                                                    @if ($isTransfer)
                                                        <span class="font-medium text-text-secondary dark:text-dark-text-secondary">Transfer</span>
                                                        <span>&bull;</span>
                                                        <span>{{ $txn->wallet?->name }} &rarr; {{ $txn->destinationWallet?->name }}</span>
                                                    @else
                                                        <span class="font-medium text-text-secondary dark:text-dark-text-secondary">{{ $txn->category?->name ?? 'Tanpa Kategori' }}</span>
                                                        <span>&bull;</span>
                                                        <span>{{ $txn->wallet?->name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <div class="text-right">
                                                <span class="text-body-md font-bold tabular-nums {{ $isIncome ? 'text-emerald-600 dark:text-[#86EFAC]' : ($isTransfer ? 'text-sky-600 dark:text-sky-300' : 'text-rose-600 dark:text-[#FDA4AF]') }}">
                                                    {{ $isIncome ? '+' : ($isTransfer ? '' : '-') }}Rp {{ $amountFormatted }}
                                                </span>
                                                @if ($isTransfer && (float) $txn->fee > 0)
                                                    <div class="text-[10px] text-text-muted dark:text-dark-text-muted tabular-nums">
                                                        Fee Rp {{ number_format((float) $txn->fee, 0, ',', '.') }}
                                                    </div>
                                                @endif
                                            </div>

                                            <form method="POST" action="{{ route('transactions.destroy', $txn) }}" onsubmit="return confirm('Hapus transaksi ini dan kembalikan saldo dompet?')">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    aria-label="Hapus transaksi"
                                                    class="w-7 h-7 rounded-full hover:bg-rose-50 dark:hover:bg-rose-950/50 flex items-center justify-center text-text-muted dark:text-dark-text-muted hover:text-rose-600 dark:hover:text-rose-400 transition-colors cursor-pointer"
                                                >
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </div>

        @include('transactions.partials.quick-transaction-modal')
    </div>
</x-layouts.mobile>
