<x-layouts.mobile title="AI Financial Advisor">
    <div
        x-data="{
            promptText: '',
            isLoading: false,
            setPrompt(text) {
                this.promptText = text;
                this.$nextTick(() => {
                    this.$refs.messageInput.focus();
                });
            },
            handleSubmit() {
                if (!this.promptText.trim()) return;
                this.isLoading = true;
                this.$refs.chatForm.submit();
            },
            scrollToBottom() {
                const el = this.$refs.chatScroll;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            }
        }"
        x-init="$nextTick(() => scrollToBottom())"
        class="flex-1 flex flex-col min-h-screen relative pb-28"
    >
        <!-- Top App Bar -->
        <header class="sticky top-0 z-30 flex items-center justify-between px-gutter py-space-sm bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md border-b border-border-subtle/50 dark:border-dark-border/40">
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('dashboard') }}"
                    aria-label="Kembali ke Dashboard"
                    class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary truncate leading-tight">
                            Si Jamal Advisor
                        </h1>
                        <span class="px-2 py-0.5 rounded-full bg-forest-subtle dark:bg-dark-lime/20 text-forest-primary dark:text-dark-lime text-[10px] font-bold border border-forest-primary/20 dark:border-dark-lime/30">
                            Gemini AI
                        </span>
                    </div>
                    <p class="text-label-xs text-text-muted dark:text-dark-text-secondary truncate">
                        Konsultasi & rekomendasi keuangan cerdas
                    </p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('advisor.reset') }}"
                onsubmit="return confirm('Mulai percakapan baru dengan AI Advisor?');"
            >
                @csrf
                <button
                    type="submit"
                    title="Mulai Ulang Percakapan"
                    class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime active:scale-95 transition-all cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                </button>
            </form>
        </header>

        <!-- Live Financial Context Strip -->
        <div class="px-gutter pt-3 pb-2 bg-surface-card/60 dark:bg-dark-surface/40 border-b border-border-subtle/40 dark:border-dark-border/40">
            <div class="flex items-center justify-between text-[11px] text-text-secondary dark:text-dark-text-secondary">
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-forest-primary dark:text-dark-lime">account_balance_wallet</span>
                    <span>Saldo: <strong class="text-text-primary dark:text-dark-text-primary">Rp {{ number_format($context['total_balance'], 0, ',', '.') }}</strong></span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] {{ $context['net_cashflow'] >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">insights</span>
                    <span>Net: <strong class="{{ $context['net_cashflow'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $context['net_cashflow'] >= 0 ? '+' : '-' }}Rp {{ number_format(abs($context['net_cashflow']), 0, ',', '.') }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Chat History Scroll View -->
        <div
            x-ref="chatScroll"
            class="flex-1 px-gutter py-4 space-y-3.5 overflow-y-auto"
        >
            @foreach($messages as $msg)
                @php
                    $isUser = ($msg['role'] ?? 'user') === 'user';
                @endphp

                <div class="flex {{ $isUser ? 'justify-end' : 'justify-start' }} gap-2.5">
                    @if(! $isUser)
                        <div class="w-8 h-8 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">smart_toy</span>
                        </div>
                    @endif

                    <div class="max-w-[85%] {{ $isUser ? 'bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas rounded-2xl rounded-tr-sm p-3.5 shadow-sm' : 'bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border rounded-2xl rounded-tl-sm p-3.5 text-text-primary dark:text-dark-text-primary shadow-sm' }}">
                        <div class="text-body-sm whitespace-pre-wrap leading-relaxed space-y-1">
                            {!! nl2br(e($msg['text'] ?? '')) !!}
                        </div>
                        <div class="text-[9px] mt-1.5 {{ $isUser ? 'text-white/70 dark:text-dark-canvas/70 text-right' : 'text-text-muted dark:text-dark-text-muted text-left' }}">
                            {{ $msg['time'] ?? '' }}
                        </div>
                    </div>

                    @if($isUser)
                        <div class="w-8 h-8 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-forest-primary dark:text-dark-lime flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[18px]">person</span>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- AI Thinking Skeleton (when waiting for response) -->
            <div
                x-show="isLoading"
                x-cloak
                class="flex justify-start gap-2.5"
            >
                <div class="w-8 h-8 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas flex items-center justify-center shrink-0 mt-0.5 animate-pulse">
                    <span class="material-symbols-outlined text-[18px]">smart_toy</span>
                </div>
                <div class="bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border rounded-2xl rounded-tl-sm p-3.5 shadow-sm flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-forest-primary dark:bg-dark-lime animate-bounce"></span>
                    <span class="w-2 h-2 rounded-full bg-forest-primary dark:bg-dark-lime animate-bounce [animation-delay:0.2s]"></span>
                    <span class="w-2 h-2 rounded-full bg-forest-primary dark:bg-dark-lime animate-bounce [animation-delay:0.4s]"></span>
                    <span class="text-xs text-text-muted dark:text-dark-text-muted ml-1">Si Jamal sedang menganalisis data...</span>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Chat Bar -->
        <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[390px] z-30 bg-surface-card/95 dark:bg-dark-sidebar/95 backdrop-blur-lg border-t border-border-subtle dark:border-dark-border p-3 space-y-2.5">
            <!-- Quick Suggestion Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5 text-xs">
                <button
                    type="button"
                    @click="setPrompt('Bulan ini aku paling boros di mana?')"
                    class="shrink-0 px-2.5 py-1 rounded-full bg-canvas-base dark:bg-dark-canvas border border-border-subtle dark:border-dark-border text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime hover:border-forest-primary/30 transition-all cursor-pointer"
                >
                    💡 Pos Paling Boros
                </button>
                <button
                    type="button"
                    @click="setPrompt('Analisis diagnosa kesehatan keuanganku saat ini')"
                    class="shrink-0 px-2.5 py-1 rounded-full bg-canvas-base dark:bg-dark-canvas border border-border-subtle dark:border-dark-border text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime hover:border-forest-primary/30 transition-all cursor-pointer"
                >
                    🩺 Cek Kesehatan Arus Kas
                </button>
                <button
                    type="button"
                    @click="setPrompt('Bagaimana tips dan strategi mencapai target tabungan impianku?')"
                    class="shrink-0 px-2.5 py-1 rounded-full bg-canvas-base dark:bg-dark-canvas border border-border-subtle dark:border-dark-border text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime hover:border-forest-primary/30 transition-all cursor-pointer"
                >
                    🎯 Strategi Impian
                </button>
                <button
                    type="button"
                    @click="setPrompt('Berapa proyeksi saldo tabunganku 6 bulan ke depan?')"
                    class="shrink-0 px-2.5 py-1 rounded-full bg-canvas-base dark:bg-dark-canvas border border-border-subtle dark:border-dark-border text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime hover:border-forest-primary/30 transition-all cursor-pointer"
                >
                    📈 Proyeksi 6 Bulan
                </button>
            </div>

            <!-- Input Form -->
            <form
                x-ref="chatForm"
                method="POST"
                action="{{ route('advisor.ask') }}"
                @submit.prevent="handleSubmit()"
                class="flex items-center gap-2"
            >
                @csrf
                <div class="flex-1 relative">
                    <input
                        x-ref="messageInput"
                        x-model="promptText"
                        type="text"
                        name="message"
                        required
                        maxlength="1000"
                        placeholder="Tanya Si Jamal soal keuanganmu..."
                        :disabled="isLoading"
                        class="w-full pl-3.5 pr-3 py-2.5 rounded-2xl bg-canvas-base dark:bg-dark-canvas border border-border-subtle dark:border-dark-border text-xs text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime transition-all disabled:opacity-50"
                        @keydown.enter.prevent="handleSubmit()"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="isLoading || !promptText.trim()"
                    class="w-10 h-10 rounded-2xl bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas flex items-center justify-center shadow-md active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[20px]">send</span>
                </button>
            </form>
        </div>
    </div>
</x-layouts.mobile>
