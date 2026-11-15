<x-guest-layout title="Daftar Akun">
    <!-- Ambient Background Forest Glow -->
    <div
        class="absolute -top-24 -left-20 w-72 h-72 bg-accent-lime/10 dark:bg-dark-forest/20 rounded-full blur-3xl pointer-events-none">
    </div>
    <div
        class="absolute top-1/3 -right-24 w-80 h-80 bg-forest-primary/5 dark:bg-forest-primary/15 rounded-full blur-3xl pointer-events-none">
    </div>
    <div class="absolute bottom-10 left-1/4 w-60 h-60 bg-dark-lime/5 rounded-full blur-3xl pointer-events-none"></div>

    <!-- 1. Top Section: Back Button & Theme Toggle -->
    <header class="relative z-10 w-full pt-space-xs">
        <div class="flex items-center justify-between w-full mb-space-md">
            <!-- Back Button to Login -->
            <a href="{{ route('login') }}"
                class="w-9 h-9 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime hover:bg-surface-card dark:hover:bg-dark-surface active:scale-95 transition-all"
                title="Kembali ke halaman masuk" aria-label="Kembali">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            </a>

            <!-- Theme Toggle Button -->
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
                    style="font-variation-settings: 'FILL' 1;">person_add</span>
            </div>
            <h1 class="text-headline-lg-mobile font-bold tracking-tight text-text-primary dark:text-dark-text-primary">
                Mulai Kelola Finansial</h1>
            <p class="text-body-sm text-text-secondary dark:text-dark-text-secondary mt-1">Daftar akun baru dan pantau
                seluruh asetmu.</p>
        </div>
    </header>

    <!-- 2. Main Bento Form Card -->
    <section
        class="relative z-10 w-full bg-surface-card dark:bg-dark-surface rounded-3xl p-space-lg border border-border-subtle dark:border-dark-border shadow-xl shadow-black/5 dark:shadow-black/40">
        <!-- Card Header -->
        <div class="mb-space-md">
            <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Buat Akun Baru</h2>
            <p class="text-label-xs text-text-secondary dark:text-dark-text-secondary mt-1">Lengkapi data di bawah untuk
                memulai pencatatan portofoliomu.</p>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('register') }}" class="space-y-space-md" x-data="{
            password: '',
            get strength() {
                if (!this.password) return 0;
                let s = 0;
                if (this.password.length >= 8) s++;
                if (/[A-Z]/.test(this.password)) s++;
                if (/[0-9]/.test(this.password)) s++;
                if (/[^A-Za-z0-9]/.test(this.password)) s++;
                return s;
            }
        }">
            @csrf

            <!-- Input 1: Name -->
            <div>
                <label
                    class="block text-label-md font-semibold tracking-wider text-text-secondary dark:text-dark-text-secondary uppercase mb-1.5"
                    for="name">
                    NAMA LENGKAP
                </label>
                <div
                    class="relative rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border focus-within:border-forest-primary dark:focus-within:border-dark-lime flex items-center px-3.5 py-3 gap-3 transition-colors">
                    <span
                        class="material-symbols-outlined text-text-muted dark:text-dark-text-muted shrink-0">person</span>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        autocomplete="name" placeholder="Nama Lengkap"
                        class="w-full bg-transparent p-0 text-text-primary dark:text-dark-text-primary placeholder-text-muted dark:placeholder-dark-text-muted text-body-md focus:ring-0 focus:outline-none border-none leading-none" />
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
            </div>

            <!-- Input 2: Email -->
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
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        autocomplete="username" placeholder="nama@email.com"
                        class="w-full bg-transparent p-0 text-text-primary dark:text-dark-text-primary placeholder-text-muted dark:placeholder-dark-text-muted text-body-md focus:ring-0 focus:outline-none border-none leading-none" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <!-- Input 3: Password -->
            <div x-data="{ show: false }">
                <label
                    class="block text-label-md font-semibold tracking-wider text-text-secondary dark:text-dark-text-secondary uppercase mb-1.5"
                    for="password">
                    KATA SANDI
                </label>
                <div
                    class="relative rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border focus-within:border-forest-primary dark:focus-within:border-dark-lime flex items-center px-3.5 py-3 gap-3 transition-colors">
                    <span
                        class="material-symbols-outlined text-text-muted dark:text-dark-text-muted shrink-0">lock</span>
                    <input id="password" :type="show ? 'text' : 'password'" name="password" x-model="password" required
                        autocomplete="new-password" placeholder="Minimal 8 karakter"
                        :class="{ 'tracking-widest': !show && password.length > 0 }"
                        class="w-full bg-transparent p-0 text-text-primary dark:text-dark-text-primary placeholder-text-muted dark:placeholder-dark-text-muted text-body-md focus:ring-0 focus:outline-none border-none leading-none" />
                    <button type="button" @click="show = !show" aria-label="Toggle password visibility"
                        class="text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime focus:outline-none flex items-center transition-colors">
                        <span class="material-symbols-outlined text-[20px]" x-show="!show">visibility</span>
                        <span class="material-symbols-outlined text-[20px]" x-show="show" x-cloak>visibility_off</span>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />

                <!-- Password Strength Meter (Stitch Visual) -->
                <div class="mt-2" x-show="password.length > 0" x-cloak>
                    <div class="grid grid-cols-4 gap-1.5 mb-1">
                        <div class="h-1 rounded-full transition-colors"
                            :class="strength >= 1 ? (strength === 1 ? 'bg-semantic-loss-text' : strength === 2 ?
                                    'bg-semantic-warning-text' : 'bg-dark-lime') :
                                'bg-border-subtle dark:bg-dark-border'">
                        </div>
                        <div class="h-1 rounded-full transition-colors"
                            :class="strength >= 2 ? (strength === 2 ? 'bg-semantic-warning-text' : 'bg-dark-lime') :
                                'bg-border-subtle dark:bg-dark-border'">
                        </div>
                        <div class="h-1 rounded-full transition-colors"
                            :class="strength >= 3 ? 'bg-dark-lime' : 'bg-border-subtle dark:bg-dark-border'"></div>
                        <div class="h-1 rounded-full transition-colors"
                            :class="strength >= 4 ? 'bg-dark-lime' : 'bg-border-subtle dark:bg-dark-border'"></div>
                    </div>
                    <p class="text-[10px] text-text-secondary dark:text-dark-text-secondary"
                        x-text="strength <= 1 ? 'Kekuatan: Lemah' : strength === 2 ? 'Kekuatan: Cukup' : strength === 3 ? 'Kekuatan: Kuat' : 'Kekuatan: Sangat Aman'">
                    </p>
                </div>
            </div>

            <!-- Input 4: Confirm Password -->
            <div x-data="{ show: false }">
                <label
                    class="block text-label-md font-semibold tracking-wider text-text-secondary dark:text-dark-text-secondary uppercase mb-1.5"
                    for="password_confirmation">
                    KONFIRMASI KATA SANDI
                </label>
                <div
                    class="relative rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border focus-within:border-forest-primary dark:focus-within:border-dark-lime flex items-center px-3.5 py-3 gap-3 transition-colors">
                    <span
                        class="material-symbols-outlined text-text-muted dark:text-dark-text-muted shrink-0">lock_clock</span>
                    <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation"
                        required autocomplete="new-password" placeholder="Ulangi kata sandi"
                        :class="{ 'tracking-widest': !show }"
                        class="w-full bg-transparent p-0 text-text-primary dark:text-dark-text-primary placeholder-text-muted dark:placeholder-dark-text-muted text-body-md focus:ring-0 focus:outline-none border-none leading-none" />
                    <button type="button" @click="show = !show" aria-label="Toggle password confirmation visibility"
                        class="text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime focus:outline-none flex items-center transition-colors">
                        <span class="material-symbols-outlined text-[20px]" x-show="!show">visibility</span>
                        <span class="material-symbols-outlined text-[20px]" x-show="show"
                            x-cloak>visibility_off</span>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
            </div>

            <!-- Primary CTA Button -->
            <button type="submit"
                class="w-full h-12 rounded-xl bg-forest-primary dark:bg-dark-lime hover:bg-forest-deep dark:hover:bg-accent-lime text-white dark:text-dark-canvas text-label-lg font-bold tracking-wide flex items-center justify-center gap-2 shadow-lg shadow-forest-primary/20 dark:shadow-dark-lime/20 active:scale-[0.98] transition-all cursor-pointer mt-2">
                <span>Daftar Akun</span>
                <span class="material-symbols-outlined text-base">arrow_forward</span>
            </button>
        </form>

        <!-- Card Footer -->
        <div class="border-t border-border-subtle dark:border-dark-border pt-space-md mt-space-md text-center">
            <p class="text-label-xs text-text-secondary dark:text-dark-text-secondary">
                Sudah punya akun?
                <a href="{{ route('login') }}"
                    class="text-forest-primary dark:text-dark-lime font-bold hover:underline ml-1">
                    Masuk Sekarang
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
            <span class="text-label-xs text-text-secondary dark:text-dark-text-secondary">Data finansialmu tersimpan
                aman &amp; terenkripsi</span>
        </div>
    </footer>
</x-guest-layout>
