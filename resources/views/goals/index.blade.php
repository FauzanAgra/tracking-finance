<x-layouts.mobile title="Financial Goals" activeNav="wallets">
    @php
        $today = now()->toDateString();
        $defaultWalletId = $defaultWallet?->id ? (string) $defaultWallet->id : '';
        $iconOptions = ['flag', 'laptop_mac', 'flight', 'directions_car', 'home', 'savings', 'school', 'diamond'];
    @endphp

    <div
        x-data="{
            goalModalOpen: @js($errors->hasAny(['title', 'target_amount', 'target_date', 'currency'])),
            goalModalMode: @js(old('_method') === 'PUT' ? 'edit' : 'create'),
            editGoalId: @js(old('_goal_id')),
            contributeModalOpen: false,
            activeGoal: null,
            goalForm: {
                title: @js(old('title', '')),
                currency: @js(old('currency', 'IDR')),
                target_amount: @js(old('target_amount', '')),
                target_date: @js(old('target_date', '')),
                icon: @js(old('icon', 'flag')),
            },
            contributeForm: {
                wallet_id: @js($defaultWalletId),
                amount: '',
                contribution_date: @js($today),
                notes: '',
            },
            openCreateGoal() {
                this.goalModalMode = 'create';
                this.editGoalId = null;
                this.goalForm = {
                    title: '',
                    currency: 'IDR',
                    target_amount: '',
                    target_date: '',
                    icon: 'flag',
                };
                this.goalModalOpen = true;
            },
            openEditGoal(goal) {
                this.goalModalMode = 'edit';
                this.editGoalId = goal.id;
                this.goalForm = {
                    title: goal.title,
                    currency: goal.currency,
                    target_amount: String(goal.target_amount),
                    target_date: goal.target_date ?? '',
                    icon: goal.icon || 'flag',
                };
                this.goalModalOpen = true;
            },
            openContributeModal(goal) {
                this.activeGoal = goal;
                this.contributeForm = {
                    wallet_id: @js($defaultWalletId),
                    amount: '',
                    contribution_date: @js($today),
                    notes: '',
                };
                this.contributeModalOpen = true;
            }
        }"
        class="flex-1 flex flex-col"
    >
        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm flex items-center justify-between border-b border-border-subtle/50 dark:border-dark-border/40">
            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('wallets.index') }}"
                    aria-label="Kembali ke Dompet"
                    class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-primary dark:text-dark-text-primary active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight">Financial Goals</h1>
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
                    @click="openCreateGoal()"
                    class="flex items-center gap-1 bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas px-3 py-2 rounded-full text-label-md font-semibold shadow-sm active:scale-95 transition-all cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Buat Goal</span>
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

            <!-- Summary Bento Card (Brankas Goals) -->
            <section class="bg-surface-card dark:bg-dark-surface rounded-3xl p-5 border border-border-subtle dark:border-dark-border shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-accent-lime-soft dark:bg-dark-surface-tint flex items-center justify-center text-forest-primary dark:text-dark-lime">
                            <span class="material-symbols-outlined text-[18px] fill-1">savings</span>
                        </div>
                        <span class="text-label-lg font-semibold text-text-primary dark:text-dark-text-primary">Brankas Goals</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-accent-lime-soft dark:bg-dark-surface-tint text-forest-primary dark:text-dark-lime text-label-xs font-semibold">
                        {{ $inProgressCount }} Target Aktif
                    </span>
                </div>

                <div>
                    <p class="text-label-xs uppercase tracking-wider font-semibold text-text-muted dark:text-dark-text-secondary">
                        Total Terkumpul (IDR)
                    </p>
                    <p class="text-headline-lg-mobile font-bold text-text-primary dark:text-dark-text-primary tabular-nums mt-1">
                        Rp {{ number_format($totalSavedIdr, 0, ',', '.') }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-3 border-t border-border-subtle dark:border-dark-border">
                    <div class="bg-surface-tint dark:bg-dark-surface-tint px-3 py-2 rounded-xl border border-border-subtle dark:border-dark-border">
                        <div class="flex items-center gap-1.5 text-label-xs text-text-muted dark:text-dark-text-secondary">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-dark-lime"></span>
                            <span>Tercapai ({{ $achievedCount }})</span>
                        </div>
                        <div class="text-label-md font-bold text-emerald-600 dark:text-dark-lime tabular-nums mt-0.5">
                            Rp {{ number_format($achievedSavedIdr, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="bg-surface-tint dark:bg-dark-surface-tint px-3 py-2 rounded-xl border border-border-subtle dark:border-dark-border">
                        <div class="flex items-center gap-1.5 text-label-xs text-text-muted dark:text-dark-text-secondary">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span>Berjalan ({{ $inProgressCount }})</span>
                        </div>
                        <div class="text-label-md font-bold text-text-primary dark:text-dark-text-primary tabular-nums mt-0.5">
                            Rp {{ number_format($inProgressSavedIdr, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Goal Cards -->
            @if ($goalCards->isEmpty())
                <div class="bg-surface-card dark:bg-dark-surface border border-dashed border-border-subtle dark:border-dark-border rounded-3xl p-8 text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-accent-lime-soft dark:bg-dark-surface-tint flex items-center justify-center text-forest-primary dark:text-dark-lime">
                        <span class="material-symbols-outlined text-[28px]">flag</span>
                    </div>
                    <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Belum Ada Target Tabungan</h3>
                    <p class="text-body-sm text-text-muted dark:text-dark-text-muted">
                        Buat target impian (seperti Dana Darurat atau Laptop Baru) dan sisihkan dana secara bertahap dari dompetmu.
                    </p>
                    <button
                        type="button"
                        @click="openCreateGoal()"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-semibold active:scale-95 transition-transform cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Buat Goal Pertama</span>
                    </button>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($goalCards as $card)
                        @php
                            $goal = $card['goal'];
                            $insights = $card['insights'];
                            $pct = $insights['percentage'];
                            $remaining = $insights['remaining'];
                            $isAchieved = $goal->status === 'achieved';
                            $goalPayload = [
                                'id' => $goal->id,
                                'title' => $goal->title,
                                'currency' => $goal->currency,
                                'target_amount' => (float) $goal->target_amount,
                                'target_date' => $goal->target_date?->format('Y-m-d'),
                                'icon' => $goal->icon,
                                'remaining' => $remaining,
                            ];
                            $statusLabel = match ($goal->status) {
                                'achieved' => 'Tercapai',
                                'cancelled' => 'Dibatalkan',
                                default => 'In Progress',
                            };
                            $statusBadge = match ($goal->status) {
                                'achieved' => 'bg-emerald-100 text-emerald-700 dark:bg-[#1C3B2A] dark:text-[#86EFAC]',
                                'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-dark-surface-tint dark:text-dark-text-muted',
                                default => 'bg-amber-100 text-amber-700 dark:bg-[#3A2B12] dark:text-[#FCD34D]',
                            };
                        @endphp

                        <section class="bg-surface-card dark:bg-dark-surface rounded-3xl p-4 border border-border-subtle dark:border-dark-border shadow-xs space-y-3.5">
                            <!-- Header Row -->
                            <div class="flex items-start justify-between gap-2.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-2xl bg-accent-lime-soft dark:bg-dark-surface-tint text-forest-primary dark:text-dark-lime flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[22px]">{{ $goal->icon }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <h2 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary truncate">{{ $goal->title }}</h2>
                                        @if ($goal->target_date)
                                            <div class="inline-flex items-center gap-1 mt-0.5 text-label-xs text-text-muted dark:text-dark-text-secondary">
                                                <span class="material-symbols-outlined text-[13px]">calendar_today</span>
                                                <span>Target: {{ $goal->target_date->translatedFormat('d M Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $statusBadge }}">
                                        {{ $statusLabel }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="openEditGoal(@js($goalPayload))"
                                        aria-label="Edit {{ $goal->title }}"
                                        class="w-7 h-7 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-[14px]">edit</span>
                                    </button>
                                    @if ($goal->contributions->isEmpty())
                                        <form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Hapus goal {{ $goal->title }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="Hapus goal" class="w-7 h-7 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-muted hover:text-rose-600 cursor-pointer">
                                                <span class="material-symbols-outlined text-[14px]">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <!-- Financial Metrics & Progress Bar -->
                            <div class="bg-surface-tint dark:bg-dark-canvas rounded-2xl p-3.5 border border-border-subtle dark:border-dark-border space-y-2.5">
                                <div class="flex items-baseline justify-between gap-2 flex-wrap">
                                    <div>
                                        <span class="text-label-xs uppercase tracking-wider text-text-muted dark:text-dark-text-secondary block">Terkumpul</span>
                                        <div class="flex items-baseline gap-1.5">
                                            <span class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tabular-nums">
                                                {{ $goal->currency }} {{ number_format((float) $goal->current_amount, 0, ',', '.') }}
                                            </span>
                                            <span class="text-body-sm text-text-muted dark:text-dark-text-muted tabular-nums">
                                                / {{ number_format((float) $goal->target_amount, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                    @if (! $isAchieved)
                                        <span class="px-2.5 py-0.5 rounded-full text-label-xs font-semibold bg-rose-100 text-rose-700 dark:bg-[#3B1822] dark:text-[#FDA4AF] tabular-nums">
                                            Kurang {{ $goal->currency }} {{ number_format($remaining, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-label-xs text-text-muted dark:text-dark-text-secondary">Progres Akumulasi</span>
                                        <span class="text-label-md font-bold text-forest-primary dark:text-dark-lime tabular-nums">{{ $pct }}%</span>
                                    </div>
                                    <div class="h-3 rounded-full bg-surface-card dark:bg-dark-surface-tint overflow-hidden border border-border-subtle dark:border-dark-border">
                                        <div class="h-full bg-forest-primary dark:bg-dark-lime rounded-full transition-all duration-500" style="width: {{ $pct }}%;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Smart Insights Pill (GOAL-003) -->
                            <div class="bg-accent-lime-soft/70 dark:bg-[#1F3A2B] text-forest-primary dark:text-[#E3F8CE] rounded-2xl p-3 text-body-sm font-medium flex items-center gap-2 border border-forest-primary/15 dark:border-[#2F6F52]">
                                <span class="material-symbols-outlined text-[18px] text-forest-primary dark:text-dark-lime shrink-0">auto_awesome</span>
                                <span class="leading-tight">{{ $insights['estimate_text'] }}</span>
                            </div>

                            <!-- Primary Action Button (GOAL-002) -->
                            @if ($goal->status === 'in_progress')
                                <button
                                    type="button"
                                    @click="openContributeModal(@js($goalPayload))"
                                    class="w-full py-3 rounded-2xl bg-forest-primary dark:bg-[#1E4D38] border border-forest-primary dark:border-[#2F6F52] text-white dark:text-dark-lime text-label-lg font-semibold flex items-center justify-center gap-1.5 active:scale-[0.98] transition-all cursor-pointer"
                                >
                                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                                    <span>Sisihkan Dana dari Dompet</span>
                                </button>
                            @endif
                        </section>
                    @endforeach
                </div>
            @endif
        </div>

        @include('goals.partials.modals')
    </div>
</x-layouts.mobile>
