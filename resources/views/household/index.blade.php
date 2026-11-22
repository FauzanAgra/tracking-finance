<x-layouts.mobile title="Keuangan Keluarga">
    <div class="flex-1 flex flex-col">
        <!-- Top App Bar -->
        <header class="sticky top-0 z-30 flex items-center justify-between px-gutter py-space-sm bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md border-b border-border-subtle/50 dark:border-dark-border/40">
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('profile.edit') }}"
                    aria-label="Kembali ke Profil"
                    class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <div class="min-w-0">
                    <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary truncate leading-tight">
                        Keuangan Keluarga
                    </h1>
                    <p class="text-label-xs text-text-muted dark:text-dark-text-secondary truncate">
                        Multi-user & kolaborasi rumah tangga
                    </p>
                </div>
            </div>

            <div class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime">
                <span class="material-symbols-outlined text-[18px]">diversity_1</span>
            </div>
        </header>

        <div class="px-gutter py-4 space-y-4">
            @if(! $membership)
                <!-- STATE A: BELUM TERGABUNG RUMAH TANGGA -->
                <section class="bg-surface-card dark:bg-dark-surface-tint p-5 rounded-3xl border border-border-subtle dark:border-dark-border shadow-sm text-center space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-forest-subtle dark:bg-dark-lime/20 text-forest-primary dark:text-dark-lime flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-[32px]">group_add</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-text-primary dark:text-dark-text-primary">
                            Kelola Keuangan Bersama
                        </h2>
                        <p class="text-xs text-text-muted dark:text-dark-text-muted mt-1 leading-relaxed">
                            Satukan visi keuangan bersama pasangan atau keluarga. Pantau dompet bersama secara transparan dengan akun mandiri masing-masing.
                        </p>
                    </div>
                </section>

                <!-- Form 1: Buat Rumah Tangga Baru -->
                <section class="bg-surface-card dark:bg-dark-surface-tint p-4.5 rounded-3xl border border-border-subtle dark:border-dark-border shadow-sm space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-[20px]">add_home</span>
                        <h2 class="text-sm font-bold text-text-primary dark:text-dark-text-primary">
                            Buat Rumah Tangga Baru
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('household.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="name" class="block text-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">
                                Nama Rumah Tangga / Keluarga
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                required
                                maxlength="100"
                                placeholder="Contoh: Keluarga Jamal & Siti"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-canvas-base dark:bg-dark-canvas border border-border-subtle dark:border-dark-border text-xs text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                            />
                        </div>

                        <button
                            type="submit"
                            class="w-full py-2.5 rounded-xl bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas font-bold text-xs shadow-md active:scale-95 transition-all cursor-pointer"
                        >
                            Bentuk Rumah Tangga (Sebagai Kepala)
                        </button>
                    </form>
                </section>

                <div class="relative flex py-1 items-center">
                    <div class="flex-grow border-t border-border-subtle dark:border-dark-border"></div>
                    <span class="flex-shrink mx-3 text-[11px] font-semibold text-text-muted dark:text-dark-text-muted uppercase tracking-wider">Atau</span>
                    <div class="flex-grow border-t border-border-subtle dark:border-dark-border"></div>
                </div>

                <!-- Form 2: Gabung via Kode Undangan -->
                <section class="bg-surface-card dark:bg-dark-surface-tint p-4.5 rounded-3xl border border-border-subtle dark:border-dark-border shadow-sm space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-forest-primary dark:text-dark-lime text-[20px]">vpn_key</span>
                        <h2 class="text-sm font-bold text-text-primary dark:text-dark-text-primary">
                            Gabung dengan Kode Undangan
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('household.join') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="invite_code" class="block text-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">
                                Kode Undangan Keluarga
                            </label>
                            <input
                                type="text"
                                id="invite_code"
                                name="invite_code"
                                required
                                maxlength="20"
                                placeholder="Contoh: FAM-X89K2P"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-canvas-base dark:bg-dark-canvas border border-border-subtle dark:border-dark-border text-xs uppercase font-mono tracking-wider text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                            />
                        </div>

                        <button
                            type="submit"
                            class="w-full py-2.5 rounded-xl bg-surface-card dark:bg-dark-surface-tint border border-forest-primary dark:border-dark-lime text-forest-primary dark:text-dark-lime font-bold text-xs shadow-sm active:scale-95 transition-all cursor-pointer hover:bg-forest-subtle/30"
                        >
                            Gabung ke Keluarga
                        </button>
                    </form>
                </section>

            @else
                <!-- STATE B: SUDAH TERGABUNG RUMAH TANGGA -->
                @php
                    $household = $membership->household;
                    $isOwner = $membership->role === 'owner';
                @endphp

                <!-- 1. Household Header Card -->
                <section class="bg-forest-primary dark:bg-dark-surface rounded-3xl p-5 text-white dark:text-dark-text-primary border border-forest-primary dark:border-dark-border shadow-md relative overflow-hidden space-y-3">
                    <div class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-white/10 pointer-events-none"></div>

                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-accent-lime dark:text-dark-lime">home</span>
                            <span class="text-label-xs font-semibold uppercase tracking-wider text-white/80 dark:text-dark-text-secondary">Rumah Tangga</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full {{ $isOwner ? 'bg-accent-lime text-forest-deep dark:bg-dark-lime dark:text-dark-canvas' : 'bg-white/20 text-white dark:text-dark-text-primary' }} text-[10px] font-bold">
                            {{ $isOwner ? 'Kepala Keluarga' : 'Anggota' }}
                        </span>
                    </div>

                    <div class="relative z-10">
                        <h2 class="text-xl font-bold tracking-tight">{{ $household->name }}</h2>
                        <p class="text-xs text-white/70 dark:text-dark-text-secondary mt-0.5">
                            Dibentuk {{ $household->created_at->translatedFormat('d F Y') }} &bull; {{ $summary['members']->count() }} Anggota
                        </p>
                    </div>

                    <!-- Total Shared Wealth -->
                    <div class="pt-3 border-t border-white/15 dark:border-dark-border/60 relative z-10">
                        <span class="text-[11px] text-white/70 dark:text-dark-text-secondary block">Total Saldo Dibagikan Bersama</span>
                        <div class="text-2xl font-black tabular-nums tracking-tight text-accent-lime dark:text-dark-lime mt-0.5">
                            Rp {{ number_format($summary['total_shared_wealth'], 0, ',', '.') }}
                        </div>
                    </div>
                </section>

                <!-- 2. Invite Code Box -->
                <section
                    x-data="{
                        code: '{{ $household->invite_code }}',
                        copied: false,
                        copy() {
                            navigator.clipboard.writeText(this.code);
                            this.copied = true;
                            setTimeout(() => this.copied = false, 2500);
                        }
                    }"
                    class="bg-surface-card dark:bg-dark-surface-tint p-4 rounded-3xl border border-border-subtle dark:border-dark-border shadow-sm flex items-center justify-between gap-3"
                >
                    <div>
                        <span class="text-[10px] font-bold text-text-muted dark:text-dark-text-muted uppercase tracking-wider block">Kode Undangan Keluarga</span>
                        <span class="text-sm font-mono font-bold text-forest-primary dark:text-dark-lime tracking-wider" x-text="code"></span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            @click="copy()"
                            class="px-3 py-1.5 rounded-xl bg-canvas-base dark:bg-dark-canvas border border-border-subtle dark:border-dark-border text-xs font-semibold text-text-primary dark:text-dark-text-primary hover:text-forest-primary dark:hover:text-dark-lime active:scale-95 transition-all cursor-pointer flex items-center gap-1"
                        >
                            <span class="material-symbols-outlined text-[15px]" x-text="copied ? 'check' : 'content_copy'"></span>
                            <span x-text="copied ? 'Disalin!' : 'Salin'"></span>
                        </button>

                        @if($isOwner)
                            <form method="POST" action="{{ route('household.invite-code') }}">
                                @csrf
                                <button
                                    type="submit"
                                    title="Perbarui Kode Undangan"
                                    class="w-8 h-8 rounded-xl bg-canvas-base dark:bg-dark-canvas border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary hover:text-forest-primary dark:hover:text-dark-lime active:scale-95 transition-all cursor-pointer"
                                >
                                    <span class="material-symbols-outlined text-[16px]">refresh</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </section>

                <!-- 3. Members List Section -->
                <section class="space-y-2.5">
                    <h2 class="text-xs font-bold text-text-muted dark:text-dark-text-muted uppercase tracking-wider">
                        Daftar Anggota Keluarga ({{ $summary['per_member_summary']->count() }})
                    </h2>

                    <div class="space-y-2">
                        @foreach($summary['per_member_summary'] as $m)
                            <div class="bg-surface-card dark:bg-dark-surface-tint p-3.5 rounded-2xl border border-border-subtle dark:border-dark-border/70 flex items-center justify-between gap-3 shadow-sm">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-full bg-forest-subtle dark:bg-dark-lime/20 text-forest-primary dark:text-dark-lime flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ mb_substr($m['user']->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-bold text-text-primary dark:text-dark-text-primary truncate">
                                                {{ $m['user']->name }}
                                            </span>
                                            @if($m['role'] === 'owner')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-forest-primary/10 text-forest-primary dark:bg-dark-lime/20 dark:text-dark-lime">
                                                    Kepala
                                                </span>
                                            @endif
                                            @if($m['user']->id === auth()->id())
                                                <span class="text-[10px] text-text-muted">(Kamu)</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-text-muted dark:text-dark-text-muted truncate">
                                            Saldo dibagikan: <strong class="text-forest-primary dark:text-dark-lime">Rp {{ number_format($m['shared_balance'], 0, ',', '.') }}</strong> ({{ $m['shared_wallets_count'] }} dompet)
                                        </p>
                                    </div>
                                </div>

                                @if($isOwner && $m['user']->id !== auth()->id())
                                    <form
                                        method="POST"
                                        action="{{ route('household.remove-member', $m['user']->id) }}"
                                        onsubmit="return confirm('Keluarkan anggota ini dari rumah tangga?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            title="Keluarkan Anggota"
                                            class="w-7 h-7 rounded-lg bg-canvas-base dark:bg-dark-canvas border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-muted hover:text-rose-600 active:scale-95 transition-all cursor-pointer"
                                        >
                                            <span class="material-symbols-outlined text-[15px]">person_remove</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- 4. Shared Wallets Management Section -->
                <section class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs font-bold text-text-muted dark:text-dark-text-muted uppercase tracking-wider">
                            Kelola Pembagian Dompet Saya
                        </h2>
                    </div>

                    <div class="space-y-2">
                        @foreach($userWallets as $wallet)
                            <div class="bg-surface-card dark:bg-dark-surface-tint p-3 rounded-2xl border border-border-subtle dark:border-dark-border/60 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-white" style="background-color: {{ $wallet->color }};">
                                        <span class="material-symbols-outlined text-[16px]">{{ $wallet->icon }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-xs font-bold text-text-primary dark:text-dark-text-primary truncate">
                                            {{ $wallet->name }}
                                        </h3>
                                        <p class="text-[11px] text-text-muted dark:text-dark-text-muted">
                                            Rp {{ number_format($wallet->balance, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('wallets.toggle-share', $wallet) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="px-2.5 py-1 rounded-xl text-[11px] font-semibold border transition-all cursor-pointer {{ $wallet->is_shared ? 'bg-forest-primary text-white border-forest-primary dark:bg-dark-lime dark:text-dark-canvas dark:border-dark-lime' : 'bg-canvas-base text-text-secondary border-border-subtle dark:bg-dark-canvas dark:text-dark-text-secondary dark:border-dark-border' }}"
                                    >
                                        {{ $wallet->is_shared ? '✓ Dibagikan' : 'Pribadi' }}
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- 5. Leave or Dissolve Section -->
                <section class="pt-4 border-t border-border-subtle dark:border-dark-border space-y-2">
                    @if($isOwner)
                        <form
                            method="POST"
                            action="{{ route('household.destroy') }}"
                            onsubmit="return confirm('Apakah kamu yakin ingin membubarkan rumah tangga ini? Seluruh riwayat dan hubungan anggota akan dihapus.');"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="w-full py-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/40 text-rose-600 dark:text-rose-400 font-semibold text-xs active:scale-95 transition-all cursor-pointer"
                            >
                                Bubarkan Rumah Tangga Ini
                            </button>
                        </form>
                    @else
                        <form
                            method="POST"
                            action="{{ route('household.leave') }}"
                            onsubmit="return confirm('Apakah kamu yakin ingin keluar dari rumah tangga ini?');"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="w-full py-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/40 text-rose-600 dark:text-rose-400 font-semibold text-xs active:scale-95 transition-all cursor-pointer"
                            >
                                Keluar dari Rumah Tangga
                            </button>
                        </form>
                    @endif
                </section>
            @endif
        </div>
    </div>
</x-layouts.mobile>
