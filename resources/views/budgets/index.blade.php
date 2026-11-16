<x-layouts.mobile title="Budget Bulanan" activeNav="transactions">
    <div
        x-data="{
            modalOpen: @js($errors->any()),
            form: {
                category_id: @js((string) old('category_id', '')),
                amount: @js(old('amount', '')),
                month_year: @js(old('month_year', $overview['month_year'])),
            },
            openBudgetModal(categoryId = '', amount = '') {
                this.form = {
                    category_id: categoryId ? String(categoryId) : '',
                    amount: amount ? String(amount) : '',
                    month_year: @js($overview['month_year']),
                };
                this.modalOpen = true;
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
                <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight">Budget Bulanan</h1>
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
                    @click="openBudgetModal()"
                    class="flex items-center gap-1 bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas px-3 py-2 rounded-full text-label-md font-semibold shadow-sm active:scale-95 transition-all cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Atur</span>
                </button>
            </div>
        </header>

        <div class="px-gutter py-space-md space-y-4">
            @if (session('status'))
                <div class="p-3 rounded-xl bg-semantic-profit-bg/60 dark:bg-dark-surface-tint border border-forest-primary/20 dark:border-dark-lime/30 text-forest-primary dark:text-dark-lime text-body-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            <!-- 2-Way Switcher: Budget Bulanan vs Kelola Kategori -->
            <div class="bg-surface-card dark:bg-dark-surface p-1 rounded-xl border border-border-subtle dark:border-dark-border flex items-center gap-1">
                <span class="flex-1 py-1.5 text-center rounded-lg bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime text-label-md font-semibold shadow-xs">
                    Budget Bulanan
                </span>
                <a
                    href="{{ route('categories.index') }}"
                    class="flex-1 py-1.5 text-center rounded-lg text-text-secondary dark:text-dark-text-secondary hover:text-text-primary dark:hover:text-dark-text-primary text-label-md font-semibold transition-colors"
                >
                    Kelola Kategori
                </a>
            </div>

            <!-- Month Selector -->
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-1 bg-surface-card dark:bg-dark-surface px-2.5 py-1.5 rounded-xl border border-border-subtle dark:border-dark-border shadow-xs">
                    <a
                        href="{{ route('budgets.index', ['month' => $prevMonth]) }}"
                        aria-label="Bulan sebelumnya"
                        class="p-1 rounded-lg text-text-secondary dark:text-dark-text-secondary hover:bg-surface-tint dark:hover:bg-dark-surface-tint"
                    >
                        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                    </a>
                    <span class="text-label-lg font-semibold text-text-primary dark:text-dark-text-primary px-2">
                        {{ $period->translatedFormat('F Y') }}
                    </span>
                    <a
                        href="{{ route('budgets.index', ['month' => $nextMonth]) }}"
                        aria-label="Bulan berikutnya"
                        class="p-1 rounded-lg text-text-secondary dark:text-dark-text-secondary hover:bg-surface-tint dark:hover:bg-dark-surface-tint"
                    >
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </a>
                </div>

                <a
                    href="{{ route('transactions.index', ['quick' => 1]) }}"
                    class="text-label-md font-semibold text-forest-primary dark:text-dark-lime hover:underline"
                >
                    + Catat Pengeluaran
                </a>
            </div>

            <!-- Overall Monthly Finance Health Card -->
            @php
                $overallHealth = $overview['overall_health'];
                $overallPct = $overview['overall_percentage'];
                $overallBadgeClass = match ($overallHealth) {
                    'over' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
                    'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
                    default => 'bg-emerald-100 text-emerald-700 dark:bg-[#1C3B2A] dark:text-[#86EFAC]',
                };
                $overallBarClass = match ($overallHealth) {
                    'over' => 'bg-rose-500',
                    'warning' => 'bg-amber-500',
                    default => 'bg-emerald-500 dark:bg-dark-lime',
                };
                $overallLabel = match ($overallHealth) {
                    'over' => $overallPct.'% Melebihi Batas',
                    'warning' => $overallPct.'% Waspada',
                    default => $overallPct.'% Terkendali (Aman)',
                };
            @endphp

            <section class="bg-surface-card dark:bg-dark-surface-tint rounded-2xl p-4 border border-border-subtle dark:border-dark-border shadow-xs space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-label-md text-text-muted dark:text-dark-text-secondary uppercase tracking-wider font-semibold">
                        Kesehatan Budget
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-label-xs font-semibold {{ $overallBadgeClass }}">
                        {{ $overallLabel }}
                    </span>
                </div>

                <div>
                    <div class="text-headline-lg-mobile font-bold text-text-primary dark:text-dark-text-primary tabular-nums tracking-tight">
                        Rp {{ number_format($overview['total_spent'], 0, ',', '.') }}
                    </div>
                    <p class="text-body-sm text-text-muted dark:text-dark-text-secondary mt-0.5">
                        / <span class="font-semibold text-text-primary dark:text-dark-text-primary tabular-nums">Rp {{ number_format($overview['total_budget'], 0, ',', '.') }}</span> Total Budget
                    </p>
                </div>

                <div class="w-full h-3 bg-surface-tint dark:bg-dark-canvas rounded-full overflow-hidden border border-border-subtle dark:border-dark-border">
                    <div
                        class="h-full rounded-full transition-all duration-500 {{ $overallBarClass }}"
                        style="width: {{ min(100, $overallPct) }}%;"
                    ></div>
                </div>

                <div class="flex items-center justify-between text-label-xs text-text-muted dark:text-dark-text-secondary">
                    <span>Sisa Anggaran</span>
                    <span class="font-bold tabular-nums text-text-primary dark:text-dark-text-primary">
                        Rp {{ number_format($overview['total_remaining'], 0, ',', '.') }}
                    </span>
                </div>
            </section>

            <!-- Per-Category Budgets -->
            @if ($overview['items']->isEmpty())
                <div class="bg-surface-card dark:bg-dark-surface border border-dashed border-border-subtle dark:border-dark-border rounded-3xl p-8 text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-accent-lime-soft dark:bg-dark-surface-tint flex items-center justify-center text-forest-primary dark:text-dark-lime">
                        <span class="material-symbols-outlined text-[28px]">savings</span>
                    </div>
                    <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Belum Ada Budget Bulan Ini</h3>
                    <p class="text-body-sm text-text-muted dark:text-dark-text-muted">
                        Tetapkan batas pengeluaran per kategori agar arus kas tetap terkendali (Hijau &lt;70%, Kuning 70-99%, Merah &ge;100%).
                    </p>
                    <button
                        type="button"
                        @click="openBudgetModal()"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-semibold active:scale-95 transition-transform cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Atur Budget Pertama</span>
                    </button>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($overview['items'] as $item)
                        @php
                            $budget = $item['budget'];
                            $category = $budget->category;
                            $pct = $item['percentage'];
                            $health = $item['health'];

                            $barColor = match ($health) {
                                'over' => 'bg-rose-500',
                                'warning' => 'bg-amber-500',
                                default => 'bg-emerald-500',
                            };
                            $badgeClass = match ($health) {
                                'over' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
                                'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
                                default => 'bg-emerald-100 text-emerald-700 dark:bg-[#1C3B2A] dark:text-[#86EFAC]',
                            };
                            $statusText = match ($health) {
                                'over' => 'Over '.$pct.'%',
                                'warning' => 'Waspada '.$pct.'%',
                                default => 'Aman '.$pct.'%',
                            };
                        @endphp

                        <article class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-4 space-y-3 shadow-xs">
                            <div class="flex items-center justify-between gap-2.5">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div
                                        class="w-9 h-9 rounded-xl flex items-center justify-center text-white shrink-0"
                                        style="background-color: {{ $category?->color ?? '#1E4D38' }};"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">{{ $category?->icon ?? 'tag' }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-title-md font-semibold text-text-primary dark:text-dark-text-primary truncate">{{ $category?->name ?? 'Kategori' }}</h3>
                                        <p class="text-label-xs text-text-muted dark:text-dark-text-muted tabular-nums">
                                            Rp {{ number_format($item['spent'], 0, ',', '.') }} / Rp {{ number_format($item['limit'], 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                                        {{ $statusText }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="openBudgetModal({{ $budget->category_id }}, {{ (float) $budget->amount }})"
                                        aria-label="Edit budget {{ $category?->name }}"
                                        class="w-7 h-7 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-[14px]">edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('budgets.destroy', $budget) }}" onsubmit="return confirm('Hapus budget {{ $category?->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            aria-label="Hapus budget"
                                            class="w-7 h-7 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-muted hover:text-rose-600 cursor-pointer"
                                        >
                                            <span class="material-symbols-outlined text-[14px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="w-full h-2.5 bg-surface-tint dark:bg-dark-canvas rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-300 {{ $barColor }}"
                                    style="width: {{ min(100, $pct) }}%;"
                                ></div>
                            </div>

                            <div class="flex items-center justify-between text-label-xs text-text-muted dark:text-dark-text-muted">
                                @if ($health === 'over')
                                    <span class="text-rose-600 dark:text-rose-300 font-semibold">Melebihi batas sebesar Rp {{ number_format($item['over_amount'], 0, ',', '.') }}</span>
                                @else
                                    <span>Tersisa Rp {{ number_format($item['remaining'], 0, ',', '.') }}</span>
                                @endif
                                <span class="tabular-nums">{{ $pct }}%</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Modal Atur / Edit Budget Bulanan -->
        <div
            x-show="modalOpen"
            x-cloak
            @keydown.escape.window="modalOpen = false"
            class="fixed inset-0 z-50 flex items-end justify-center"
            role="dialog"
            aria-modal="true"
        >
            <div @click="modalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
            <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6">
                <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Atur Budget Bulanan</h2>
                        <p class="text-label-xs text-text-muted dark:text-dark-text-muted">{{ $period->translatedFormat('F Y') }}</p>
                    </div>
                    <button type="button" @click="modalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
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

                <form method="POST" action="{{ route('budgets.store') }}" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="month_year" x-model="form.month_year">

                    <div>
                        <label for="budget-category" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Kategori Pengeluaran</label>
                        <select id="budget-category" name="category_id" x-model="form.category_id" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2.5 text-body-sm text-text-primary dark:text-dark-text-primary">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($expenseCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="budget-amount" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Batas Anggaran (Rp)</label>
                        <input id="budget-amount" type="number" name="amount" step="any" min="1" x-model="form.amount" required placeholder="1500000" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-title-md font-bold tabular-nums text-text-primary dark:text-dark-text-primary">
                    </div>

                    <button type="submit" class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md cursor-pointer">
                        Simpan Budget
                    </button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.mobile>
