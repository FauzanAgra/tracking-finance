<!-- 1. Modal Create / Edit Goal (GOAL-001) -->
<div
    x-show="goalModalOpen"
    x-cloak
    @keydown.escape.window="goalModalOpen = false"
    class="fixed inset-0 z-50 flex items-end justify-center"
    role="dialog"
    aria-modal="true"
>
    <div @click="goalModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
    <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6 max-h-[90vh] overflow-y-auto no-scrollbar">
        <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary" x-text="goalModalMode === 'edit' ? 'Edit Goal' : 'Buat Goal Baru'"></h2>
            <button type="button" @click="goalModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
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

        <form
            method="POST"
            :action="goalModalMode === 'edit' ? '/goals/' + editGoalId : '{{ route('goals.store') }}'"
            class="space-y-3.5"
        >
            @csrf
            <template x-if="goalModalMode === 'edit'">
                <div>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_goal_id" :value="editGoalId">
                </div>
            </template>

            <div>
                <label for="goal-title" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Nama Target</label>
                <input id="goal-title" type="text" name="title" x-model="goalForm.title" required maxlength="150" placeholder="Contoh: Dana Darurat, Laptop Baru" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-body-md text-text-primary dark:text-dark-text-primary">
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label for="goal-currency" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Mata Uang</label>
                    <select id="goal-currency" name="currency" x-model="goalForm.currency" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2.5 text-body-sm text-text-primary dark:text-dark-text-primary">
                        <option value="IDR">IDR (Rupiah)</option>
                        <option value="USD">USD</option>
                        <option value="USDT">USDT</option>
                    </select>
                </div>

                <div>
                    <label for="goal-date" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Tenggat (Opsional)</label>
                    <input id="goal-date" type="date" name="target_date" x-model="goalForm.target_date" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                </div>
            </div>

            <div>
                <label for="goal-target" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Nominal Target</label>
                <x-money-input id="goal-target" name="target_amount" model="goalForm.target_amount" :required="true" placeholder="18.000.000" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-title-md font-bold text-text-primary dark:text-dark-text-primary" />
            </div>

            <div>
                <label class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1.5">Pilih Icon</label>
                <input type="hidden" name="icon" x-model="goalForm.icon">
                <div class="grid grid-cols-8 gap-1.5">
                    @foreach ($iconOptions as $iconName)
                        <button
                            type="button"
                            @click="goalForm.icon = '{{ $iconName }}'"
                            :class="goalForm.icon === '{{ $iconName }}' ? 'bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas border-forest-primary dark:border-dark-lime' : 'bg-surface-tint dark:bg-dark-surface-tint text-text-secondary border-border-subtle dark:border-dark-border'"
                            class="h-9 rounded-xl border flex items-center justify-center cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-[18px]">{{ $iconName }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md cursor-pointer" x-text="goalModalMode === 'edit' ? 'Simpan Perubahan' : 'Simpan Goal'"></button>
        </form>
    </section>
</div>

<!-- 2. Modal Setoran Dana ke Goal (GOAL-002) -->
<div
    x-show="contributeModalOpen"
    x-cloak
    @keydown.escape.window="contributeModalOpen = false"
    class="fixed inset-0 z-50 flex items-end justify-center"
    role="dialog"
    aria-modal="true"
>
    <div @click="contributeModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
    <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6">
        <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Sisihkan Dana</h2>
                <p class="text-label-xs text-text-muted dark:text-dark-text-muted" x-text="activeGoal ? activeGoal.title + ' • Kurang ' + activeGoal.currency + ' ' + Number(activeGoal.remaining).toLocaleString('id-ID') : ''"></p>
            </div>
            <button type="button" @click="contributeModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form method="POST" :action="activeGoal ? '/goals/' + activeGoal.id + '/contributions' : '#'" class="space-y-3.5">
            @csrf
            <div>
                <label for="contrib-amount" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Nominal Setoran</label>
                <x-money-input id="contrib-amount" name="amount" model="contributeForm.amount" :required="true" placeholder="500.000" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-title-md font-bold text-text-primary dark:text-dark-text-primary" />
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label for="contrib-wallet" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Sumber Dompet</label>
                    <select id="contrib-wallet" name="wallet_id" x-model="contributeForm.wallet_id" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                        @foreach ($wallets as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->currency }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="contrib-date" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Tanggal</label>
                    <input id="contrib-date" type="date" name="contribution_date" x-model="contributeForm.contribution_date" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                </div>
            </div>

            <div>
                <label for="contrib-notes" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Catatan (Opsional)</label>
                <input id="contrib-notes" type="text" name="notes" x-model="contributeForm.notes" placeholder="Contoh: Sisihan gaji bulan ini" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
            </div>

            <button type="submit" class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md cursor-pointer">
                Setor ke Brankas Goal
            </button>
        </form>
    </section>
</div>
