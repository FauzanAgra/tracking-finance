<x-layouts.mobile title="Transaksi Rutin" activeNav="transactions">
    @php
        $today = now()->toDateString();
    @endphp

    <div
        x-data="{
            modalOpen: false,
            form: {
                type: 'expense',
                amount: '',
                wallet_id: @js($wallets->first()?->id ? (string) $wallets->first()->id : ''),
                destination_wallet_id: '',
                category_id: '',
                fee: '0',
                frequency: 'monthly',
                start_date: @js($today),
                next_run_date: @js($today),
                description: '',
            },
            openModal() {
                this.form = {
                    type: 'expense',
                    amount: '',
                    wallet_id: @js($wallets->first()?->id ? (string) $wallets->first()->id : ''),
                    destination_wallet_id: '',
                    category_id: '',
                    fee: '0',
                    frequency: 'monthly',
                    start_date: @js($today),
                    next_run_date: @js($today),
                    description: '',
                };
                this.modalOpen = true;
            }
        }"
        class="flex-1 flex flex-col"
    >
        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm border-b border-border-subtle/50 dark:border-dark-border/40 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('transactions.index') }}"
                    aria-label="Kembali ke transaksi"
                    class="w-8 h-8 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime transition-colors"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight leading-tight">Transaksi Rutin</h1>
                    <span class="text-label-xs text-text-muted dark:text-dark-text-secondary">Pencatatan otomatis berkala</span>
                </div>
            </div>

            <button
                type="button"
                @click="openModal()"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-xs font-semibold shadow-xs active:scale-95 transition-transform cursor-pointer"
            >
                <span class="material-symbols-outlined text-[16px]">add</span>
                <span>Jadwal Baru</span>
            </button>
        </header>

        <div class="px-gutter py-space-md space-y-4">
            <!-- 2-Column KPI Strip -->
            <section aria-label="Ringkasan Rutin" class="grid grid-cols-2 gap-2.5">
                <div class="bg-surface-card dark:bg-dark-surface p-3.5 rounded-2xl border border-border-subtle dark:border-dark-border shadow-xs">
                    <span class="text-label-xs text-text-muted dark:text-dark-text-secondary block">Jadwal Aktif</span>
                    <span class="text-headline-sm font-bold text-forest-primary dark:text-dark-lime tabular-nums">
                        {{ $totalActive }}
                    </span>
                    <p class="text-[11px] text-text-muted dark:text-dark-text-muted mt-0.5">dari {{ $recurringTransactions->count() }} total jadwal</p>
                </div>

                <div class="bg-surface-card dark:bg-dark-surface p-3.5 rounded-2xl border border-border-subtle dark:border-dark-border shadow-xs">
                    <span class="text-label-xs text-text-muted dark:text-dark-text-secondary block">Estimasi Beban / Bln</span>
                    <span class="text-headline-sm font-bold text-rose-600 dark:text-[#FDA4AF] tabular-nums truncate block">
                        Rp {{ number_format($monthlyEstimate, 0, ',', '.') }}
                    </span>
                    <p class="text-[11px] text-text-muted dark:text-dark-text-muted mt-0.5">Pengeluaran rutin aktif</p>
                </div>
            </section>

            <!-- Recurring List -->
            @if ($recurringTransactions->isEmpty())
                <div class="bg-surface-card dark:bg-dark-surface border border-dashed border-border-subtle dark:border-dark-border rounded-3xl p-8 text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-accent-lime-soft dark:bg-dark-surface-tint flex items-center justify-center text-forest-primary dark:text-dark-lime">
                        <span class="material-symbols-outlined text-[28px]">update</span>
                    </div>
                    <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Belum Ada Transaksi Rutin</h3>
                    <p class="text-body-sm text-text-muted dark:text-dark-text-muted">
                        Buat pengeluaran atau pemasukan rutin otomatis seperti tagihan kos, Netflix, atau gaji bulanan.
                    </p>
                    <button
                        type="button"
                        @click="openModal()"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-semibold active:scale-95 transition-transform cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Tambah Transaksi Rutin</span>
                    </button>
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach ($recurringTransactions as $rec)
                        @php
                            $isInc = $rec->type === 'income';
                            $isTrf = $rec->type === 'transfer';
                            $freqBadge = match ($rec->frequency) {
                                'daily' => 'Harian',
                                'weekly' => 'Mingguan',
                                'monthly' => 'Bulanan',
                                default => 'Bulanan',
                            };
                            $badgeColor = $rec->category?->color ?? ($isInc ? '#10B981' : ($isTrf ? '#3B82F6' : '#F43F5E'));
                            $iconName = $rec->category?->icon ?? ($isInc ? 'payments' : ($isTrf ? 'swap_horiz' : 'receipt_long'));
                        @endphp

                        <article class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-3.5 shadow-xs flex items-center justify-between gap-3 {{ $rec->is_active ? '' : 'opacity-60 bg-surface-tint/50 dark:bg-dark-surface/50' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-xs"
                                    style="background-color: {{ $badgeColor }};"
                                >
                                    <span class="material-symbols-outlined text-[20px]">{{ $iconName }}</span>
                                </div>

                                <div class="min-w-0">
                                    <h3 class="text-body-md font-semibold text-text-primary dark:text-dark-text-primary truncate leading-tight">
                                        {{ $rec->description ?: ($rec->category?->name ?? 'Transaksi Rutin') }}
                                    </h3>
                                    <div class="flex items-center gap-1.5 mt-0.5 text-label-xs text-text-muted dark:text-dark-text-muted flex-wrap">
                                        <span class="px-1.5 py-0.2 rounded-md bg-surface-tint dark:bg-dark-surface-tint text-text-secondary dark:text-dark-text-secondary font-semibold text-[10px]">
                                            {{ $freqBadge }}
                                        </span>
                                        <span>&bull;</span>
                                        <span>{{ $rec->wallet?->name }}</span>
                                        <span>&bull;</span>
                                        <span class="tabular-nums">Jatuh tempo: {{ $rec->next_run_date->translatedFormat('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <div class="text-right">
                                    <span class="text-body-md font-bold tabular-nums block {{ $isInc ? 'text-emerald-600 dark:text-dark-lime' : ($isTrf ? 'text-sky-600 dark:text-sky-300' : 'text-rose-600 dark:text-[#FDA4AF]') }}">
                                        {{ $isInc ? '+' : ($isTrf ? '' : '-') }}Rp {{ number_format((float) $rec->amount, 0, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Toggle Active Switch / Button -->
                                <form method="POST" action="{{ route('recurring.toggle', $rec) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        title="{{ $rec->is_active ? 'Nonaktifkan sementara' : 'Aktifkan kembali' }}"
                                        aria-label="Toggle status aktif"
                                        class="w-7 h-7 rounded-full flex items-center justify-center transition-colors cursor-pointer {{ $rec->is_active ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : 'bg-surface-tint dark:bg-dark-surface-tint text-text-muted' }}"
                                    >
                                        <span class="material-symbols-outlined text-[16px]">{{ $rec->is_active ? 'check' : 'pause' }}</span>
                                    </button>
                                </form>

                                <!-- Delete button using global modal -->
                                <button
                                    type="button"
                                    @click="$dispatch('confirm-delete', { label: '{{ addslashes($rec->description ?: 'Jadwal rutin ini') }}', action: '{{ route('recurring.destroy', $rec) }}' })"
                                    aria-label="Hapus transaksi rutin"
                                    class="w-7 h-7 rounded-full hover:bg-rose-50 dark:hover:bg-rose-950/50 flex items-center justify-center text-text-muted hover:text-rose-600 cursor-pointer transition-colors"
                                >
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Bottom-Sheet Modal: Tambah Transaksi Rutin -->
        <div
            x-show="modalOpen"
            x-cloak
            @keydown.escape.window="modalOpen = false"
            class="fixed inset-0 z-50 flex items-end justify-center"
            role="dialog"
            aria-modal="true"
        >
            <div
                x-show="modalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="modalOpen = false"
                class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"
            ></div>

            <section
                x-show="modalOpen"
                x-transition:enter="ease-out duration-250"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6 max-h-[92vh] overflow-y-auto no-scrollbar"
            >
                <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>

                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-[20px]">update</span>
                        <div>
                            <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary leading-none">Buat Jadwal Rutin</h2>
                            <span class="text-label-xs text-text-muted dark:text-dark-text-muted">Eksekusi otomatis saat jatuh tempo</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="modalOpen = false"
                        aria-label="Tutup modal"
                        class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('recurring.store') }}" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="type" x-model="form.type">

                    <!-- Type Selector -->
                    <div class="bg-surface-tint dark:bg-dark-sidebar p-1 rounded-xl border border-border-subtle dark:border-dark-border grid grid-cols-3 gap-1">
                        <button
                            type="button"
                            @click="form.type = 'expense'"
                            :class="form.type === 'expense' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                            class="py-1.5 rounded-lg text-label-xs font-semibold flex items-center justify-center cursor-pointer"
                        >
                            <span>Keluar</span>
                        </button>
                        <button
                            type="button"
                            @click="form.type = 'income'"
                            :class="form.type === 'income' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                            class="py-1.5 rounded-lg text-label-xs font-semibold flex items-center justify-center cursor-pointer"
                        >
                            <span>Masuk</span>
                        </button>
                        <button
                            type="button"
                            @click="form.type = 'transfer'"
                            :class="form.type === 'transfer' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                            class="py-1.5 rounded-lg text-label-xs font-semibold flex items-center justify-center cursor-pointer"
                        >
                            <span>Transfer</span>
                        </button>
                    </div>

                    <!-- Nominal Amount -->
                    <div class="rounded-2xl bg-surface-tint dark:bg-dark-sidebar border border-border-subtle dark:border-dark-border p-3 text-center">
                        <label class="block text-label-xs text-text-muted dark:text-dark-text-muted uppercase tracking-wider mb-1">
                            Nominal Rutin
                        </label>
                        <div class="flex items-center justify-center gap-1">
                            <span class="text-title-md font-bold text-text-secondary">Rp</span>
                            <x-money-input
                                name="amount"
                                model="form.amount"
                                :required="true"
                                placeholder="0"
                                class="w-44 bg-transparent text-center text-headline-lg-mobile font-bold text-text-primary dark:text-dark-text-primary focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Frequency & Start Date -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Frekuensi</label>
                            <select
                                name="frequency"
                                x-model="form.frequency"
                                required
                                class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary"
                            >
                                <option value="daily">Harian (Setiap Hari)</option>
                                <option value="weekly">Mingguan (Tiap Minggu)</option>
                                <option value="monthly">Bulanan (Tiap Bulan)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Mulai Tanggal</label>
                            <input
                                type="date"
                                name="start_date"
                                x-model="form.start_date"
                                required
                                class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary"
                            >
                        </div>
                    </div>

                    <!-- Wallets -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1" x-text="form.type === 'transfer' ? 'Dari Dompet' : 'Dompet'"></label>
                            <select
                                name="wallet_id"
                                x-model="form.wallet_id"
                                required
                                class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary"
                            >
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->currency }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="form.type === 'transfer'">
                            <label class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Ke Dompet</label>
                            <select
                                name="destination_wallet_id"
                                x-model="form.destination_wallet_id"
                                :required="form.type === 'transfer'"
                                class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary"
                            >
                                <option value="">-- Pilih Tujuan --</option>
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->currency }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="form.type !== 'transfer'">
                            <label class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Kategori</label>
                            <select
                                name="category_id"
                                x-model="form.category_id"
                                :required="form.type !== 'transfer'"
                                class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary"
                            >
                                <option value="">-- Pilih Kategori --</option>
                                <template x-if="form.type === 'expense'">
                                    <optgroup label="Pengeluaran">
                                        @foreach ($expenseCategories as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </template>
                                <template x-if="form.type === 'income'">
                                    <optgroup label="Pemasukan">
                                        @foreach ($incomeCategories as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Keterangan (Opsional)</label>
                        <input
                            type="text"
                            name="description"
                            x-model="form.description"
                            placeholder="Contoh: Langganan WiFi, Kos bulanan, Gaji..."
                            class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2 text-body-sm text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md active:scale-[0.98] transition-transform cursor-pointer"
                    >
                        Simpan Jadwal Rutin
                    </button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.mobile>
