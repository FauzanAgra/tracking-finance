<x-layouts.mobile title="Profil & Pengaturan" activeNav="profile">
    @php
        $user = auth()->user();
        $initials = collect(explode(' ', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="flex-1 flex flex-col">
        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm flex items-center justify-between border-b border-border-subtle/50 dark:border-dark-border/40">
            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('dashboard') }}"
                    aria-label="Kembali ke Beranda"
                    class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-primary dark:text-dark-text-primary active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight">Profil &amp; Pengaturan</h1>
            </div>

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
                class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime active:scale-95 transition-all cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]" x-show="darkMode">dark_mode</span>
                <span class="material-symbols-outlined text-[18px]" x-show="!darkMode" x-cloak>light_mode</span>
            </button>
        </header>

        <div class="px-gutter py-space-md space-y-4">
            <!-- User Hero Card -->
            <section class="bg-forest-primary dark:bg-dark-surface rounded-3xl p-5 text-white dark:text-dark-text-primary border border-forest-primary dark:border-dark-border shadow-md flex items-center gap-3.5">
                <div class="w-14 h-14 rounded-full bg-white/15 dark:bg-dark-forest text-accent-lime dark:text-dark-lime flex items-center justify-center font-bold text-lg shrink-0 border border-white/20 dark:border-dark-border">
                    {{ $initials ?: 'U' }}
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-title-md font-bold truncate">{{ $user->name }}</h2>
                    <p class="text-body-sm text-white/75 dark:text-dark-text-secondary truncate">{{ $user->email }}</p>
                    <span class="inline-flex items-center gap-1 mt-1.5 px-2.5 py-0.5 rounded-full bg-white/15 dark:bg-dark-surface-tint text-accent-lime dark:text-dark-lime text-[11px] font-semibold">
                        Mata Uang Utama: {{ $user->base_currency ?? 'IDR' }}
                    </span>
                </div>
            </section>

            <!-- Quick Module Navigation -->
            <section class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-3.5 shadow-xs space-y-2">
                <span class="text-label-xs font-bold uppercase tracking-wider text-text-muted dark:text-dark-text-secondary block px-1">
                    Akses Modul Lainnya
                </span>
                <div class="grid grid-cols-3 gap-2">
                    <a href="{{ route('categories.index') }}" class="px-2.5 py-2 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-center text-label-md font-semibold text-text-primary dark:text-dark-text-primary">
                        Kategori
                    </a>
                    <a href="{{ route('budgets.index') }}" class="px-2.5 py-2 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-center text-label-md font-semibold text-text-primary dark:text-dark-text-primary">
                        Budget
                    </a>
                    <a href="{{ route('debts.index') }}" class="px-2.5 py-2 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-center text-label-md font-semibold text-text-primary dark:text-dark-text-primary">
                        Utang
                    </a>
                    <a href="{{ route('goals.index') }}" class="px-2.5 py-2 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-center text-label-md font-semibold text-text-primary dark:text-dark-text-primary">
                        Goals
                    </a>
                    <a href="{{ route('investments.index') }}" class="px-2.5 py-2 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-center text-label-md font-semibold text-text-primary dark:text-dark-text-primary">
                        Portofolio
                    </a>
                    <a href="{{ route('receipts.index') }}" class="px-2.5 py-2 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-center text-label-md font-semibold text-text-primary dark:text-dark-text-primary">
                        Scan AI
                    </a>
                </div>
            </section>

            <!-- Profile Information Form -->
            <section class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-3xl p-4 shadow-xs">
                @include('profile.partials.update-profile-information-form')
            </section>

            <!-- Update Password Form -->
            <section class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-3xl p-4 shadow-xs">
                @include('profile.partials.update-password-form')
            </section>

            <!-- Delete Account Form -->
            <section class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-3xl p-4 shadow-xs">
                @include('profile.partials.delete-user-form')
            </section>

            <!-- Logout Action -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full py-3 rounded-full bg-rose-50 dark:bg-[#3B1822] border border-rose-200 dark:border-[#5A2534] text-rose-700 dark:text-[#FDA4AF] text-label-lg font-bold flex items-center justify-center gap-2 active:scale-[0.98] transition-all cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span>Keluar dari Akun</span>
                </button>
            </form>
        </div>
    </div>
</x-layouts.mobile>
