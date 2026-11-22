<x-layouts.mobile title="Portofolio Investasi" activeNav="wallets">
    @php
        $today = now()->toDateString();
        $positions = $summary['positions'];
        $totalPnl = $summary['total_unrealized_pnl_idr'];
        $totalPnlPct = $summary['total_unrealized_pnl_pct'];
        $isProfit = $totalPnl >= 0;
    @endphp

    <div
        x-data="{
            filterType: 'all',
            tradeModalOpen: @js($errors->hasAny(['symbol', 'asset_type', 'type', 'lots', 'quantity', 'price_per_unit', 'wallet_id'])),
            manualModalOpen: @js($errors->has('current_price')),
            activeAsset: null,
            manualPriceInput: '',
            autoFee: true,
            tradeForm: {
                asset_type: @js(old('asset_type', 'idx_stock')),
                type: @js(old('type', 'buy')),
                symbol: @js(old('symbol', '')),
                name: @js(old('name', '')),
                currency: @js(old('currency', 'IDR')),
                lots: @js(old('lots', '')),
                quantity: @js(old('quantity', '')),
                price_per_unit: @js(old('price_per_unit', '')),
                fee: @js(old('fee', '0')),
                wallet_id: @js((string) old('wallet_id', '')),
                transaction_date: @js(old('transaction_date', $today)),
                notes: @js(old('notes', '')),
            },
            openTradeModal(preset = null) {
                this.autoFee = true;
                if (preset) {
                    this.tradeForm = {
                        asset_type: preset.asset_type,
                        type: 'buy',
                        symbol: preset.symbol,
                        name: preset.name,
                        currency: preset.currency,
                        lots: '',
                        quantity: '',
                        price_per_unit: String(preset.current_price || ''),
                        fee: '0',
                        wallet_id: '',
                        transaction_date: @js($today),
                        notes: '',
                    };
                } else {
                    this.tradeForm = {
                        asset_type: 'idx_stock',
                        type: 'buy',
                        symbol: '',
                        name: '',
                        currency: 'IDR',
                        lots: '',
                        quantity: '',
                        price_per_unit: '',
                        fee: '0',
                        wallet_id: '',
                        transaction_date: @js($today),
                        notes: '',
                    };
                }
                this.recalculateFee();
                this.tradeModalOpen = true;
            },
            recalculateFee() {
                if (!this.autoFee) return;
                const price = Number(this.tradeForm.price_per_unit) || 0;
                const qty = this.tradeForm.asset_type === 'idx_stock'
                    ? (Number(this.tradeForm.lots) || 0) * 100
                    : (Number(this.tradeForm.quantity) || 0);
                const gross = price * qty;
                if (this.tradeForm.asset_type === 'idx_stock' && gross > 0) {
                    const rate = this.tradeForm.type === 'sell' ? 0.0025 : 0.0015;
                    this.tradeForm.fee = String(Math.round(gross * rate));
                } else if (!this.tradeForm.fee) {
                    this.tradeForm.fee = '0';
                }
            },
            openManualPriceModal(asset) {
                this.activeAsset = asset;
                this.manualPriceInput = String(asset.current_price);
                this.manualModalOpen = true;
            }
        }"
        class="flex-1 flex flex-col"
    >
        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm flex items-center justify-between border-b border-border-subtle/50 dark:border-dark-border/40">
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('wallets.index') }}"
                    aria-label="Kembali ke Dompet"
                    class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-primary dark:text-dark-text-primary active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight">Portofolio</h1>
            </div>

            <div class="flex items-center gap-1.5">
                <form method="POST" action="{{ route('investments.refresh') }}">
                    @csrf
                    <button
                        type="submit"
                        title="Refresh Harga Pasar"
                        aria-label="Refresh Harga Pasar"
                        class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime active:scale-95 transition-all cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">sync</span>
                    </button>
                </form>

                <button
                    type="button"
                    @click="openTradeModal()"
                    class="flex items-center gap-1 bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas px-3 py-2 rounded-full text-label-md font-semibold shadow-sm active:scale-95 transition-all cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Trade</span>
                </button>
            </div>
        </header>

        <div class="px-gutter py-space-md space-y-4">

            <!-- Hero Portfolio Card (INV-004) -->
            <section class="bg-forest-primary dark:bg-dark-forest rounded-3xl p-5 text-white shadow-md relative overflow-hidden space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-label-xs uppercase tracking-wider text-white/75 font-semibold">Nilai Portofolio (IDR)</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white/15 text-accent-lime tabular-nums">
                        1 USD &approx; Rp {{ number_format($summary['usd_idr_rate'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="text-headline-lg-mobile font-bold tabular-nums tracking-tight">
                    Rp {{ number_format($summary['total_market_value_idr'], 0, ',', '.') }}
                </div>

                <div class="grid grid-cols-2 gap-2 pt-3 border-t border-white/15">
                    <div>
                        <span class="text-[11px] text-white/70 block">Total Modal</span>
                        <span class="text-label-lg font-semibold tabular-nums">
                            Rp {{ number_format($summary['total_invested_idr'], 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-[11px] text-white/70 block">Floating PnL</span>
                        <span class="text-label-lg font-bold tabular-nums {{ $isProfit ? 'text-accent-lime' : 'text-rose-300' }}">
                            {{ $isProfit ? '+' : '' }}Rp {{ number_format($totalPnl, 0, ',', '.') }} ({{ $isProfit ? '+' : '' }}{{ $totalPnlPct }}%)
                        </span>
                    </div>
                </div>
            </section>

            <!-- Asset Type Filter Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                @foreach (['all' => 'Semua', 'idx_stock' => 'Saham IDX', 'us_stock' => 'Saham US', 'crypto' => 'Crypto'] as $tabKey => $tabLabel)
                    <button
                        type="button"
                        @click="filterType = '{{ $tabKey }}'"
                        :class="filterType === '{{ $tabKey }}' ? 'bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas border-forest-primary dark:border-dark-lime' : 'bg-surface-card dark:bg-dark-surface text-text-secondary dark:text-dark-text-secondary border-border-subtle dark:border-dark-border'"
                        class="px-3.5 py-1.5 rounded-full text-label-md font-semibold border whitespace-nowrap active:scale-95 transition-all cursor-pointer"
                    >
                        {{ $tabLabel }}
                    </button>
                @endforeach
            </div>

            <!-- Asset Cards -->
            @if ($positions->isEmpty())
                <div class="bg-surface-card dark:bg-dark-surface border border-dashed border-border-subtle dark:border-dark-border rounded-3xl p-8 text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-accent-lime-soft dark:bg-dark-surface-tint flex items-center justify-center text-forest-primary dark:text-dark-lime">
                        <span class="material-symbols-outlined text-[28px]">trending_up</span>
                    </div>
                    <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Belum Ada Aset Investasi</h3>
                    <p class="text-body-sm text-text-muted dark:text-dark-text-muted">
                        Catat pembelian Saham IDX (Lot), Saham US, atau Crypto pertamamu dengan perhitungan Average Cost otomatis.
                    </p>
                    <button
                        type="button"
                        @click="openTradeModal()"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-semibold active:scale-95 transition-transform cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Catat Trade Pertama</span>
                    </button>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($positions as $pos)
                        @php
                            $asset = $pos['asset'];
                            $pnl = $pos['unrealized_pnl'];
                            $pnlPct = $pos['unrealized_pnl_pct'];
                            $assetProfit = $pnl >= 0;
                            $decimals = $asset->currency === 'IDR' ? 0 : 2;
                            $qtyLabel = $asset->asset_type === 'idx_stock'
                                ? number_format((float) $pos['lots'], 2, ',', '.').' Lot ('.number_format((float) $pos['quantity'], 0, ',', '.').' lbr)'
                                : rtrim(rtrim(number_format((float) $pos['quantity'], 6, '.', ''), '0'), '.').' '.$asset->symbol;
                            $assetPayload = [
                                'id' => $asset->id,
                                'symbol' => $asset->symbol,
                                'name' => $asset->name,
                                'asset_type' => $asset->asset_type,
                                'currency' => $asset->currency,
                                'current_price' => (float) $asset->current_price,
                            ];
                        @endphp

                        <article
                            x-show="filterType === 'all' || filterType === '{{ $asset->asset_type }}'"
                            class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-4 space-y-3 shadow-xs"
                        >
                            <div class="flex items-start justify-between gap-2.5">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">{{ $asset->symbol }}</h3>
                                        @if ($asset->is_manual_price)
                                            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                                Manual
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-label-xs text-text-muted dark:text-dark-text-muted">{{ $asset->name }} &bull; {{ $qtyLabel }}</p>
                                </div>

                                <div class="flex items-center gap-2.5">
                                    @if (!empty($pos['sparkline_points']))
                                        <div class="w-14 h-5 flex items-center justify-center shrink-0" title="Tren Harga Terkini">
                                            <svg viewBox="0 0 54 20" class="w-full h-full overflow-visible">
                                                <polyline
                                                    fill="none"
                                                    stroke="{{ $pos['is_trend_up'] ? '#10B981' : '#F43F5E' }}"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    points="{{ $pos['sparkline_points'] }}"
                                                />
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="text-right">
                                        <div class="text-title-md font-bold text-text-primary dark:text-dark-text-primary tabular-nums">
                                            {{ $asset->currency }} {{ number_format($pos['market_value'], $decimals, ',', '.') }}
                                        </div>
                                        <div class="text-label-xs font-semibold tabular-nums {{ $assetProfit ? 'text-emerald-600 dark:text-dark-lime' : 'text-rose-600 dark:text-[#FDA4AF]' }}">
                                            {{ $assetProfit ? '+' : '' }}{{ number_format($pnl, $decimals, ',', '.') }} ({{ $assetProfit ? '+' : '' }}{{ $pnlPct }}%)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 bg-surface-tint dark:bg-dark-sidebar rounded-xl p-2.5 border border-border-subtle dark:border-dark-border text-label-xs">
                                <div>
                                    <span class="text-text-muted dark:text-dark-text-secondary block">Avg Price (DCA)</span>
                                    <span class="font-semibold text-text-primary dark:text-dark-text-primary tabular-nums">
                                        {{ $asset->currency }} {{ number_format($pos['average_price'], $decimals, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-text-muted dark:text-dark-text-secondary block">Harga Terkini</span>
                                    <span class="font-semibold text-text-primary dark:text-dark-text-primary tabular-nums">
                                        {{ $asset->currency }} {{ number_format($pos['current_price'], $decimals, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    @click="openTradeModal(@js($assetPayload))"
                                    class="py-2 px-3 rounded-xl bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime text-label-md font-semibold flex items-center justify-center gap-1 cursor-pointer"
                                >
                                    <span class="material-symbols-outlined text-[15px]">add_chart</span>
                                    <span>Tambah Trade</span>
                                </button>
                                <button
                                    type="button"
                                    @click="openManualPriceModal(@js($assetPayload))"
                                    class="py-2 px-3 rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-text-primary dark:text-dark-text-primary text-label-md font-semibold flex items-center justify-center gap-1 cursor-pointer"
                                >
                                    <span class="material-symbols-outlined text-[15px]">edit</span>
                                    <span>Edit Harga Manual</span>
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        @include('investments.partials.modals')
    </div>
</x-layouts.mobile>
