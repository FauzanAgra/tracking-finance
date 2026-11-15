<x-layouts.mobile title="Dompet & Valas" activeNav="wallets">
    <div
        x-data="{
            filterCurrency: 'ALL',
            copiedId: null,
            modalOpen: @json($errors->any()),
            modalMode: @json(old('_method') === 'PUT' ? 'edit' : 'create'),
            editWalletId: @json(old('_wallet_id')),
            form: {
                name: @json(old('name', '')),
                type: @json(old('type', 'bank')),
                currency: @json(old('currency', 'IDR')),
                balance: @json(old('balance', '0')),
                account_number: @json(old('account_number', '')),
                color: @json(old('color', '#1E4D38')),
                is_default: @json((bool) old('is_default', false)),
            },
            openCreate() {
                this.modalMode = 'create';
                this.editWalletId = null;
                this.form = {
                    name: '',
                    type: 'bank',
                    currency: 'IDR',
                    balance: '0',
                    account_number: '',
                    color: '#1E4D38',
                    is_default: false,
                };
                this.modalOpen = true;
            },
            openEdit(wallet) {
                this.modalMode = 'edit';
                this.editWalletId = wallet.id;
                this.form = {
                    name: wallet.name,
                    type: wallet.type,
                    currency: wallet.currency,
                    balance: Number(wallet.balance).toString(),
                    account_number: wallet.account_number ?? '',
                    color: wallet.color ?? '#1E4D38',
                    is_default: Boolean(wallet.is_default),
                };
                this.modalOpen = true;
            },
            copyAccount(id, text) {
                if (!text) return;
                navigator.clipboard.writeText(text);
                this.copiedId = id;
                setTimeout(() => { if (this.copiedId === id) this.copiedId = null; }, 1800);
            }
        }"
        class="flex-1 flex flex-col"
    >
        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm flex items-center justify-between border-b border-border-subtle/50 dark:border-dark-border/40">
            <div class="flex items-center gap-space-xs">
                <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight">Dompet &amp; Valas</h1>
            </div>

            <div class="flex items-center gap-2">
                <!-- Theme Toggle -->
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

                <!-- Add Wallet Button -->
                <button
                    type="button"
                    @click="openCreate()"
                    aria-label="Tambah Dompet"
                    class="w-9 h-9 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas flex items-center justify-center font-bold shadow-sm hover:opacity-95 active:scale-95 transition-transform duration-150 cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[20px]">add</span>
                </button>
            </div>
        </header>

        <!-- Main Content Canvas -->
        <div class="flex-1 px-gutter pt-space-md flex flex-col gap-space-md">
            <!-- Flash Notifications -->
            @if (session('status'))
                <div class="flex items-center gap-2 p-3 rounded-2xl bg-semantic-profit-bg text-semantic-profit-text border border-accent-lime dark:bg-dark-surface-tint dark:text-dark-lime dark:border-dark-border text-body-sm">
                    <span class="material-symbols-outlined text-[18px] fill-1">check_circle</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center gap-2 p-3 rounded-2xl bg-semantic-loss-bg text-semantic-loss-text border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800 text-body-sm">
                    <span class="material-symbols-outlined text-[18px] fill-1">error</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Top Summary Bento Card -->
            <section class="bg-surface-card dark:bg-dark-surface rounded-3xl p-5 border border-border-subtle dark:border-dark-border flex flex-col gap-space-sm relative overflow-hidden shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-[#294336] flex items-center justify-center text-forest-primary dark:text-dark-lime">
                            <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span>
                        </div>
                        <span class="text-label-xs uppercase font-semibold text-text-secondary dark:text-dark-text-secondary tracking-wider">TOTAL SALDO IDR</span>
                    </div>
                    <span class="w-2 h-2 rounded-full bg-forest-primary dark:bg-dark-lime animate-pulse"></span>
                </div>

                <div>
                    <div class="text-headline-lg-mobile font-bold tracking-tight text-text-primary dark:text-dark-text-primary tabular-nums">
                        Rp {{ number_format($currencyTotals['IDR'], 0, ',', '.') }}
                    </div>
                    <p class="text-body-sm text-text-muted dark:text-dark-text-muted mt-0.5">
                        Total aset likuid dari {{ $wallets->where('is_active', true)->count() }} dompet aktif
                    </p>
                </div>

                <!-- Currency Breakdown Chips Row -->
                <div class="flex items-center gap-2 pt-1 overflow-x-auto no-scrollbar">
                    <div class="bg-surface-tint dark:bg-dark-surface-tint text-text-primary dark:text-dark-text-primary border border-border-subtle dark:border-[#294336] text-label-md font-semibold px-3 py-1 rounded-full whitespace-nowrap">
                        IDR: <span class="tabular-nums text-text-secondary dark:text-dark-text-secondary font-normal">Rp {{ number_format($currencyTotals['IDR'], 0, ',', '.') }}</span>
                    </div>
                    @if ($currencyCounts['USD'] > 0)
                        <div class="bg-surface-tint dark:bg-dark-surface-tint text-text-primary dark:text-dark-text-primary border border-border-subtle dark:border-[#294336] text-label-md font-semibold px-3 py-1 rounded-full whitespace-nowrap">
                            USD: <span class="tabular-nums text-text-secondary dark:text-dark-text-secondary font-normal">${{ number_format($currencyTotals['USD'], 2, '.', ',') }}</span>
                        </div>
                    @endif
                    @if ($currencyCounts['USDT'] > 0)
                        <div class="bg-surface-tint dark:bg-dark-surface-tint text-text-primary dark:text-dark-text-primary border border-border-subtle dark:border-[#294336] text-label-md font-semibold px-3 py-1 rounded-full whitespace-nowrap">
                            USDT: <span class="tabular-nums text-text-secondary dark:text-dark-text-secondary font-normal">{{ rtrim(rtrim(number_format($currencyTotals['USDT'], 4, '.', ','), '0'), '.') }}</span>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Currency Filter Tabs -->
            <section class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                <button
                    type="button"
                    @click="filterCurrency = 'ALL'"
                    :class="filterCurrency === 'ALL'
                        ? 'bg-forest-primary text-white dark:bg-dark-lime dark:text-dark-canvas font-semibold'
                        : 'bg-surface-card text-text-secondary border border-border-subtle dark:bg-dark-surface dark:text-dark-text-secondary dark:border-dark-border hover:text-text-primary dark:hover:text-dark-text-primary'"
                    class="text-label-md rounded-full px-4 py-1.5 whitespace-nowrap active:scale-95 transition-all cursor-pointer"
                >
                    Semua ({{ $currencyCounts['ALL'] }})
                </button>
                <button
                    type="button"
                    @click="filterCurrency = 'IDR'"
                    :class="filterCurrency === 'IDR'
                        ? 'bg-forest-primary text-white dark:bg-dark-lime dark:text-dark-canvas font-semibold'
                        : 'bg-surface-card text-text-secondary border border-border-subtle dark:bg-dark-surface dark:text-dark-text-secondary dark:border-dark-border hover:text-text-primary dark:hover:text-dark-text-primary'"
                    class="text-label-md rounded-full px-4 py-1.5 whitespace-nowrap active:scale-95 transition-all cursor-pointer"
                >
                    IDR ({{ $currencyCounts['IDR'] }})
                </button>
                <button
                    type="button"
                    @click="filterCurrency = 'USD'"
                    :class="filterCurrency === 'USD'
                        ? 'bg-forest-primary text-white dark:bg-dark-lime dark:text-dark-canvas font-semibold'
                        : 'bg-surface-card text-text-secondary border border-border-subtle dark:bg-dark-surface dark:text-dark-text-secondary dark:border-dark-border hover:text-text-primary dark:hover:text-dark-text-primary'"
                    class="text-label-md rounded-full px-4 py-1.5 whitespace-nowrap active:scale-95 transition-all cursor-pointer"
                >
                    USD ({{ $currencyCounts['USD'] }})
                </button>
                <button
                    type="button"
                    @click="filterCurrency = 'USDT'"
                    :class="filterCurrency === 'USDT'
                        ? 'bg-forest-primary text-white dark:bg-dark-lime dark:text-dark-canvas font-semibold'
                        : 'bg-surface-card text-text-secondary border border-border-subtle dark:bg-dark-surface dark:text-dark-text-secondary dark:border-dark-border hover:text-text-primary dark:hover:text-dark-text-primary'"
                    class="text-label-md rounded-full px-4 py-1.5 whitespace-nowrap active:scale-95 transition-all cursor-pointer"
                >
                    USDT ({{ $currencyCounts['USDT'] }})
                </button>
            </section>

            <!-- Wallets List or Empty State -->
            @if ($wallets->isEmpty())
                <section class="bg-surface-card dark:bg-dark-surface border border-dashed border-border-subtle dark:border-dark-border rounded-3xl p-8 text-center flex flex-col items-center gap-3">
                    <div class="w-14 h-14 rounded-2xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime">
                        <span class="material-symbols-outlined text-[28px]">account_balance_wallet</span>
                    </div>
                    <div>
                        <h2 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Belum Ada Dompet</h2>
                        <p class="text-body-sm text-text-secondary dark:text-dark-text-secondary mt-1">
                            Tambahkan dompet pertamamu (seperti BCA, Tunai, atau GoPay) untuk mulai mencatat transaksi.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="openCreate()"
                        class="mt-1 px-5 py-2.5 rounded-xl bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold flex items-center gap-2 active:scale-95 transition-transform cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Tambah Dompet Pertama</span>
                    </button>
                </section>
            @else
                <section class="flex flex-col gap-3">
                    <div class="flex items-center justify-between px-1">
                        <h2 class="text-title-md font-semibold text-text-primary dark:text-dark-text-primary">Daftar Dompet</h2>
                        <span class="bg-surface-tint dark:bg-dark-surface-tint text-text-secondary dark:text-dark-text-secondary border border-border-subtle dark:border-[#294336] text-label-xs px-2.5 py-0.5 rounded-full">
                            {{ $wallets->count() }} Dompet
                        </span>
                    </div>

                    @foreach ($wallets as $wallet)
                        @php
                            $formattedBalance = match ($wallet->currency) {
                                'IDR' => 'Rp ' . number_format((float) $wallet->balance, 0, ',', '.'),
                                'USD' => '$' . number_format((float) $wallet->balance, 2, '.', ','),
                                default => rtrim(rtrim(number_format((float) $wallet->balance, 4, '.', ','), '0'), '.') . ' ' . $wallet->currency,
                            };

                            $typeLabel = match ($wallet->type) {
                                'bank' => 'Bank',
                                'cash' => 'Tunai / Cash',
                                'e_wallet' => 'E-Wallet',
                                'investment_cash' => 'Kas Investasi',
                                default => ucfirst($wallet->type),
                            };

                            $walletPayload = [
                                'id' => $wallet->id,
                                'name' => $wallet->name,
                                'type' => $wallet->type,
                                'currency' => $wallet->currency,
                                'balance' => (float) $wallet->balance,
                                'account_number' => $wallet->account_number,
                                'color' => $wallet->color,
                                'is_default' => $wallet->is_default,
                            ];
                        @endphp

                        @if ($wallet->is_default)
                            <!-- Featured Hero Card for Default Wallet -->
                            <div
                                x-show="filterCurrency === 'ALL' || filterCurrency === '{{ $wallet->currency }}'"
                                class="bg-gradient-to-br from-forest-primary to-forest-deep dark:from-dark-forest dark:to-forest-deep border border-forest-primary dark:border-[#2F6F52] rounded-2xl p-4 text-white relative overflow-hidden shadow-lg"
                            >
                                <!-- Subtle Geometric Watermark Rings -->
                                <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full border-8 border-white/5 pointer-events-none"></div>
                                <div class="absolute -right-2 -top-2 w-20 h-20 rounded-full border-4 border-white/5 pointer-events-none"></div>

                                <div class="relative z-10 flex flex-col gap-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-white backdrop-blur-sm">
                                                <span class="material-symbols-outlined text-[19px]">{{ $wallet->icon }}</span>
                                            </div>
                                            <div>
                                                <div class="text-label-lg font-bold text-white leading-tight">{{ $wallet->name }}</div>
                                                <span class="text-label-xs text-white/70">{{ $typeLabel }} &bull; {{ $wallet->currency }}</span>
                                            </div>
                                        </div>

                                        <span class="bg-accent-lime dark:bg-dark-lime text-forest-deep dark:text-dark-canvas text-[10px] font-bold px-2.5 py-0.5 rounded-full tracking-wide">
                                            ★ Default
                                        </span>
                                    </div>

                                    <div class="pt-0.5">
                                        <span class="text-label-xs text-white/70 block">Saldo Tersedia</span>
                                        <div class="text-headline-md font-bold tabular-nums text-white tracking-tight">
                                            {{ $formattedBalance }}
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-white/10">
                                        @if ($wallet->account_number)
                                            <div class="flex items-center gap-1.5 text-label-md text-white/80 font-mono">
                                                <span>{{ $wallet->account_number }}</span>
                                                <button
                                                    type="button"
                                                    @click="copyAccount({{ $wallet->id }}, '{{ $wallet->account_number }}')"
                                                    class="hover:text-white active:scale-95 transition-transform cursor-pointer"
                                                    title="Salin nomor rekening"
                                                >
                                                    <span class="material-symbols-outlined text-[14px]" x-show="copiedId !== {{ $wallet->id }}">content_copy</span>
                                                    <span class="material-symbols-outlined text-[14px] text-accent-lime" x-show="copiedId === {{ $wallet->id }}" x-cloak>check</span>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-label-xs text-white/60">Dompet Utama Catat Cepat</span>
                                        @endif

                                        <div class="flex items-center gap-1.5">
                                            <button
                                                type="button"
                                                @click='openEdit(@json($walletPayload))'
                                                aria-label="Edit {{ $wallet->name }}"
                                                class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white active:scale-95 transition-all cursor-pointer"
                                            >
                                                <span class="material-symbols-outlined text-[14px]">edit</span>
                                            </button>
                                            <form method="POST" action="{{ route('wallets.destroy', $wallet) }}" onsubmit="return confirm('Hapus dompet {{ $wallet->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    aria-label="Hapus {{ $wallet->name }}"
                                                    class="w-7 h-7 rounded-full bg-white/10 hover:bg-rose-500/80 flex items-center justify-center text-white active:scale-95 transition-all cursor-pointer"
                                                >
                                                    <span class="material-symbols-outlined text-[14px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Standard Wallet Card -->
                            <div
                                x-show="filterCurrency === 'ALL' || filterCurrency === '{{ $wallet->currency }}'"
                                class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-4 flex flex-col gap-3 shadow-xs"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-9 h-9 rounded-xl flex items-center justify-center text-white shadow-xs"
                                            style="background-color: {{ $wallet->color }};"
                                        >
                                            <span class="material-symbols-outlined text-[19px]">{{ $wallet->icon }}</span>
                                        </div>
                                        <div>
                                            <h3 class="text-label-lg font-semibold text-text-primary dark:text-dark-text-primary leading-tight">{{ $wallet->name }}</h3>
                                            <span class="text-label-xs text-text-muted dark:text-dark-text-muted">{{ $typeLabel }} &bull; {{ $wallet->currency }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            @click='openEdit(@json($walletPayload))'
                                            aria-label="Edit {{ $wallet->name }}"
                                            class="w-7 h-7 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime active:scale-95 transition-all cursor-pointer"
                                        >
                                            <span class="material-symbols-outlined text-[14px]">edit</span>
                                        </button>
                                        <form method="POST" action="{{ route('wallets.destroy', $wallet) }}" onsubmit="return confirm('Hapus dompet {{ $wallet->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                aria-label="Hapus {{ $wallet->name }}"
                                                class="w-7 h-7 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-semantic-loss-text dark:hover:text-rose-400 active:scale-95 transition-all cursor-pointer"
                                            >
                                                <span class="material-symbols-outlined text-[14px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-label-xs text-text-muted dark:text-dark-text-muted block">Saldo Tersedia</span>
                                    <div class="text-headline-md font-bold text-text-primary dark:text-dark-text-primary tabular-nums">
                                        {{ $formattedBalance }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-border-subtle/70 dark:border-dark-border/60">
                                    @if ($wallet->account_number)
                                        <div class="flex items-center gap-1.5 text-label-xs text-text-secondary dark:text-dark-text-muted font-mono">
                                            <span>{{ $wallet->account_number }}</span>
                                            <button
                                                type="button"
                                                @click="copyAccount({{ $wallet->id }}, '{{ $wallet->account_number }}')"
                                                class="hover:text-forest-primary dark:hover:text-dark-lime cursor-pointer"
                                                title="Salin nomor rekening"
                                            >
                                                <span class="material-symbols-outlined text-[14px]" x-show="copiedId !== {{ $wallet->id }}">content_copy</span>
                                                <span class="material-symbols-outlined text-[14px] text-forest-primary dark:text-dark-lime" x-show="copiedId === {{ $wallet->id }}" x-cloak>check</span>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-label-xs text-text-muted dark:text-dark-text-muted">Aktif</span>
                                    @endif

                                    <form method="POST" action="{{ route('wallets.set-default', $wallet) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="text-forest-primary dark:text-dark-lime text-label-md font-semibold hover:underline active:scale-95 transition-transform cursor-pointer"
                                        >
                                            Jadikan Default
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </section>
            @endif
        </div>

        <!-- Add / Edit Wallet Bottom Sheet Modal -->
        @include('wallets.partials.wallet-modal')
    </div>
</x-layouts.mobile>
