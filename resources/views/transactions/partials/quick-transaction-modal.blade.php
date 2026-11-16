<!-- Bottom-Sheet Modal: Catat Cepat Transaksi (TXN-002 / Stitch 08) -->
<div
    x-show="quickModalOpen"
    x-cloak
    @keydown.escape.window="quickModalOpen = false"
    class="fixed inset-0 z-50 flex items-end justify-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="quick-txn-title"
>
    <!-- Backdrop -->
    <div
        x-show="quickModalOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="quickModalOpen = false"
        class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"
    ></div>

    <!-- Sheet Container -->
    <section
        x-show="quickModalOpen"
        x-transition:enter="ease-out duration-250"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6 max-h-[92vh] overflow-y-auto no-scrollbar"
    >
        <!-- Drag Handle -->
        <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>

        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-[20px] fill-1">bolt</span>
                <div>
                    <h2 id="quick-txn-title" class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary leading-none">Catat Cepat</h2>
                    <span class="text-label-xs text-text-muted dark:text-dark-text-muted">Smart default &bull; input instan</span>
                </div>
            </div>
            <button
                type="button"
                @click="quickModalOpen = false"
                aria-label="Tutup modal"
                class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        @if (session('error'))
            <div class="mb-3 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-label-md">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-3 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-label-md space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($wallets->isEmpty())
            <div class="p-4 rounded-2xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-center space-y-3">
                <p class="text-body-sm text-text-secondary dark:text-dark-text-secondary">
                    Kamu belum punya dompet aktif. Buat dompet dulu sebelum mencatat transaksi.
                </p>
                <a
                    href="{{ route('wallets.index') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-md font-semibold"
                >
                    <span>Buat Dompet</span>
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('transactions.store') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="type" x-model="quickForm.type">

                <!-- 4-Way Segmented Transaction Type Control -->
                <div class="bg-surface-tint dark:bg-dark-sidebar p-1 rounded-xl border border-border-subtle dark:border-dark-border grid grid-cols-4 gap-1">
                    <button
                        type="button"
                        @click="setQuickType('expense')"
                        :class="quickForm.type === 'expense' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                        class="py-1.5 rounded-lg text-[11px] font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer"
                    >
                        <span>Keluar</span>
                    </button>
                    <button
                        type="button"
                        @click="setQuickType('income')"
                        :class="quickForm.type === 'income' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                        class="py-1.5 rounded-lg text-[11px] font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer"
                    >
                        <span>Masuk</span>
                    </button>
                    <button
                        type="button"
                        @click="setQuickType('transfer')"
                        :class="quickForm.type === 'transfer' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                        class="py-1.5 rounded-lg text-[11px] font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer"
                    >
                        <span>Transfer</span>
                    </button>
                    <button
                        type="button"
                        @click="setQuickType('currency_exchange')"
                        :class="quickForm.type === 'currency_exchange' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                        class="py-1.5 rounded-lg text-[11px] font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer"
                    >
                        <span>Valas</span>
                    </button>
                </div>

                <!-- Hero Amount Input -->
                <div class="rounded-2xl bg-surface-tint dark:bg-dark-sidebar border border-border-subtle dark:border-dark-border p-3.5 text-center">
                    <label for="quick-amount" class="block text-label-xs text-text-muted dark:text-dark-text-muted uppercase tracking-wider mb-1">
                        Nominal Transaksi
                    </label>
                    <div class="flex items-center justify-center gap-1.5">
                        <span class="text-title-md font-bold text-text-secondary dark:text-dark-text-secondary">Rp</span>
                        <input
                            id="quick-amount"
                            type="number"
                            name="amount"
                            step="any"
                            min="1"
                            x-model="quickForm.amount"
                            required
                            placeholder="0"
                            class="w-44 bg-transparent text-center text-headline-lg-mobile font-bold tabular-nums text-text-primary dark:text-dark-text-primary focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Source Wallet + Date -->
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label for="quick-wallet" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1" x-text="quickForm.type === 'transfer' ? 'Dari Dompet' : 'Dompet'"></label>
                        <select
                            id="quick-wallet"
                            name="wallet_id"
                            x-model="quickForm.wallet_id"
                            required
                            class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                        >
                            @foreach ($wallets as $w)
                                <option value="{{ $w->id }}">
                                    {{ $w->name }} ({{ $w->currency }}){{ $w->is_default ? ' ★' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="quick-date" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Tanggal</label>
                        <input
                            id="quick-date"
                            type="date"
                            name="transaction_date"
                            x-model="quickForm.transaction_date"
                            required
                            class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                        >
                    </div>
                </div>

                <!-- Category (for Expense / Income) -->
                <div x-show="quickForm.type === 'expense' || quickForm.type === 'income'">
                    <div class="flex items-center justify-between mb-1">
                        <label for="quick-category" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary">Kategori</label>
                        <a href="{{ route('categories.index') }}" class="text-label-xs font-semibold text-forest-primary dark:text-dark-lime hover:underline">
                            + Kelola Kategori
                        </a>
                    </div>
                    <select
                        id="quick-category"
                        name="category_id"
                        x-model="quickForm.category_id"
                        :required="quickForm.type === 'expense' || quickForm.type === 'income'"
                        class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2.5 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                    >
                        <option value="">-- Pilih Kategori --</option>
                        <template x-if="quickForm.type === 'expense'">
                            <optgroup label="Kategori Pengeluaran">
                                @foreach ($expenseCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </optgroup>
                        </template>
                        <template x-if="quickForm.type === 'income'">
                            <optgroup label="Kategori Pemasukan">
                                @foreach ($incomeCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </optgroup>
                        </template>
                    </select>
                </div>

                <!-- Destination Wallet & Fee (for Transfer / Currency Exchange) -->
                <div x-show="quickForm.type === 'transfer' || quickForm.type === 'currency_exchange'" class="space-y-2.5">
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label for="quick-dest-wallet" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Ke Dompet</label>
                            <select
                                id="quick-dest-wallet"
                                name="destination_wallet_id"
                                x-model="quickForm.destination_wallet_id"
                                :required="quickForm.type === 'transfer' || quickForm.type === 'currency_exchange'"
                                class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                            >
                                <option value="">-- Pilih Tujuan --</option>
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->currency }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="quick-fee" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Biaya Admin</label>
                            <input
                                id="quick-fee"
                                type="number"
                                name="fee"
                                step="any"
                                min="0"
                                x-model="quickForm.fee"
                                placeholder="0"
                                class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm tabular-nums text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                            >
                        </div>
                    </div>

                    <div x-show="quickForm.type === 'currency_exchange'" class="rounded-xl bg-surface-tint dark:bg-dark-sidebar border border-border-subtle dark:border-dark-border p-3 space-y-1.5">
                        <label for="quick-dest-amount" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary">Nominal Diterima (Valas)</label>
                        <input
                            id="quick-dest-amount"
                            type="number"
                            name="destination_amount"
                            step="any"
                            min="0.0001"
                            x-model="quickForm.destination_amount"
                            :required="quickForm.type === 'currency_exchange'"
                            placeholder="Contoh: 100"
                            class="w-full rounded-xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border px-3 py-2 text-body-md font-bold tabular-nums text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                        >
                        <p class="text-label-xs text-text-muted dark:text-dark-text-muted tabular-nums" x-show="Number(quickForm.amount) > 0 && Number(quickForm.destination_amount) > 0">
                            Estimasi kurs: 1 unit tujuan = <strong class="text-forest-primary dark:text-dark-lime" x-text="(Number(quickForm.amount) / Number(quickForm.destination_amount)).toLocaleString('id-ID', { maximumFractionDigits: 2 })"></strong>
                        </p>
                    </div>
                </div>

                <!-- Optional Description -->
                <div>
                    <label for="quick-description" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Catatan (Opsional)</label>
                    <input
                        id="quick-description"
                        type="text"
                        name="description"
                        x-model="quickForm.description"
                        maxlength="500"
                        placeholder="Contoh: Kopi susu, bayar parkir, pindah dana..."
                        class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md active:scale-[0.98] transition-transform cursor-pointer"
                >
                    Simpan Transaksi
                </button>
            </form>
        @endif
    </section>
</div>
