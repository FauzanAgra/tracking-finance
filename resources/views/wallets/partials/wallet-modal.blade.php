<!-- Backdrop & Bottom Sheet Modal (Create / Edit Wallet) -->
<div
    x-show="modalOpen"
    x-cloak
    @keydown.escape.window="modalOpen = false"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
    role="dialog"
    aria-modal="true"
>
    <!-- Ambient Backdrop -->
    <div
        x-show="modalOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="modalOpen = false"
        class="fixed inset-0 bg-forest-deep/50 dark:bg-dark-canvas/80 backdrop-blur-sm"
    ></div>

    <!-- Bottom Sheet Panel -->
    <aside
        x-show="modalOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="translate-y-full sm:translate-y-6 sm:opacity-0"
        x-transition:enter-end="translate-y-0 sm:opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="translate-y-0 sm:opacity-100"
        x-transition:leave-end="translate-y-full sm:translate-y-6 sm:opacity-0"
        class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-[28px] sm:rounded-3xl border-t border-x sm:border border-border-subtle dark:border-dark-border shadow-2xl flex flex-col max-h-[90vh]"
    >
        <!-- Top Drag Handle Pill -->
        <div class="w-full flex items-center justify-center pt-3 pb-1 sm:hidden">
            <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border"></div>
        </div>

        <!-- Modal Header -->
        <div class="flex items-center justify-between px-space-md py-3 border-b border-border-subtle dark:border-dark-border/60">
            <div class="flex items-center gap-2.5">
                <div class="w-2.5 h-2.5 rounded-full bg-forest-primary dark:bg-dark-lime"></div>
                <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight"
                    x-text="modalMode === 'edit' ? 'Edit Dompet' : 'Tambah Dompet'">
                </h2>
            </div>
            <button
                type="button"
                @click="modalOpen = false"
                aria-label="Tutup modal"
                class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-text-secondary dark:text-dark-text-secondary flex items-center justify-center hover:text-text-primary dark:hover:text-dark-text-primary transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <!-- Scrollable Form Body -->
        <form
            method="POST"
            :action="modalMode === 'edit' ? '/wallets/' + editWalletId : '{{ route('wallets.store') }}'"
            class="p-space-md space-y-space-md overflow-y-auto no-scrollbar pb-6"
        >
            @csrf
            <template x-if="modalMode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <input type="hidden" name="_wallet_id" :value="editWalletId ?? ''">

            <!-- 1. Nama Dompet -->
            <div class="space-y-1.5">
                <label class="block text-label-xs uppercase tracking-wider text-text-secondary dark:text-dark-text-secondary font-semibold" for="wallet-name">
                    Nama Dompet
                </label>
                <input
                    id="wallet-name"
                    name="name"
                    type="text"
                    x-model="form.name"
                    placeholder="Contoh: BCA, Cash, GoPay, Binance USDT"
                    required
                    class="w-full h-11 px-4 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-text-primary dark:text-dark-text-primary placeholder:text-text-muted dark:placeholder:text-dark-text-muted text-body-md focus:border-forest-primary dark:focus:border-dark-lime focus:ring-1 focus:ring-forest-primary dark:focus:ring-dark-lime focus:outline-none transition-colors"
                />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <!-- 2. Tipe Dompet (2x2 Pill Grid) -->
            <div class="space-y-1.5">
                <label class="block text-label-xs uppercase tracking-wider text-text-secondary dark:text-dark-text-secondary font-semibold">
                    Tipe Dompet
                </label>
                <div class="grid grid-cols-2 gap-2.5" role="radiogroup">
                    @foreach ([
                        ['value' => 'bank', 'label' => 'Bank', 'icon' => 'account_balance'],
                        ['value' => 'cash', 'label' => 'Tunai / Cash', 'icon' => 'payments'],
                        ['value' => 'e_wallet', 'label' => 'E-Wallet', 'icon' => 'qr_code_2'],
                        ['value' => 'investment_cash', 'label' => 'Kas Investasi', 'icon' => 'trending_up'],
                    ] as $opt)
                        <label
                            @click="form.type = '{{ $opt['value'] }}'"
                            :class="form.type === '{{ $opt['value'] }}'
                                ? 'bg-accent-lime-soft border-forest-primary text-forest-primary dark:bg-[#1E4D38] dark:border-[#2F6F52] dark:text-dark-lime'
                                : 'bg-surface-tint border-border-subtle text-text-secondary dark:bg-dark-surface-tint dark:border-dark-border dark:text-dark-text-secondary hover:text-text-primary dark:hover:text-dark-text-primary'"
                            class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all duration-150 active:scale-[0.98]"
                        >
                            <input type="radio" name="type" value="{{ $opt['value'] }}" x-model="form.type" class="sr-only" />
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[19px]">{{ $opt['icon'] }}</span>
                                <span class="text-label-md font-semibold">{{ $opt['label'] }}</span>
                            </div>
                            <span class="material-symbols-outlined text-[17px] fill-1" x-show="form.type === '{{ $opt['value'] }}'">check_circle</span>
                            <span class="w-4 h-4 rounded-full border border-border-subtle dark:border-dark-border" x-show="form.type !== '{{ $opt['value'] }}'"></span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('type')" class="mt-1" />
            </div>

            <!-- 3. Mata Uang & Saldo Awal -->
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label class="block text-label-xs uppercase tracking-wider text-text-secondary dark:text-dark-text-secondary font-semibold" for="wallet-currency">
                        Mata Uang
                    </label>
                    <div class="relative">
                        <select
                            id="wallet-currency"
                            name="currency"
                            x-model="form.currency"
                            class="w-full h-11 pl-3 pr-8 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-text-primary dark:text-dark-text-primary text-body-md appearance-none focus:border-forest-primary dark:focus:border-dark-lime focus:ring-1 focus:ring-forest-primary dark:focus:ring-dark-lime focus:outline-none cursor-pointer"
                        >
                            <option value="IDR">IDR (Rupiah)</option>
                            <option value="USD">USD ($)</option>
                            <option value="USDT">USDT</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-text-secondary dark:text-dark-text-secondary">
                            <span class="material-symbols-outlined text-[18px]">expand_more</span>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-label-xs uppercase tracking-wider text-text-secondary dark:text-dark-text-secondary font-semibold" for="wallet-balance">
                        Saldo
                    </label>
                    <input
                        id="wallet-balance"
                        name="balance"
                        type="number"
                        step="0.0001"
                        min="0"
                        x-model="form.balance"
                        required
                        class="w-full h-11 px-3 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-text-primary dark:text-dark-text-primary tabular-nums text-body-md text-right focus:border-forest-primary dark:focus:border-dark-lime focus:ring-1 focus:ring-forest-primary dark:focus:ring-dark-lime focus:outline-none"
                    />
                    <x-input-error :messages="$errors->get('balance')" class="mt-1" />
                </div>
            </div>

            <!-- 4. Nomor Rekening / Kartu (Opsional) -->
            <div class="space-y-1.5">
                <label class="block text-label-xs uppercase tracking-wider text-text-secondary dark:text-dark-text-secondary font-semibold" for="wallet-account">
                    Nomor Rekening / Kartu (Opsional)
                </label>
                <div class="relative">
                    <input
                        id="wallet-account"
                        name="account_number"
                        type="text"
                        x-model="form.account_number"
                        placeholder="Contoh: 1234 5678 9967"
                        class="w-full h-11 pl-4 pr-10 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-text-primary dark:text-dark-text-primary placeholder:text-text-muted dark:placeholder:text-dark-text-muted tabular-nums text-body-md focus:border-forest-primary dark:focus:border-dark-lime focus:ring-1 focus:ring-forest-primary dark:focus:ring-dark-lime focus:outline-none"
                    />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-text-muted dark:text-dark-text-muted">
                        <span class="material-symbols-outlined text-[18px]">credit_card</span>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('account_number')" class="mt-1" />
            </div>

            <!-- 5. Tema Warna Kartu -->
            <div class="space-y-2">
                <label class="block text-label-xs uppercase tracking-wider text-text-secondary dark:text-dark-text-secondary font-semibold">
                    Tema Warna Kartu
                </label>
                <input type="hidden" name="color" :value="form.color">
                <div class="flex items-center gap-3.5 pt-0.5">
                    @foreach (['#1E4D38', '#3B82F6', '#10B981', '#0D9488', '#334155'] as $hex)
                        <button
                            type="button"
                            @click="form.color = '{{ $hex }}'"
                            style="background-color: {{ $hex }};"
                            :class="form.color === '{{ $hex }}' ? 'ring-2 ring-offset-2 ring-offset-surface-card dark:ring-offset-dark-surface ring-forest-primary dark:ring-dark-lime' : 'opacity-80 hover:opacity-100'"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-white shadow-sm transition-all active:scale-95 cursor-pointer"
                            aria-label="Pilih warna {{ $hex }}"
                        >
                            <span class="material-symbols-outlined text-[14px]" x-show="form.color === '{{ $hex }}'">check</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 6. Smart Default Switch -->
            <div class="bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-[#294336] rounded-2xl p-4 flex items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <p class="text-label-lg font-semibold text-text-primary dark:text-dark-text-primary">
                        Jadikan Dompet Utama (Default)
                    </p>
                    <p class="text-label-xs text-text-secondary dark:text-dark-text-secondary">
                        Otomatis terpilih saat catat cepat ≤ 3 detik
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" x-model="form.is_default" class="sr-only peer" />
                    <div class="w-11 h-6 bg-border-subtle dark:bg-dark-border rounded-full peer peer-checked:bg-forest-primary dark:peer-checked:bg-dark-lime transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white dark:after:bg-dark-canvas after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full h-12 rounded-xl bg-forest-primary hover:bg-forest-deep dark:bg-dark-lime dark:hover:bg-accent-lime text-white dark:text-dark-canvas text-label-lg font-bold tracking-wide flex items-center justify-center gap-2 shadow-lg shadow-forest-primary/20 dark:shadow-dark-lime/20 active:scale-[0.98] transition-all cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span x-text="modalMode === 'edit' ? 'Simpan Perubahan' : 'Simpan Dompet'"></span>
            </button>
        </form>
    </aside>
</div>
