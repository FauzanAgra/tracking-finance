<x-guest-layout title="Masuk">
    <!-- Ambient Background Forest Glow -->
    <div
        class="absolute -top-24 -left-20 w-72 h-72 bg-accent-lime/10 dark:bg-dark-forest/20 rounded-full blur-3xl pointer-events-none">
    </div>
    <div
        class="absolute top-1/3 -right-24 w-80 h-80 bg-forest-primary/5 dark:bg-forest-primary/15 rounded-full blur-3xl pointer-events-none">
    </div>
    <div class="absolute bottom-10 left-1/4 w-60 h-60 bg-dark-lime/5 rounded-full blur-3xl pointer-events-none"></div>

    <!-- 1. Top Section -->
    <header class="relative z-10 w-full pt-space-xs">
        <!-- Status Badge & Mode Toggle Button -->
        <div class="flex items-center justify-between w-full mb-space-md">
            <!-- Status indicator -->
            <div
                class="flex items-center gap-2 px-space-sm py-1 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-forest-primary dark:bg-dark-lime opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-forest-primary dark:bg-dark-lime"></span>
                </span>
                <span
                    class="text-label-xs font-semibold tracking-wider text-text-secondary dark:text-dark-text-secondary">SECURE
                    SYSTEM v2.4</span>
            </div>

            <!-- Moon/Sun Mode Toggle Button (Sesuai Stitch: Icon Bulan saat Dark, Icon Matahari saat Light) -->
            <button type="button" x-data="{
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
            }" @click="toggle()" aria-label="Toggle theme mode"
                class="w-9 h-9 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime hover:bg-surface-card dark:hover:bg-dark-surface active:scale-95 transition-all">
                <span class="material-symbols-outlined text-[18px]" x-show="darkMode">dark_mode</span>
                <span class="material-symbols-outlined text-[18px]" x-show="!darkMode" x-cloak>light_mode</span>
            </button>
        </div>

        <!-- Centered Brand Lockup -->
        <div class="flex flex-col items-center text-center mt-space-xs mb-space-md">
            <!-- Geometric Glowing Icon Badge -->
            <div
                class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-surface-tint to-surface-card dark:from-dark-forest dark:to-dark-surface border border-border-subtle dark:border-dark-border flex items-center justify-center shadow-lg shadow-forest-primary/5 dark:shadow-dark-lime/10 mb-space-sm group">
                <div class="absolute inset-0 rounded-2xl bg-accent-lime/20 dark:bg-dark-lime/10 blur-sm"></div>
                <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-2xl relative z-10"
                    style="font-variation-settings: 'FILL' 1;">query_stats</span>
            </div>
            <h1 class="text-headline-lg-mobile font-bold tracking-tight text-text-primary dark:text-dark-text-primary">
                Tracking Finance</h1>
            <p class="text-body-sm text-text-secondary dark:text-dark-text-secondary mt-1">Personal Wealth, Cashflow
                &amp; Investment Hub</p>
        </div>
    </header>

    <!-- 2. Main Bento Form Card -->
    <section
        class="relative z-10 w-full bg-surface-card dark:bg-dark-surface rounded-3xl p-space-lg border border-border-subtle dark:border-dark-border shadow-xl shadow-black/5 dark:shadow-black/40">
        <!-- Card Header -->
        <div class="mb-space-md">
            <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Masuk ke Akunmu</h2>
            <p class="text-label-xs text-text-secondary dark:text-dark-text-secondary mt-1">Kelola dompet, piutang, dan
                portofolio dalam satu tempat.</p>
        </div>

        <!-- Session Status (e.g. Password reset link sent) -->
        <x-auth-session-status class="mb-space-md" :status="session('status')" />

        <!-- Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-space-md">
            @csrf

            <!-- Input 1: Email -->
            <div>
                <label
                    class="block text-label-md font-semibold tracking-wider text-text-secondary dark:text-dark-text-secondary uppercase mb-1.5"
                    for="email">
                    EMAIL
                </label>
                <div
                    class="relative rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border focus-within:border-forest-primary dark:focus-within:border-dark-lime flex items-center px-3.5 py-3 gap-3 transition-colors">
                    <span
                        class="material-symbols-outlined text-text-muted dark:text-dark-text-muted shrink-0">mail</span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        autocomplete="username" placeholder="nama@email.com"
                        class="w-full bg-transparent p-0 text-text-primary dark:text-dark-text-primary placeholder-text-muted dark:placeholder-dark-text-muted text-body-md focus:ring-0 focus:outline-none border-none leading-none" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <!-- Input 2: Password with Alpine.js Show/Hide -->
            <div x-data="{ show: false }">
                <label
                    class="block text-label-md font-semibold tracking-wider text-text-secondary dark:text-dark-text-secondary uppercase mb-1.5"
                    for="password">
                    PASSWORD
                </label>
                <div
                    class="relative rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border focus-within:border-forest-primary dark:focus-within:border-dark-lime flex items-center px-3.5 py-3 gap-3 transition-colors">
                    <span
                        class="material-symbols-outlined text-text-muted dark:text-dark-text-muted shrink-0">lock</span>
                    <input id="password" :type="show ? 'text' : 'password'" name="password" required
                        autocomplete="current-password" placeholder="••••••••••••" :class="{ 'tracking-widest': !show }"
                        class="w-full bg-transparent p-0 text-text-primary dark:text-dark-text-primary placeholder-text-muted dark:placeholder-dark-text-muted text-body-md focus:ring-0 focus:outline-none border-none leading-none" />
                    <button type="button" @click="show = !show" aria-label="Toggle password visibility"
                        class="text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime focus:outline-none flex items-center transition-colors">
                        <span class="material-symbols-outlined text-[20px]" x-show="!show">visibility</span>
                        <span class="material-symbols-outlined text-[20px]" x-show="show" x-cloak>visibility_off</span>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <!-- Utility Row: Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-label-xs pt-1">
                <label for="remember_me" class="flex items-center gap-2 cursor-pointer select-none">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="stitch-checkbox bg-surface-tint dark:bg-dark-surface-tint border-border-subtle dark:border-dark-border text-forest-primary dark:text-dark-lime focus:ring-0 focus:outline-none" />
                    <span class="text-text-secondary dark:text-dark-text-secondary">Ingat saya</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="text-forest-primary dark:text-dark-lime font-semibold hover:underline">
                        Lupa password?
                    </a>
                @endif
            </div>

            <!-- Primary CTA Button -->
            <button type="submit"
                class="w-full h-12 rounded-xl bg-forest-primary dark:bg-dark-lime hover:bg-forest-deep dark:hover:bg-accent-lime text-white dark:text-dark-canvas text-label-lg font-bold tracking-wide flex items-center justify-center gap-2 shadow-lg shadow-forest-primary/20 dark:shadow-dark-lime/20 active:scale-[0.98] transition-all cursor-pointer">
                <span>Masuk</span>
                <span class="material-symbols-outlined text-base">arrow_forward</span>
            </button>
        </form>

        <!-- Divider -->
        <div class="relative flex items-center justify-center my-space-md">
            <div class="border-t border-border-subtle dark:border-dark-border w-full"></div>
            <span
                class="bg-surface-card dark:bg-dark-surface px-3 text-label-xs uppercase text-text-muted dark:text-dark-text-muted tracking-widest shrink-0">
                atau
            </span>
            <div class="border-t border-border-subtle dark:border-dark-border w-full"></div>
        </div>

        <!-- Biometric Quick Login Button (Visual Stitch) -->
        <button type="button"
            onclick="alert('Fitur biometrik akan segera hadir untuk browser yang mendukung WebAuthn.')"
            class="w-full h-12 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border hover:border-text-muted dark:hover:border-dark-text-muted text-text-primary dark:text-dark-text-primary text-label-xs font-semibold flex items-center justify-center gap-2.5 transition-colors active:scale-[0.98] cursor-pointer">
            <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-xl"
                style="font-variation-settings: 'FILL' 1;">fingerprint</span>
            <span>Masuk dengan Sidik Jari / Face ID</span>
        </button>

        <!-- Card Footer -->
        <div class="border-t border-border-subtle dark:border-dark-border pt-space-md mt-space-md text-center">
            <p class="text-label-xs text-text-secondary dark:text-dark-text-secondary">
                Belum punya akun?
                <a href="{{ route('register') }}"
                    class="text-forest-primary dark:text-dark-lime font-bold hover:underline ml-1">
                    Daftar Sekarang
                </a>
            </p>
        </div>
    </section>

    <!-- 3. Bottom Security Badge -->
    <footer class="relative z-10 mt-space-md mb-space-xs flex justify-center w-full">
        <div
            class="bg-surface-card/90 dark:bg-dark-surface/90 border border-border-subtle dark:border-dark-border rounded-full px-4 py-2 flex items-center justify-center gap-2 backdrop-blur-md">
            <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-base shrink-0"
                style="font-variation-settings: 'FILL' 1;">verified_user</span>
            <span class="text-label-xs text-text-secondary dark:text-dark-text-secondary">Dilindungi enkripsi end-to-end
                256-bit standar perbankan</span>
        </div>
    </footer>
</x-guest-layout>
