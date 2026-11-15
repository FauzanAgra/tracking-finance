<x-layouts.mobile title="Utang & Piutang" activeNav="transactions">
    @php
        $today = now()->toDateString();
        $defaultWalletId = $defaultWallet?->id ? (string) $defaultWallet->id : '';
    @endphp

    <div
        x-data="{
            filterTab: 'all',
            createModalOpen: @js($errors->hasAny(['person_name', 'type', 'total_amount', 'issue_date', 'due_date'])),
            paymentModalOpen: false,
            historyModalOpen: false,
            writeOffModalOpen: false,
            activeDebt: null,
            paymentForm: {
                wallet_id: @js($defaultWalletId),
                amount: '',
                payment_date: @js($today),
                notes: '',
            },
            writeOffNotes: '',
            openCreateModal() {
                this.createModalOpen = true;
            },
            openPaymentModal(debt) {
                this.activeDebt = debt;
                this.paymentForm = {
                    wallet_id: @js($defaultWalletId),
                    amount: debt.remaining > 0 ? String(debt.remaining) : '',
                    payment_date: @js($today),
                    notes: '',
                };
                this.paymentModalOpen = true;
            },
            openHistoryModal(debt) {
                this.activeDebt = debt;
                this.historyModalOpen = true;
            },
            openWriteOffModal(debt) {
                this.activeDebt = debt;
                this.writeOffNotes = '';
                this.writeOffModalOpen = true;
            }
        }"
        class="flex-1 flex flex-col"
    >
        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm flex items-center justify-between border-b border-border-subtle/50 dark:border-dark-border/40">
            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('transactions.index') }}"
                    aria-label="Kembali ke Riwayat"
                    class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-primary dark:text-dark-text-primary active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight">Utang &amp; Piutang</h1>
            </div>

            <div class="flex items-center gap-2">
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
                    @click="openCreateModal()"
                    class="flex items-center gap-1 bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas px-3 py-2 rounded-full text-label-md font-semibold shadow-sm active:scale-95 transition-all cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Tambah</span>
                </button>
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

            <!-- 2-Column Top Summary Bento Cards -->
            <section aria-label="Ringkasan Utang dan Piutang" class="grid grid-cols-2 gap-3">
                <div class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-3.5 flex flex-col justify-between shadow-xs">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-label-xs text-text-muted dark:text-dark-text-secondary uppercase tracking-wider font-semibold block">Total Piutang</span>
                            <span class="text-[10px] text-text-muted dark:text-dark-text-muted">Hak Kita</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-accent-lime-soft dark:bg-forest-primary flex items-center justify-center text-forest-primary dark:text-dark-lime shrink-0">
                            <span class="material-symbols-outlined text-[16px]">call_received</span>
                        </div>
                    </div>
                    <div class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tabular-nums mt-2 truncate">
                        Rp {{ number_format($totalReceivableRemaining, 0, ',', '.') }}
                    </div>
                    <div class="mt-2 pt-2 border-t border-border-subtle dark:border-dark-border">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-accent-lime-soft dark:bg-forest-primary text-forest-primary dark:text-dark-lime text-[10px] font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-forest-primary dark:bg-dark-lime"></span>
                            {{ $activeReceivableCount }} Aktif
                        </span>
                    </div>
                </div>

                <div class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-3.5 flex flex-col justify-between shadow-xs">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-label-xs text-text-muted dark:text-dark-text-secondary uppercase tracking-wider font-semibold block">Total Utang</span>
                            <span class="text-[10px] text-text-muted dark:text-dark-text-muted">Kewajiban</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-[#3B1822] flex items-center justify-center text-rose-600 dark:text-[#FDA4AF] shrink-0">
                            <span class="material-symbols-outlined text-[16px]">call_made</span>
                        </div>
                    </div>
                    <div class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tabular-nums mt-2 truncate">
                        Rp {{ number_format($totalPayableRemaining, 0, ',', '.') }}
                    </div>
                    <div class="mt-2 pt-2 border-t border-border-subtle dark:border-dark-border">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $activePayableCount === 0 ? 'bg-accent-lime-soft dark:bg-forest-primary text-forest-primary dark:text-dark-lime' : 'bg-rose-100 dark:bg-[#3B1822] text-rose-700 dark:text-[#FDA4AF]' }} text-[10px] font-semibold">
                            {{ $activePayableCount === 0 ? 'Bebas Utang' : $activePayableCount.' Aktif' }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Status Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                @foreach (['all' => 'Semua', 'receivable' => 'Piutang', 'payable' => 'Utang', 'settled' => 'Lunas / Diikhlaskan'] as $tabKey => $tabLabel)
                    <button
                        type="button"
                        @click="filterTab = '{{ $tabKey }}'"
                        :class="filterTab === '{{ $tabKey }}' ? 'bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas border-forest-primary dark:border-dark-lime' : 'bg-surface-card dark:bg-dark-surface text-text-secondary dark:text-dark-text-secondary border-border-subtle dark:border-dark-border'"
                        class="px-3.5 py-1.5 rounded-full text-label-md font-semibold border whitespace-nowrap active:scale-95 transition-all cursor-pointer"
                    >
                        {{ $tabLabel }}
                    </button>
                @endforeach
            </div>

            <!-- Debt / Receivable Cards -->
            @if ($debts->isEmpty())
                <div class="bg-surface-card dark:bg-dark-surface border border-dashed border-border-subtle dark:border-dark-border rounded-3xl p-8 text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-accent-lime-soft dark:bg-dark-surface-tint flex items-center justify-center text-forest-primary dark:text-dark-lime">
                        <span class="material-symbols-outlined text-[28px]">handshake</span>
                    </div>
                    <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Belum Ada Catatan</h3>
                    <p class="text-body-sm text-text-muted dark:text-dark-text-muted">
                        Catat piutang teman (misal: kasus Jamal) atau utang pribadi lengkap dengan histori cicilan per dompet.
                    </p>
                    <button
                        type="button"
                        @click="openCreateModal()"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-semibold active:scale-95 transition-transform cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Tambah Catatan Pertama</span>
                    </button>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($debts as $debt)
                        @php
                            $paidAmount = (float) $debt->payments->sum('amount');
                            $writtenOffAmount = (float) $debt->written_off_amount;
                            $totalAmount = (float) $debt->total_amount;
                            $remaining = max(0, $totalAmount - $paidAmount - $writtenOffAmount);
                            $progressPercent = $totalAmount > 0 ? min(100, (int) round((($paidAmount + $writtenOffAmount) / $totalAmount) * 100)) : 0;
                            $isSettled = in_array($debt->status, ['paid', 'written_off'], true);
                            $initials = collect(explode(' ', trim($debt->person_name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

                            $statusLabel = match ($debt->status) {
                                'paid' => 'Lunas',
                                'written_off' => 'Diikhlaskan',
                                'partially_paid' => 'Dicicil',
                                default => 'Belum Bayar',
                            };
                            $statusClass = match ($debt->status) {
                                'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
                                'written_off' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
                                'partially_paid' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
                                default => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
                            };

                            $debtPayload = [
                                'id' => $debt->id,
                                'person_name' => $debt->person_name,
                                'type' => $debt->type,
                                'total_amount' => $totalAmount,
                                'paid_amount' => $paidAmount,
                                'written_off_amount' => $writtenOffAmount,
                                'remaining' => $remaining,
                                'status' => $debt->status,
                                'payments' => $debt->payments->map(fn ($p) => [
                                    'id' => $p->id,
                                    'amount' => (float) $p->amount,
                                    'payment_date' => $p->payment_date->translatedFormat('D, d M Y'),
                                    'wallet_name' => $p->wallet?->name ?? 'Dompet',
                                    'notes' => $p->notes,
                                ])->values()->all(),
                            ];
                        @endphp

                        <section
                            x-show="filterTab === 'all' || (filterTab === 'settled' ? {{ $isSettled ? 'true' : 'false' }} : (filterTab === '{{ $debt->type }}' && !{{ $isSettled ? 'true' : 'false' }}))"
                            class="bg-surface-card dark:bg-dark-surface rounded-3xl p-4 border border-border-subtle dark:border-dark-border shadow-xs space-y-3.5"
                        >
                            <!-- Card Header -->
                            <div class="flex items-center justify-between gap-2.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-accent-lime-soft dark:bg-dark-forest text-forest-primary dark:text-dark-lime font-bold text-label-lg flex items-center justify-center shrink-0">
                                        {{ $initials ?: '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary truncate">{{ $debt->person_name }}</h3>
                                            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-semibold {{ $debt->type === 'receivable' ? 'bg-forest-primary/10 text-forest-primary dark:bg-dark-forest dark:text-dark-lime' : 'bg-rose-100 text-rose-700 dark:bg-[#3B1822] dark:text-[#FDA4AF]' }}">
                                                {{ $debt->type === 'receivable' ? 'Piutang' : 'Utang' }}
                                            </span>
                                        </div>
                                        @if ($debt->notes)
                                            <p class="text-label-xs text-text-muted dark:text-dark-text-muted truncate">{{ $debt->notes }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                    @if ($debt->payments->isEmpty())
                                        <form method="POST" action="{{ route('debts.destroy', $debt) }}" onsubmit="return confirm('Hapus catatan {{ $debt->person_name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="Hapus catatan" class="w-7 h-7 rounded-full hover:bg-rose-50 dark:hover:bg-rose-950/50 flex items-center justify-center text-text-muted hover:text-rose-600 cursor-pointer">
                                                <span class="material-symbols-outlined text-[15px]">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <!-- 3-Column Metrics Strip -->
                            <div class="grid grid-cols-3 gap-2 bg-surface-tint dark:bg-dark-sidebar rounded-xl p-3 border border-border-subtle dark:border-dark-border">
                                <div>
                                    <div class="text-label-xs text-text-muted dark:text-dark-text-secondary">Total Pokok</div>
                                    <div class="text-label-md font-semibold text-text-primary dark:text-dark-text-primary tabular-nums mt-0.5">
                                        Rp {{ number_format($totalAmount, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-label-xs text-text-muted dark:text-dark-text-secondary">Sudah Bayar</div>
                                    <div class="text-label-md font-semibold text-emerald-600 dark:text-dark-lime tabular-nums mt-0.5">
                                        Rp {{ number_format($paidAmount, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-label-xs text-text-muted dark:text-dark-text-secondary">
                                        {{ $debt->status === 'written_off' ? 'Diikhlaskan' : 'Sisa Tagihan' }}
                                    </div>
                                    <div class="text-label-md font-bold {{ $debt->status === 'written_off' ? 'text-amber-600 dark:text-amber-300' : 'text-rose-600 dark:text-[#FDA4AF]' }} tabular-nums mt-0.5">
                                        Rp {{ number_format($debt->status === 'written_off' ? $writtenOffAmount : $remaining, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bar & Dates -->
                            <div class="space-y-1.5">
                                <div class="w-full h-2 bg-surface-tint dark:bg-dark-surface-tint rounded-full overflow-hidden">
                                    <div class="h-full bg-forest-primary dark:bg-dark-lime rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%;"></div>
                                </div>
                                <div class="flex justify-between items-center text-label-xs text-text-muted dark:text-dark-text-secondary">
                                    <span>Tanggal: {{ $debt->issue_date->translatedFormat('d M Y') }}</span>
                                    @if ($debt->due_date)
                                        <span class="font-medium text-forest-primary dark:text-dark-lime">Tempo: {{ $debt->due_date->translatedFormat('d M Y') }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Inline Preview of Latest 2 Payments (DEBT-004) -->
                            @if ($debt->payments->isNotEmpty())
                                <div class="pt-2 border-t border-border-subtle dark:border-dark-border space-y-1.5">
                                    <div class="text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary uppercase tracking-wider">
                                        Riwayat Cicilan Terakhir
                                    </div>
                                    @foreach ($debt->payments->take(2) as $payment)
                                        <div class="flex items-center justify-between py-1 text-body-sm">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-[15px]">check_circle</span>
                                                <span class="text-text-primary dark:text-dark-text-primary truncate">{{ $payment->payment_date->translatedFormat('D, d M') }}</span>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-accent-lime-soft dark:bg-dark-forest text-forest-primary dark:text-dark-lime border border-border-subtle dark:border-dark-border">
                                                    {{ $payment->wallet?->name }}
                                                </span>
                                            </div>
                                            <span class="font-bold tabular-nums text-text-primary dark:text-dark-text-primary shrink-0">
                                                {{ $debt->type === 'receivable' ? '+' : '-' }}Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Action Footer -->
                            <div class="space-y-2 pt-1">
                                @if (! $isSettled)
                                    <button
                                        type="button"
                                        @click="openPaymentModal(@js($debtPayload))"
                                        class="w-full py-2.5 px-4 rounded-xl bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-md font-bold flex items-center justify-center gap-1.5 shadow-xs active:scale-[0.98] transition-all cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-[17px]">add_circle</span>
                                        <span>{{ $debt->type === 'receivable' ? 'Catat Cicilan Masuk' : 'Bayar Cicilan Utang' }}</span>
                                    </button>
                                @endif

                                <div class="grid {{ $isSettled ? 'grid-cols-1' : 'grid-cols-2' }} gap-2">
                                    <button
                                        type="button"
                                        @click="openHistoryModal(@js($debtPayload))"
                                        class="py-2 px-3 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-label-md font-semibold text-text-primary dark:text-dark-text-primary flex items-center justify-center gap-1.5 active:scale-95 transition-all cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-[16px]">history</span>
                                        <span>Riwayat ({{ $debt->payments->count() }})</span>
                                    </button>

                                    @if (! $isSettled)
                                        <button
                                            type="button"
                                            @click="openWriteOffModal(@js($debtPayload))"
                                            class="py-2 px-3 rounded-xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border text-label-md font-semibold text-text-secondary dark:text-dark-text-secondary hover:text-amber-600 dark:hover:text-amber-300 flex items-center justify-center gap-1.5 active:scale-95 transition-all cursor-pointer"
                                        >
                                            <span class="material-symbols-outlined text-[16px]">volunteer_activism</span>
                                            <span>Ikhlaskan Sisa</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </div>

        @include('debts.partials.modals')
    </div>
</x-layouts.mobile>
