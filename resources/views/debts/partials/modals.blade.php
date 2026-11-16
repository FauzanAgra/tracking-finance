<!-- 1. Modal Tambah Catatan Utang / Piutang -->
<div
    x-show="createModalOpen"
    x-cloak
    @keydown.escape.window="createModalOpen = false"
    class="fixed inset-0 z-50 flex items-end justify-center"
    role="dialog"
    aria-modal="true"
>
    <div @click="createModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
    <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6 max-h-[90vh] overflow-y-auto no-scrollbar">
        <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Tambah Catatan</h2>
            <button type="button" @click="createModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        @if ($errors->any())
            <div class="mb-3 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-label-md space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('debts.store') }}" class="space-y-3.5" x-data="{ debtType: @js(old('type', 'receivable')) }">
            @csrf
            <input type="hidden" name="type" x-model="debtType">
            <input type="hidden" name="currency" value="IDR">

            <div class="bg-surface-tint dark:bg-dark-sidebar p-1 rounded-xl border border-border-subtle dark:border-dark-border flex items-center gap-1">
                <button
                    type="button"
                    @click="debtType = 'receivable'"
                    :class="debtType === 'receivable' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                    class="flex-1 py-1.5 rounded-lg text-label-md font-semibold transition-all cursor-pointer"
                >
                    Piutang (Orang Pinjam)
                </button>
                <button
                    type="button"
                    @click="debtType = 'payable'"
                    :class="debtType === 'payable' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                    class="flex-1 py-1.5 rounded-lg text-label-md font-semibold transition-all cursor-pointer"
                >
                    Utang (Kita Pinjam)
                </button>
            </div>

            <div>
                <label for="debt-person" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Nama Orang / Pihak</label>
                <input id="debt-person" type="text" name="person_name" value="{{ old('person_name') }}" required placeholder="Contoh: Jamal" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-body-md text-text-primary dark:text-dark-text-primary">
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label for="debt-amount" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Total Pokok (Rp)</label>
                    <x-money-input id="debt-amount" name="total_amount" :value="old('total_amount', '')" :required="true" placeholder="400.000" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-md text-text-primary dark:text-dark-text-primary" />
                </div>
                <div>
                    <label for="debt-phone" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">No. HP (Opsional)</label>
                    <input id="debt-phone" type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="0812..." class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-md text-text-primary dark:text-dark-text-primary">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label for="debt-issue" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Tanggal Pinjam</label>
                    <input id="debt-issue" type="date" name="issue_date" value="{{ old('issue_date', now()->toDateString()) }}" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                </div>
                <div>
                    <label for="debt-due" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Jatuh Tempo</label>
                    <input id="debt-due" type="date" name="due_date" value="{{ old('due_date') }}" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                </div>
            </div>

            <div>
                <label for="debt-notes" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Catatan (Opsional)</label>
                <input id="debt-notes" type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Pinjam dana darurat motor" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
            </div>

            <button type="submit" class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md cursor-pointer">
                Simpan Catatan
            </button>
        </form>
    </section>
</div>

<!-- 2. Modal Input Pembayaran Cicilan (DEBT-003) -->
<div
    x-show="paymentModalOpen"
    x-cloak
    @keydown.escape.window="paymentModalOpen = false"
    class="fixed inset-0 z-50 flex items-end justify-center"
    role="dialog"
    aria-modal="true"
>
    <div @click="paymentModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
    <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6">
        <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Catat Cicilan</h2>
                <p class="text-label-xs text-text-muted dark:text-dark-text-muted" x-text="activeDebt ? activeDebt.person_name + ' • Sisa Rp ' + Number(activeDebt.remaining).toLocaleString('id-ID') : ''"></p>
            </div>
            <button type="button" @click="paymentModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form method="POST" :action="activeDebt ? '/debts/' + activeDebt.id + '/payments' : '#'" class="space-y-3.5">
            @csrf
            <div>
                <label for="pay-amount" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Nominal Cicilan (Rp)</label>
                <x-money-input id="pay-amount" name="amount" model="paymentForm.amount" :required="true" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-title-md font-bold text-text-primary dark:text-dark-text-primary" />
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label for="pay-wallet" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1" x-text="activeDebt && activeDebt.type === 'receivable' ? 'Masuk ke Dompet' : 'Bayar dari Dompet'"></label>
                    <select id="pay-wallet" name="wallet_id" x-model="paymentForm.wallet_id" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                        @foreach ($wallets as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->currency }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="pay-date" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Tanggal Bayar</label>
                    <input id="pay-date" type="date" name="payment_date" x-model="paymentForm.payment_date" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                </div>
            </div>

            <div>
                <label for="pay-notes" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Catatan (Opsional)</label>
                <input id="pay-notes" type="text" name="notes" x-model="paymentForm.notes" placeholder="Contoh: Bayar cash di warung" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
            </div>

            <button type="submit" class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md cursor-pointer">
                Simpan Cicilan
            </button>
        </form>
    </section>
</div>

<!-- 3. Modal Log Histori Cicilan per Orang (DEBT-004) -->
<div
    x-show="historyModalOpen"
    x-cloak
    @keydown.escape.window="historyModalOpen = false"
    class="fixed inset-0 z-50 flex items-end justify-center"
    role="dialog"
    aria-modal="true"
>
    <div @click="historyModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
    <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6 max-h-[80vh] overflow-y-auto no-scrollbar">
        <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Histori Cicilan</h2>
                <p class="text-label-xs text-text-muted dark:text-dark-text-muted" x-text="activeDebt ? activeDebt.person_name : ''"></p>
            </div>
            <button type="button" @click="historyModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <template x-if="activeDebt && activeDebt.payments.length === 0">
            <p class="text-body-sm text-text-muted dark:text-dark-text-muted text-center py-6">
                Belum ada riwayat pembayaran cicilan.
            </p>
        </template>

        <div class="divide-y divide-border-subtle dark:divide-dark-border">
            <template x-for="payment in (activeDebt ? activeDebt.payments : [])" :key="payment.id">
                <div class="py-3 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-body-sm font-semibold text-text-primary dark:text-dark-text-primary" x-text="payment.payment_date"></div>
                        <div class="text-label-xs text-text-muted dark:text-dark-text-muted" x-text="payment.wallet_name + (payment.notes ? ' • ' + payment.notes : '')"></div>
                    </div>
                    <div class="text-body-md font-bold tabular-nums text-emerald-600 dark:text-dark-lime" x-text="(activeDebt && activeDebt.type === 'receivable' ? '+' : '-') + 'Rp ' + Number(payment.amount).toLocaleString('id-ID')"></div>
                </div>
            </template>
        </div>
    </section>
</div>

<!-- 4. Modal Ikhlaskan Sisa Utang / Write-Off (DEBT-005) -->
<div
    x-show="writeOffModalOpen"
    x-cloak
    @keydown.escape.window="writeOffModalOpen = false"
    class="fixed inset-0 z-50 flex items-end justify-center"
    role="dialog"
    aria-modal="true"
>
    <div @click="writeOffModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
    <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6">
        <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Ikhlaskan Sisa Pinjaman</h2>
            <button type="button" @click="writeOffModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <p class="text-body-sm text-text-secondary dark:text-dark-text-secondary mb-4">
            Sisa tagihan sebesar <strong class="text-text-primary dark:text-dark-text-primary tabular-nums" x-text="activeDebt ? 'Rp ' + Number(activeDebt.remaining).toLocaleString('id-ID') : ''"></strong> untuk <strong x-text="activeDebt ? activeDebt.person_name : ''"></strong> akan ditandai impas (<code class="text-xs">written_off</code>) tanpa mengubah saldo dompet.
        </p>

        <form method="POST" :action="activeDebt ? '/debts/' + activeDebt.id + '/write-off' : '#'" class="space-y-3.5">
            @csrf
            @method('PATCH')
            <div>
                <label for="writeoff-notes" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Alasan / Catatan (Opsional)</label>
                <input id="writeoff-notes" type="text" name="writeoff_notes" x-model="writeOffNotes" placeholder="Contoh: Dianggap sedekah / impas" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
            </div>

            <button type="submit" class="w-full py-3 rounded-full bg-amber-500 hover:bg-amber-600 text-white text-label-lg font-bold shadow-md cursor-pointer">
                Konfirmasi Ikhlaskan Sisa
            </button>
        </form>
    </section>
</div>
