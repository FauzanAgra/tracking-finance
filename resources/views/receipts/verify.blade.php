<x-layouts.mobile title="Verifikasi Struk AI" activeNav="transactions">
    @php
        $merchantName = (string) ($extracted['merchant_name'] ?? '');
        $totalAmount = (float) ($extracted['total_amount'] ?? 0);
        $txnDate = (string) ($extracted['transaction_date'] ?? now()->toDateString());
        $items = is_array($extracted['items'] ?? null) ? $extracted['items'] : [];
    @endphp

    <div class="flex-1 flex flex-col">
        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm flex items-center justify-between border-b border-border-subtle/50 dark:border-dark-border/40">
            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('receipts.index') }}"
                    aria-label="Kembali ke Scan Struk"
                    class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-primary dark:text-dark-text-primary active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight leading-none">Verifikasi Struk</h1>
                    <span class="text-label-xs text-text-muted dark:text-dark-text-muted">Tinjau hasil ekstraksi AI</span>
                </div>
            </div>
        </header>

        <div class="px-gutter py-space-md space-y-4">
            @if (session('status'))
                <div class="p-3 rounded-xl bg-semantic-profit-bg/60 dark:bg-dark-surface-tint border border-forest-primary/20 dark:border-dark-lime/30 text-forest-primary dark:text-dark-lime text-body-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-body-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-label-md space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Extracted Items Preview -->
            @if (! empty($items))
                <section class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-4 space-y-2.5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-label-xs font-bold uppercase tracking-wider text-text-muted dark:text-dark-text-secondary">Rincian Item Terdeteksi</span>
                        <span class="text-label-xs font-semibold text-forest-primary dark:text-dark-lime">{{ count($items) }} item</span>
                    </div>
                    <div class="divide-y divide-border-subtle dark:divide-dark-border">
                        @foreach ($items as $item)
                            <div class="py-1.5 flex items-center justify-between text-body-sm">
                                <span class="text-text-primary dark:text-dark-text-primary truncate pr-2">{{ $item['name'] ?? 'Item' }}</span>
                                <span class="font-semibold tabular-nums text-text-secondary dark:text-dark-text-secondary shrink-0">
                                    Rp {{ number_format((float) ($item['price'] ?? 0), 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Verification & Confirmation Form (AI-003) -->
            @if ($scan->generated_transaction_id)
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-[#1C3B2A] border border-emerald-200 dark:border-[#29563E] text-emerald-700 dark:text-[#86EFAC] text-body-sm font-medium">
                    Struk ini sudah dikonfirmasi dan tersimpan ke riwayat transaksi (#{{ $scan->generated_transaction_id }}).
                </div>
            @else
                <form method="POST" action="{{ route('receipts.confirm', $scan) }}" class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-3xl p-4 space-y-3.5 shadow-xs">
                    @csrf

                    <div>
                        <label for="verify-desc" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Merchant / Catatan</label>
                        <input
                            id="verify-desc"
                            type="text"
                            name="description"
                            value="{{ old('description', $merchantName) }}"
                            placeholder="Nama toko / catatan transaksi"
                            class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-body-md text-text-primary dark:text-dark-text-primary"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label for="verify-amount" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Total Nominal (Rp)</label>
                            <x-money-input
                                id="verify-amount"
                                name="amount"
                                :value="old('amount', $totalAmount > 0 ? $totalAmount : '')"
                                :required="true"
                                class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-title-md font-bold text-text-primary dark:text-dark-text-primary"
                            />
                        </div>

                        <div>
                            <label for="verify-date" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Tanggal</label>
                            <input
                                id="verify-date"
                                type="date"
                                name="transaction_date"
                                value="{{ old('transaction_date', $txnDate) }}"
                                required
                                class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label for="verify-wallet" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Dompet Sumber</label>
                            <select id="verify-wallet" name="wallet_id" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2.5 text-body-sm text-text-primary dark:text-dark-text-primary">
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}" @selected((string) old('wallet_id', $defaultWallet?->id) === (string) $w->id)>
                                        {{ $w->name }} ({{ $w->currency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="verify-category" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Kategori</label>
                            <select id="verify-category" name="category_id" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2.5 text-body-sm text-text-primary dark:text-dark-text-primary">
                                @foreach ($expenseCategories as $cat)
                                    <option value="{{ $cat->id }}" @selected((string) old('category_id', $matchedCategory?->id) === (string) $cat->id)>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md cursor-pointer">
                        Konfirmasi &amp; Simpan Transaksi
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.mobile>
