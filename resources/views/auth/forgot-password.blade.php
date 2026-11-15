<x-guest-layout title="Reset Password">
    <!-- Atmospheric Background Glow Elements -->
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-72 h-72 bg-accent-lime/15 dark:bg-dark-forest/30 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-10 right-0 w-64 h-64 bg-forest-primary/10 dark:bg-forest-deep/40 rounded-full blur-[90px] pointer-events-none"></div>

    <!-- Header Navigation Bar -->
    <header class="relative z-10 flex items-center justify-between pt-space-xs pb-space-md">
        <!-- Circular Back Button -->
        <a
            href="{{ route('login') }}"
            aria-label="Kembali ke halaman login"
            class="w-10 h-10 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-primary dark:text-dark-text-primary hover:text-forest-primary dark:hover:text-dark-lime hover:border-forest-primary/40 dark:hover:border-dark-forest active:scale-95 transition-all duration-150 shadow-sm"
        >
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </a>

        <div class="flex items-center gap-2">
            <!-- Security Trust Badge -->
            <div class="bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-forest-primary dark:text-dark-lime text-label-md px-3 py-1 rounded-full flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[15px]" style="font-variation-settings: 'FILL' 1;">lock</span>
                <span>End-to-End SSL</span>
            </div>

            <!-- Theme Toggle Button -->
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
                aria-label="Toggle theme mode"
                class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime hover:bg-surface-tint dark:hover:bg-dark-surface active:scale-95 transition-all shadow-sm"
            >
                <span class="material-symbols-outlined text-[18px]" x-show="darkMode">dark_mode</span>
                <span class="material-symbols-outlined text-[18px]" x-show="!darkMode" x-cloak>light_mode</span>
            </button>
        </div>
    </header>

    <!-- Main Content Area / Bento Core -->
    <main class="relative z-10 flex-1 flex flex-col justify-center py-space-sm">
        <div class="rounded-3xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border p-space-lg shadow-xl shadow-black/5 dark:shadow-black/40 backdrop-blur-md w-full relative overflow-hidden">
            <!-- Subtle Ambient Pattern (Light Mode) -->
            <div class="absolute -right-8 -top-8 w-28 h-28 rounded-full bg-surface-tint/60 dark:bg-transparent pointer-events-none"></div>

            <!-- Hero Visual Icon Badge -->
            <div class="w-16 h-16 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-[#294336] flex items-center justify-center mx-auto mb-4 text-forest-primary dark:text-dark-lime shadow-sm relative">
                <div class="hidden dark:block absolute inset-0 rounded-full bg-dark-lime/10 animate-pulse pointer-events-none"></div>
                <span class="material-symbols-outlined text-[30px]">lock_reset</span>
            </div>

            <!-- Headline & Instructions -->
            <h1 class="text-headline-lg-mobile text-text-primary dark:text-dark-text-primary text-center font-bold tracking-tight mb-2">
                Reset Password
            </h1>
            <p class="text-body-sm text-text-secondary dark:text-dark-text-secondary text-center mb-6 leading-relaxed">
                Masukkan email terdaftar untuk menerima tautan atur ulang kata sandi akun Tracking Finance Anda.
            </p>

            <!-- Session Status (e.g. Password reset link sent) -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Reset Request Form -->
            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="text-label-xs font-semibold tracking-wider text-text-secondary dark:text-dark-text-secondary uppercase mb-1.5 block" for="email">
                        EMAIL TERDAFTAR
                    </label>
                    <div class="relative rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-[#294336] focus-within:border-forest-primary dark:focus-within:border-dark-lime transition-colors duration-150">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted dark:text-dark-text-muted">
                            <span class="material-symbols-outlined text-[18px]">mail</span>
                        </div>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="nama@email.com"
                            class="w-full bg-transparent border-0 text-text-primary dark:text-dark-text-primary text-body-md placeholder-text-muted dark:placeholder-dark-text-muted pl-10 pr-4 py-3 rounded-xl focus:ring-0 focus:outline-none"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                </div>

                <!-- Informational Alert Callout -->
                <div class="rounded-xl bg-surface-tint dark:bg-dark-surface-tint/80 border border-border-subtle dark:border-[#294336] p-3.5 flex items-start gap-3 text-body-sm text-semantic-info-text dark:text-dark-text-secondary leading-relaxed">
                    <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime shrink-0 text-[18px] mt-0.5">info</span>
                    <span>Tautan reset akan berlaku selama 60 menit demi keamanan akun Anda.</span>
                </div>

                <!-- Primary Submit Action Button -->
                <button
                    type="submit"
                    class="w-full h-12 rounded-xl bg-forest-primary hover:bg-forest-deep dark:bg-dark-lime dark:hover:bg-accent-lime active:scale-[0.98] text-white dark:text-dark-canvas text-title-md font-bold flex items-center justify-center gap-2 shadow-lg shadow-forest-primary/15 dark:shadow-dark-lime/15 transition-all duration-150 mt-1 cursor-pointer"
                >
                    <span>Kirim Tautan Reset</span>
                    <span class="material-symbols-outlined text-[18px]">send</span>
                </button>
            </form>

            <!-- Secondary Action Link -->
            <a href="{{ route('login') }}" class="text-body-sm text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-text-primary font-medium text-center mt-5 block transition-colors duration-150">
                Kembali ke Login
            </a>
        </div>
    </main>

    <!-- Bottom Assistance Footnote -->
    <footer class="relative z-10 py-space-sm text-center">
        <div class="inline-flex items-center gap-1.5 text-text-muted dark:text-dark-text-muted text-label-xs">
            <span class="material-symbols-outlined text-[14px]">shield</span>
            <span>Pusat Bantuan &amp; Keamanan Akun</span>
        </div>
    </footer>
</x-guest-layout>
