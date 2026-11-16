<x-layouts.mobile title="Profil & Pengaturan" activeNav="profile">
    @php
        $initials = collect(explode(' ', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
        $hasProfileErrors = $errors->hasAny(['name', 'email', 'base_currency']);
        $hasPasswordErrors = $errors->updatePassword->any();
        $hasDeleteErrors = $errors->userDeletion->any();
    @endphp

    <div
        x-data="{
            editModalOpen: @js($hasProfileErrors),
            passwordModalOpen: @js($hasPasswordErrors),
            deleteModalOpen: @js($hasDeleteErrors),
            darkMode: document.documentElement.classList.contains('dark'),
            toggleTheme() {
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
        class="flex-1 flex flex-col"
    >
        <!-- Sticky Top Header (Stitch 20_profile_settings) -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm flex items-center justify-between border-b border-border-subtle/50 dark:border-dark-border/40">
            <a
                href="{{ route('dashboard') }}"
                aria-label="Kembali"
                class="w-10 h-10 rounded-full bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-primary dark:text-dark-text-primary active:scale-95 transition-transform"
            >
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>

            <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary text-center">
                Profil &amp; Pengaturan
            </h1>

            <a
                href="{{ route('transactions.index') }}"
                aria-label="Riwayat Transaksi"
                class="w-10 h-10 rounded-full bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-primary dark:text-dark-text-primary active:scale-95 transition-transform"
            >
                <span class="material-symbols-outlined text-[20px]">receipt_long</span>
            </a>
        </header>

        <div class="px-gutter py-space-md space-y-4">
            @if (session('status') === 'profile-updated')
                <div class="p-3 rounded-2xl bg-semantic-profit-bg/60 dark:bg-dark-surface-tint border border-forest-primary/20 dark:border-dark-lime/30 text-forest-primary dark:text-dark-lime text-body-sm font-medium">
                    Profil berhasil diperbarui.
                </div>
            @elseif (session('status') === 'password-updated')
                <div class="p-3 rounded-2xl bg-semantic-profit-bg/60 dark:bg-dark-surface-tint border border-forest-primary/20 dark:border-dark-lime/30 text-forest-primary dark:text-dark-lime text-body-sm font-medium">
                    Password berhasil diperbarui.
                </div>
            @endif

            <!-- 1. User Profile Bento Card -->
            <section class="rounded-3xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border p-5 shadow-xs relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-28 h-28 rounded-full bg-accent-lime/20 dark:bg-dark-lime/10 pointer-events-none blur-xl"></div>

                <div class="flex items-center justify-between gap-3 relative z-10">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="relative shrink-0">
                            <div class="w-16 h-16 rounded-full bg-accent-lime-soft dark:bg-dark-surface-tint text-forest-primary dark:text-dark-lime font-bold text-headline-sm flex items-center justify-center border-2 border-forest-primary/30 dark:border-[#2F6F52] shadow-xs">
                                {{ $initials ?: 'U' }}
                            </div>
                            <div class="absolute bottom-0 right-0 w-4 h-4 rounded-full bg-forest-primary dark:bg-dark-lime border-2 border-surface-card dark:border-dark-surface flex items-center justify-center">
                                <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-dark-canvas"></span>
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary truncate">{{ $user->name }}</span>
                                <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-[16px] fill-1 shrink-0">verified</span>
                            </div>
                            <span class="text-body-sm text-text-muted dark:text-dark-text-secondary truncate block">{{ $user->email }}</span>
                            <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100/80 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 text-label-xs font-semibold border border-amber-300/50 dark:border-amber-800/30">
                                <span class="material-symbols-outlined text-[13px] fill-1">star</span>
                                <span>Basis {{ $user->base_currency ?? 'IDR' }}</span>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="editModalOpen = true"
                        class="rounded-xl bg-surface-tint dark:bg-dark-surface-tint text-text-primary dark:text-dark-text-primary text-label-md font-semibold px-3 py-2 border border-border-subtle dark:border-dark-border hover:border-forest-primary dark:hover:border-dark-lime active:scale-95 transition-all self-start shrink-0 cursor-pointer"
                    >
                        Edit Profil
                    </button>
                </div>

                <!-- Quick Stats Mini-Row -->
                <div class="mt-5 pt-4 border-t border-border-subtle dark:border-dark-border grid grid-cols-3 gap-2 text-center">
                    <a href="{{ route('wallets.index') }}" class="p-2.5 rounded-xl bg-surface-tint dark:bg-[#101A15] border border-border-subtle dark:border-dark-surface-tint flex flex-col items-center hover:border-forest-primary dark:hover:border-dark-lime transition-colors">
                        <span class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tabular-nums">{{ $activeWalletsCount }}</span>
                        <span class="text-label-xs text-text-muted dark:text-dark-text-secondary mt-0.5">Dompet Aktif</span>
                    </a>
                    <a href="{{ route('goals.index') }}" class="p-2.5 rounded-xl bg-surface-tint dark:bg-[#101A15] border border-border-subtle dark:border-dark-surface-tint flex flex-col items-center hover:border-forest-primary dark:hover:border-dark-lime transition-colors">
                        <span class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tabular-nums">{{ $inProgressGoalsCount }}</span>
                        <span class="text-label-xs text-text-muted dark:text-dark-text-secondary mt-0.5">Goals Berjalan</span>
                    </a>
                    <a href="{{ route('investments.index') }}" class="p-2.5 rounded-xl bg-surface-tint dark:bg-[#101A15] border border-border-subtle dark:border-dark-surface-tint flex flex-col items-center hover:border-forest-primary dark:hover:border-dark-lime transition-colors">
                        <span class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tabular-nums">{{ $investmentAssetsCount }}</span>
                        <span class="text-label-xs text-text-muted dark:text-dark-text-secondary mt-0.5">Aset Investasi</span>
                    </a>
                </div>
            </section>

            <!-- 2. Settings Group 1: Tampilan & Mata Uang -->
            <section class="rounded-3xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border p-4 space-y-3 shadow-xs">
                <div class="flex items-center gap-2 px-1">
                    <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-[18px]">palette</span>
                    <h2 class="text-label-md font-bold tracking-wider text-text-muted dark:text-dark-text-secondary uppercase">
                        Tampilan &amp; Mata Uang
                    </h2>
                </div>

                <div class="space-y-2.5">
                    <!-- Row 1: Dual Mode Theme Pill Toggle -->
                    <div class="p-3 rounded-2xl bg-surface-tint dark:bg-[#101A15] border border-border-subtle dark:border-dark-border flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime">
                                <span class="material-symbols-outlined text-[20px]" x-text="darkMode ? 'dark_mode' : 'light_mode'"></span>
                            </div>
                            <div>
                                <p class="text-title-md font-semibold text-text-primary dark:text-dark-text-primary leading-tight">Mode Tampilan</p>
                                <p class="text-label-xs text-text-muted dark:text-dark-text-secondary mt-0.5" x-text="darkMode ? 'Obsidian Dark aktif' : 'COINEST Light aktif'"></p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="toggleTheme()"
                            aria-label="Ubah tema"
                            :class="darkMode ? 'bg-[#1F3A2B] border-[#2F6F52] justify-end' : 'bg-accent-lime-soft border-forest-primary/25 justify-start'"
                            class="w-14 h-8 rounded-full p-1 flex items-center transition-all cursor-pointer border"
                        >
                            <span class="w-6 h-6 rounded-full bg-forest-primary dark:bg-dark-lime flex items-center justify-center text-white dark:text-[#0A110E] shadow-xs">
                                <span class="material-symbols-outlined text-[14px]" x-text="darkMode ? 'dark_mode' : 'light_mode'"></span>
                            </span>
                        </button>
                    </div>

                    <!-- Row 2: Mata Uang Utama Net Worth -->
                    <button
                        type="button"
                        @click="editModalOpen = true"
                        class="w-full p-3 rounded-2xl bg-surface-tint dark:bg-[#101A15] border border-border-subtle dark:border-dark-border flex items-center justify-between text-left hover:border-forest-primary dark:hover:border-dark-lime transition-colors cursor-pointer"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime">
                                <span class="material-symbols-outlined text-[20px]">currency_exchange</span>
                            </div>
                            <div>
                                <p class="text-title-md font-semibold text-text-primary dark:text-dark-text-primary leading-tight">Mata Uang Utama</p>
                                <p class="text-label-xs text-text-muted dark:text-dark-text-secondary mt-0.5">Konversi laporan Net Worth</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2.5 py-1 rounded-lg bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-label-md font-bold text-forest-primary dark:text-dark-lime">
                                {{ $user->base_currency ?? 'IDR' }}
                            </span>
                            <span class="material-symbols-outlined text-[18px] text-text-muted">chevron_right</span>
                        </div>
                    </button>
                </div>
            </section>

            <!-- 3. Settings Group 2: Keamanan & Akun -->
            <section class="rounded-3xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border p-4 space-y-2.5 shadow-xs">
                <div class="flex items-center gap-2 px-1">
                    <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-[18px]">shield</span>
                    <h2 class="text-label-md font-bold tracking-wider text-text-muted dark:text-dark-text-secondary uppercase">
                        Keamanan &amp; Akun
                    </h2>
                </div>

                <button
                    type="button"
                    @click="passwordModalOpen = true"
                    class="w-full p-3 rounded-2xl bg-surface-tint dark:bg-[#101A15] border border-border-subtle dark:border-dark-border flex items-center justify-between text-left hover:border-forest-primary dark:hover:border-dark-lime transition-colors cursor-pointer"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime">
                            <span class="material-symbols-outlined text-[20px]">lock</span>
                        </div>
                        <div>
                            <p class="text-title-md font-semibold text-text-primary dark:text-dark-text-primary leading-tight">Ubah Password</p>
                            <p class="text-label-xs text-text-muted dark:text-dark-text-secondary mt-0.5">Perbarui kata sandi keamanan</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-[18px] text-text-muted">chevron_right</span>
                </button>

                <button
                    type="button"
                    @click="deleteModalOpen = true"
                    class="w-full p-3 rounded-2xl bg-surface-tint dark:bg-[#101A15] border border-border-subtle dark:border-dark-border flex items-center justify-between text-left hover:border-rose-400 transition-colors cursor-pointer"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-[#3B1822] border border-rose-200 dark:border-[#5A2534] flex items-center justify-center text-rose-600 dark:text-[#FDA4AF]">
                            <span class="material-symbols-outlined text-[20px]">delete_forever</span>
                        </div>
                        <div>
                            <p class="text-title-md font-semibold text-rose-700 dark:text-[#FDA4AF] leading-tight">Hapus Akun</p>
                            <p class="text-label-xs text-text-muted dark:text-dark-text-secondary mt-0.5">Hapus akun &amp; seluruh data permanen</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-[18px] text-text-muted">chevron_right</span>
                </button>
            </section>

            <!-- 4. Logout Button -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full py-3.5 rounded-full bg-rose-50 dark:bg-[#3B1822] border border-rose-200 dark:border-[#5A2534] text-rose-700 dark:text-[#FDA4AF] text-label-lg font-bold flex items-center justify-center gap-2 active:scale-[0.98] transition-all cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span>Keluar dari Akun</span>
                </button>
            </form>
        </div>

        <!-- Modal 1: Edit Profil & Mata Uang -->
        <div x-show="editModalOpen" x-cloak @keydown.escape.window="editModalOpen = false" class="fixed inset-0 z-50 flex items-end justify-center" role="dialog" aria-modal="true">
            <div @click="editModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
            <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6">
                <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Edit Profil</h2>
                    <button type="button" @click="editModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                @if ($hasProfileErrors)
                    <div class="mb-3 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-label-md space-y-1">
                        @foreach ($errors->only(['name', 'email', 'base_currency']) as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-3.5">
                    @csrf
                    @method('patch')
                    <div>
                        <label for="name" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Nama</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-body-md text-text-primary dark:text-dark-text-primary">
                    </div>
                    <div>
                        <label for="email" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-body-md text-text-primary dark:text-dark-text-primary">
                    </div>
                    <div>
                        <label for="base_currency" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Mata Uang Utama</label>
                        <select id="base_currency" name="base_currency" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2.5 text-body-sm text-text-primary dark:text-dark-text-primary">
                            @foreach (['IDR' => 'IDR - Rupiah Indonesia', 'USD' => 'USD - US Dollar', 'USDT' => 'USDT - Tether'] as $code => $label)
                                <option value="{{ $code }}" @selected(old('base_currency', $user->base_currency ?? 'IDR') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md cursor-pointer">
                        Simpan Perubahan
                    </button>
                </form>
            </section>
        </div>

        <!-- Modal 2: Ubah Password -->
        <div x-show="passwordModalOpen" x-cloak @keydown.escape.window="passwordModalOpen = false" class="fixed inset-0 z-50 flex items-end justify-center" role="dialog" aria-modal="true">
            <div @click="passwordModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
            <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6">
                <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Ubah Password</h2>
                    <button type="button" @click="passwordModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                @if ($hasPasswordErrors)
                    <div class="mb-3 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-label-md space-y-1">
                        @foreach ($errors->updatePassword->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-3.5">
                    @csrf
                    @method('put')
                    <div>
                        <label for="update_password_current_password" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Password Saat Ini</label>
                        <input id="update_password_current_password" name="current_password" type="password" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-body-md text-text-primary dark:text-dark-text-primary">
                    </div>
                    <div>
                        <label for="update_password_password" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Password Baru</label>
                        <input id="update_password_password" name="password" type="password" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-body-md text-text-primary dark:text-dark-text-primary">
                    </div>
                    <div>
                        <label for="update_password_password_confirmation" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Konfirmasi Password Baru</label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-body-md text-text-primary dark:text-dark-text-primary">
                    </div>
                    <button type="submit" class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md cursor-pointer">
                        Perbarui Password
                    </button>
                </form>
            </section>
        </div>

        <!-- Modal 3: Hapus Akun -->
        <div x-show="deleteModalOpen" x-cloak @keydown.escape.window="deleteModalOpen = false" class="fixed inset-0 z-50 flex items-end justify-center" role="dialog" aria-modal="true">
            <div @click="deleteModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
            <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6">
                <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-headline-sm font-bold text-rose-700 dark:text-[#FDA4AF]">Hapus Akun Permanen</h2>
                    <button type="button" @click="deleteModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                <p class="text-body-sm text-text-muted dark:text-dark-text-secondary mb-3">
                    Masukkan password kamu untuk mengonfirmasi penghapusan seluruh data akun secara permanen.
                </p>

                @if ($hasDeleteErrors)
                    <div class="mb-3 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-label-md space-y-1">
                        @foreach ($errors->userDeletion->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-3.5">
                    @csrf
                    @method('delete')
                    <div>
                        <label for="delete-password" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Password</label>
                        <input id="delete-password" name="password" type="password" required placeholder="Masukkan password akun" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-body-md text-text-primary dark:text-dark-text-primary">
                    </div>
                    <button type="submit" class="w-full py-3 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-label-lg font-bold shadow-md cursor-pointer">
                        Hapus Akun Permanen
                    </button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.mobile>
