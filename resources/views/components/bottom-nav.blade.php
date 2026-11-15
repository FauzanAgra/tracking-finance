@props(['active' => 'dashboard'])

<nav
    aria-label="Navigasi Utama"
    class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[390px] z-30 bg-surface-card/90 dark:bg-dark-sidebar/90 backdrop-blur-lg border-t border-border-subtle dark:border-dark-border px-4 py-2 flex items-center justify-between"
>
    {{-- 1. Home / Dashboard --}}
    <a
        href="{{ route('dashboard') }}"
        class="flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-colors {{ $active === 'dashboard' ? 'text-forest-primary dark:text-dark-lime' : 'text-text-muted dark:text-dark-text-muted hover:text-text-primary dark:hover:text-dark-text-primary' }}"
    >
        <span class="material-symbols-outlined text-[22px] {{ $active === 'dashboard' ? 'fill-1' : '' }}">home</span>
        <span class="text-[10px] font-semibold">Beranda</span>
    </a>

    {{-- 2. Dompet & Valas --}}
    <a
        href="{{ route('wallets.index') }}"
        class="flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-colors {{ $active === 'wallets' ? 'text-forest-primary dark:text-dark-lime' : 'text-text-muted dark:text-dark-text-muted hover:text-text-primary dark:hover:text-dark-text-primary' }}"
    >
        <span class="material-symbols-outlined text-[22px] {{ $active === 'wallets' ? 'fill-1' : '' }}">account_balance_wallet</span>
        <span class="text-[10px] font-semibold">Dompet</span>
    </a>

    {{-- 3. Center Quick Action (Catat Cepat TXN-002) --}}
    <a
        href="{{ route('transactions.index', ['quick' => 1]) }}"
        title="Catat Cepat"
        class="w-11 h-11 -mt-5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas shadow-lg shadow-forest-primary/25 dark:shadow-dark-lime/25 flex items-center justify-center active:scale-95 transition-transform"
    >
        <span class="material-symbols-outlined text-[24px]">add</span>
    </a>

    {{-- 4. Riwayat / Transaksi (TXN-004) --}}
    <a
        href="{{ route('transactions.index') }}"
        class="flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-colors {{ $active === 'transactions' ? 'text-forest-primary dark:text-dark-lime' : 'text-text-muted dark:text-dark-text-muted hover:text-text-primary dark:hover:text-dark-text-primary' }}"
    >
        <span class="material-symbols-outlined text-[22px] {{ $active === 'transactions' ? 'fill-1' : '' }}">receipt_long</span>
        <span class="text-[10px] font-semibold">Riwayat</span>
    </a>

    {{-- 5. Profil --}}
    <a
        href="{{ route('profile.edit') }}"
        class="flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-colors {{ $active === 'profile' ? 'text-forest-primary dark:text-dark-lime' : 'text-text-muted dark:text-dark-text-muted hover:text-text-primary dark:hover:text-dark-text-primary' }}"
    >
        <span class="material-symbols-outlined text-[22px] {{ $active === 'profile' ? 'fill-1' : '' }}">person</span>
        <span class="text-[10px] font-semibold">Profil</span>
    </a>
</nav>
