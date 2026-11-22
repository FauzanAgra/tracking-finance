<!-- Bottom-Sheet Modal: Edit Transaksi (IMV-004) -->
<div
    x-show="editModalOpen"
    x-cloak
    @keydown.escape.window="editModalOpen = false"
    class="fixed inset-0 z-50 flex items-end justify-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-txn-title"
>
    <!-- Backdrop -->
    <div
        x-show="editModalOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="editModalOpen = false"
        class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"
    ></div>

    <!-- Sheet Container -->
    <section
        x-show="editModalOpen"
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
                <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-[20px]">edit_note</span>
                <div>
                    <h2 id="edit-txn-title" class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary leading-none">Edit Transaksi</h2>
                    <span class="text-label-xs text-text-muted dark:text-dark-text-muted">Perbarui detail & saldo otomatis</span>
                </div>
            </div>
            <button
                type="button"
                @click="editModalOpen = false"
                aria-label="Tutup modal"
                class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form method="POST" :action="'/transactions/' + editForm.id" class="space-y-3.5">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" x-model="editForm.type">

            <!-- 4-Way Segmented Transaction Type Control -->
            <div class="bg-surface-tint dark:bg-dark-sidebar p-1 rounded-xl border border-border-subtle dark:border-dark-border grid grid-cols-4 gap-1">
                <button
                    type="button"
                    @click="setEditType('expense')"
                    :class="editForm.type === 'expense' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                    class="py-1.5 rounded-lg text-[11px] font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer"
                >
                    <span>Keluar</span>
                </button>
                <button
                    type="button"
                    @click="setEditType('income')"
                    :class="editForm.type === 'income' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                    class="py-1.5 rounded-lg text-[11px] font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer"
                >
                    <span>Masuk</span>
                </button>
                <button
                    type="button"
                    @click="setEditType('transfer')"
                    :class="editForm.type === 'transfer' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                    class="py-1.5 rounded-lg text-[11px] font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer"
                >
                    <span>Transfer</span>
                </button>
                <button
                    type="button"
                    @click="setEditType('currency_exchange')"
                    :class="editForm.type === 'currency_exchange' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                    class="py-1.5 rounded-lg text-[11px] font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer"
                >
                    <span>Valas</span>
                </button>
            </div>

            <!-- Hero Amount Input -->
            <div class="rounded-2xl bg-surface-tint dark:bg-dark-sidebar border border-border-subtle dark:border-dark-border p-3.5 text-center">
                <label for="edit-amount" class="block text-label-xs text-text-muted dark:text-dark-text-muted uppercase tracking-wider mb-1">
                    Nominal Transaksi
                </label>
                <div class="flex items-center justify-center gap-1.5">
                    <span class="text-title-md font-bold text-text-secondary dark:text-dark-text-secondary">Rp</span>
                    <x-money-input
                        id="edit-amount"
                        name="amount"
                        model="editForm.amount"
                        :required="true"
                        placeholder="0"
                        class="w-44 bg-transparent text-center text-headline-lg-mobile font-bold text-text-primary dark:text-dark-text-primary focus:outline-none"
                    />
                </div>
            </div>

            <!-- Source Wallet + Date -->
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label for="edit-wallet" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1" x-text="editForm.type === 'transfer' ? 'Dari Dompet' : 'Dompet'"></label>
                    <select
                        id="edit-wallet"
                        name="wallet_id"
                        x-model="editForm.wallet_id"
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
                    <label for="edit-date" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Tanggal</label>
                    <input
                        id="edit-date"
                        type="date"
                        name="transaction_date"
                        x-model="editForm.transaction_date"
                        required
                        class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                    >
                </div>
            </div>

            <!-- Category (for Expense / Income) -->
            <div x-show="editForm.type === 'expense' || editForm.type === 'income'">
                <label for="edit-category" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Kategori</label>
                <select
                    id="edit-category"
                    name="category_id"
                    x-model="editForm.category_id"
                    :required="editForm.type === 'expense' || editForm.type === 'income'"
                    class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2.5 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                >
                    <option value="">-- Pilih Kategori --</option>
                    <template x-if="editForm.type === 'expense'">
                        <optgroup label="Kategori Pengeluaran">
                            @foreach ($expenseCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </optgroup>
                    </template>
                    <template x-if="editForm.type === 'income'">
                        <optgroup label="Kategori Pemasukan">
                            @foreach ($incomeCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </optgroup>
                    </template>
                </select>
            </div>

            <!-- Destination Wallet & Fee (for Transfer / Currency Exchange) -->
            <div x-show="editForm.type === 'transfer' || editForm.type === 'currency_exchange'" class="space-y-2.5">
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label for="edit-dest-wallet" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Ke Dompet</label>
                        <select
                            id="edit-dest-wallet"
                            name="destination_wallet_id"
                            x-model="editForm.destination_wallet_id"
                            :required="editForm.type === 'transfer' || editForm.type === 'currency_exchange'"
                            class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                        >
                            <option value="">-- Pilih Tujuan --</option>
                            @foreach ($wallets as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->currency }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit-fee" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Biaya Admin</label>
                        <x-money-input
                            id="edit-fee"
                            name="fee"
                            model="editForm.fee"
                            placeholder="0"
                            class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                        />
                    </div>
                </div>

                <div x-show="editForm.type === 'currency_exchange'" class="rounded-xl bg-surface-tint dark:bg-dark-sidebar border border-border-subtle dark:border-dark-border p-3 space-y-1.5">
                    <label for="edit-dest-amount" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary">Nominal Diterima (Valas)</label>
                    <x-money-input
                        id="edit-dest-amount"
                        name="destination_amount"
                        model="editForm.destination_amount"
                        x-bind:required="editForm.type === 'currency_exchange'"
                        placeholder="Contoh: 100"
                        class="w-full rounded-xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border px-3 py-2 text-body-md font-bold text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                    />
                    <p class="text-label-xs text-text-muted dark:text-dark-text-muted tabular-nums" x-show="Number(editForm.amount) > 0 && Number(editForm.destination_amount) > 0">
                        Estimasi kurs: 1 unit tujuan = <strong class="text-forest-primary dark:text-dark-lime" x-text="(Number(editForm.amount) / Number(editForm.destination_amount)).toLocaleString('id-ID', { maximumFractionDigits: 2 })"></strong>
                    </p>
                </div>
            </div>

            <!-- Optional Description -->
            <div>
                <label for="edit-description" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Catatan (Opsional)</label>
                <input
                    id="edit-description"
                    type="text"
                    name="description"
                    x-model="editForm.description"
                    maxlength="500"
                    placeholder="Contoh: Kopi susu, bayar parkir, pindah dana..."
                    class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                >
            </div>

            <button
                type="submit"
                class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md active:scale-[0.98] transition-transform cursor-pointer"
            >
                Simpan Perubahan
            </button>
        </form>
    </section>
</div>
