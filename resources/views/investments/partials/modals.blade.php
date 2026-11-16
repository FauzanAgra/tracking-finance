<!-- 1. Modal Trade Beli / Jual / Dividen (INV-002) -->
<div
    x-show="tradeModalOpen"
    x-cloak
    @keydown.escape.window="tradeModalOpen = false"
    class="fixed inset-0 z-50 flex items-end justify-center"
    role="dialog"
    aria-modal="true"
>
    <div @click="tradeModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
    <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6 max-h-[92vh] overflow-y-auto no-scrollbar">
        <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Catat Transaksi Aset</h2>
            <button type="button" @click="tradeModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
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

        <form method="POST" action="{{ route('investments.trades.store') }}" class="space-y-3.5">
            @csrf
            <input type="hidden" name="type" x-model="tradeForm.type">

            <!-- Buy / Sell / Dividend Segmented Control -->
            <div class="bg-surface-tint dark:bg-dark-sidebar p-1 rounded-xl border border-border-subtle dark:border-dark-border grid grid-cols-3 gap-1">
                <button type="button" @click="tradeForm.type = 'buy'; recalculateFee()" :class="tradeForm.type === 'buy' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary'" class="py-1.5 rounded-lg text-label-md font-semibold cursor-pointer">Beli</button>
                <button type="button" @click="tradeForm.type = 'sell'; recalculateFee()" :class="tradeForm.type === 'sell' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary'" class="py-1.5 rounded-lg text-label-md font-semibold cursor-pointer">Jual</button>
                <button type="button" @click="tradeForm.type = 'dividend'; recalculateFee()" :class="tradeForm.type === 'dividend' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary'" class="py-1.5 rounded-lg text-label-md font-semibold cursor-pointer">Dividen</button>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label for="inv-asset-type" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Kelas Aset</label>
                    <select id="inv-asset-type" name="asset_type" x-model="tradeForm.asset_type" @change="tradeForm.currency = tradeForm.asset_type === 'idx_stock' ? 'IDR' : (tradeForm.asset_type === 'us_stock' ? 'USD' : 'USDT'); recalculateFee()" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                        <option value="idx_stock">Saham IDX</option>
                        <option value="us_stock">Saham US</option>
                        <option value="crypto">Crypto</option>
                    </select>
                </div>

                <div>
                    <label for="inv-symbol" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Ticker / Simbol</label>
                    <input id="inv-symbol" type="text" name="symbol" x-model="tradeForm.symbol" required placeholder="BBCA / AAPL / BTC" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm uppercase text-text-primary dark:text-dark-text-primary">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label for="inv-name" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Nama Aset</label>
                    <input id="inv-name" type="text" name="name" x-model="tradeForm.name" placeholder="Bank Central Asia" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                </div>
                <div>
                    <label for="inv-currency" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Mata Uang</label>
                    <select id="inv-currency" name="currency" x-model="tradeForm.currency" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                        <option value="IDR">IDR</option>
                        <option value="USD">USD</option>
                        <option value="USDT">USDT</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div x-show="tradeForm.asset_type === 'idx_stock'">
                    <label for="inv-lots" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Jumlah Lot (1 Lot = 100)</label>
                    <input id="inv-lots" type="number" name="lots" step="any" min="0.01" x-model="tradeForm.lots" @input="recalculateFee()" :required="tradeForm.asset_type === 'idx_stock'" placeholder="2" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-md font-bold tabular-nums text-text-primary dark:text-dark-text-primary">
                </div>

                <div x-show="tradeForm.asset_type !== 'idx_stock'">
                    <label for="inv-qty" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Jumlah Unit / Koin</label>
                    <input id="inv-qty" type="number" name="quantity" step="any" min="0.00000001" x-model="tradeForm.quantity" @input="recalculateFee()" :required="tradeForm.asset_type !== 'idx_stock'" placeholder="0.005" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-md font-bold tabular-nums text-text-primary dark:text-dark-text-primary">
                </div>

                <div>
                    <label for="inv-price" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Harga per Unit</label>
                    <input id="inv-price" type="number" name="price_per_unit" step="any" min="0.000001" x-model="tradeForm.price_per_unit" @input="recalculateFee()" required placeholder="10000" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-md font-bold tabular-nums text-text-primary dark:text-dark-text-primary">
                </div>
            </div>

            <!-- Broker Fee Toggle + Manual Input -->
            <div class="rounded-xl bg-surface-tint dark:bg-dark-sidebar border border-border-subtle dark:border-dark-border p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <label for="inv-fee" class="text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary">Fee Broker / Gas</label>
                    <label class="inline-flex items-center gap-1.5 text-label-xs text-forest-primary dark:text-dark-lime font-semibold cursor-pointer">
                        <input type="checkbox" x-model="autoFee" @change="recalculateFee()" class="stitch-checkbox border-forest-primary dark:border-dark-lime">
                        <span>Auto Fee IDX (0.15% / 0.25%)</span>
                    </label>
                </div>
                <input id="inv-fee" type="number" name="fee" step="any" min="0" x-model="tradeForm.fee" @input="autoFee = false" class="w-full rounded-xl bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm tabular-nums text-text-primary dark:text-dark-text-primary">
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label for="inv-wallet" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Dompet Dana (Opsional)</label>
                    <select id="inv-wallet" name="wallet_id" x-model="tradeForm.wallet_id" class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                        <option value="">-- Tanpa Dompet --</option>
                        @foreach ($wallets as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->currency }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="inv-date" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Tanggal</label>
                    <input id="inv-date" type="date" name="transaction_date" x-model="tradeForm.transaction_date" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3 py-2 text-body-sm text-text-primary dark:text-dark-text-primary">
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md cursor-pointer">
                Simpan Transaksi Aset
            </button>
        </form>
    </section>
</div>

<!-- 2. Modal Edit Harga Manual / API Fallback (INV-006) -->
<div
    x-show="manualModalOpen"
    x-cloak
    @keydown.escape.window="manualModalOpen = false"
    class="fixed inset-0 z-50 flex items-end justify-center"
    role="dialog"
    aria-modal="true"
>
    <div @click="manualModalOpen = false" class="fixed inset-0 bg-black/65 backdrop-blur-[2px]"></div>
    <section class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6">
        <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary">Edit Harga Manual</h2>
                <p class="text-label-xs text-text-muted dark:text-dark-text-muted" x-text="activeAsset ? activeAsset.symbol + ' (' + activeAsset.currency + ')' : ''"></p>
            </div>
            <button type="button" @click="manualModalOpen = false" class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint flex items-center justify-center text-text-secondary cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form method="POST" :action="activeAsset ? '/investments/' + activeAsset.id + '/manual-price' : '#'" class="space-y-3.5">
            @csrf
            @method('PATCH')
            <div>
                <label for="manual-price-input" class="block text-label-xs font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Harga Terkini</label>
                <input id="manual-price-input" type="number" name="current_price" step="any" min="0.000001" x-model="manualPriceInput" required class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-title-md font-bold tabular-nums text-text-primary dark:text-dark-text-primary">
            </div>
            <button type="submit" class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md cursor-pointer">
                Simpan Harga Manual
            </button>
        </form>
    </section>
</div>
