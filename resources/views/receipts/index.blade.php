<x-layouts.mobile title="AI Receipt Scanner" activeNav="transactions">
    <div
        x-data="{
            previewUrl: null,
            fileName: '',
            isDragging: false,
            submitting: false,
            handleFile(file) {
                if (!file) return;
                this.fileName = file.name;
                const reader = new FileReader();
                reader.onload = (e) => { this.previewUrl = e.target.result; };
                reader.readAsDataURL(file);
            }
        }"
        class="flex-1 flex flex-col"
    >
        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-30 bg-canvas-base/85 dark:bg-dark-canvas/85 backdrop-blur-md px-gutter py-space-sm flex items-center justify-between border-b border-border-subtle/50 dark:border-dark-border/40">
            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('transactions.index') }}"
                    aria-label="Kembali ke Riwayat"
                    class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-primary dark:text-dark-text-primary active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight leading-none">Scan Struk AI</h1>
                    <span class="text-label-xs text-text-muted dark:text-dark-text-muted">Ekstraksi otomatis Gemini Vision</span>
                </div>
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
                class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-forest-primary dark:text-dark-lime active:scale-95 transition-all"
            >
                <span class="material-symbols-outlined text-[18px]" x-show="darkMode">dark_mode</span>
                <span class="material-symbols-outlined text-[18px]" x-show="!darkMode" x-cloak>light_mode</span>
            </button>
        </header>

        <div class="px-gutter py-space-md space-y-4">
            @if (session('error'))
                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-body-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-label-md space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Drag & Drop / Camera Upload Form (AI-002) -->
            <form
                method="POST"
                action="{{ route('receipts.store') }}"
                enctype="multipart/form-data"
                @submit="submitting = true"
                class="space-y-4"
            >
                @csrf
                <label
                    for="receipt-file"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="isDragging = false; const f = $event.dataTransfer.files[0]; if (f) { $refs.fileInput.files = $event.dataTransfer.files; handleFile(f); }"
                    :class="isDragging ? 'border-forest-primary dark:border-dark-lime bg-accent-lime-soft/30 dark:bg-dark-surface-tint' : 'border-border-subtle dark:border-dark-border bg-surface-card dark:bg-dark-surface'"
                    class="block border-2 border-dashed rounded-3xl p-6 text-center cursor-pointer transition-all"
                >
                    <input
                        id="receipt-file"
                        x-ref="fileInput"
                        type="file"
                        name="receipt"
                        accept="image/jpeg,image/png,image/webp"
                        capture="environment"
                        required
                        @change="handleFile($event.target.files[0])"
                        class="sr-only"
                    >

                    <template x-if="!previewUrl">
                        <div class="space-y-3">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-accent-lime-soft dark:bg-dark-surface-tint flex items-center justify-center text-forest-primary dark:text-dark-lime">
                                <span class="material-symbols-outlined text-[32px]">photo_camera</span>
                            </div>
                            <div>
                                <h2 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Upload atau Foto Struk</h2>
                                <p class="text-body-sm text-text-muted dark:text-dark-text-muted mt-1">
                                    Drag &amp; drop gambar nota di sini, atau tap untuk buka kamera HP (JPG, PNG, WEBP maks. 5MB).
                                </p>
                            </div>
                        </div>
                    </template>

                    <template x-if="previewUrl">
                        <div class="space-y-3">
                            <img :src="previewUrl" alt="Preview Struk" class="max-h-64 mx-auto rounded-2xl object-contain border border-border-subtle dark:border-dark-border shadow-sm">
                            <p class="text-label-md font-semibold text-forest-primary dark:text-dark-lime truncate" x-text="fileName"></p>
                            <p class="text-label-xs text-text-muted dark:text-dark-text-muted">Tap gambar untuk mengganti foto</p>
                        </div>
                    </template>
                </label>

                <button
                    type="submit"
                    :disabled="!previewUrl || submitting"
                    :class="(!previewUrl || submitting) ? 'opacity-50 cursor-not-allowed' : 'active:scale-[0.98] cursor-pointer'"
                    class="w-full py-3.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md flex items-center justify-center gap-2 transition-all"
                >
                    <span class="material-symbols-outlined text-[20px]">auto_awesome</span>
                    <span x-text="submitting ? 'Memindai Struk dengan AI...' : 'Pindai Struk Sekarang'"></span>
                </button>
            </form>

            <!-- Recent Scans History -->
            @if ($recentScans->isNotEmpty())
                <section class="space-y-2 pt-2">
                    <h2 class="text-label-md font-bold text-text-muted dark:text-dark-text-secondary uppercase tracking-wider px-1">
                        Riwayat Scan Terbaru
                    </h2>
                    <div class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl divide-y divide-border-subtle dark:divide-dark-border overflow-hidden">
                        @foreach ($recentScans as $scan)
                            @php
                                $data = $scan->raw_gemini_response ?? [];
                                $merchant = $data['merchant_name'] ?? 'Struk #'.$scan->id;
                                $amount = (float) ($data['total_amount'] ?? 0);
                            @endphp
                            <a href="{{ route('receipts.show', $scan) }}" class="p-3.5 flex items-center justify-between gap-3 hover:bg-surface-tint/60 dark:hover:bg-dark-surface-tint/60 transition-colors">
                                <div class="min-w-0">
                                    <h3 class="text-body-md font-semibold text-text-primary dark:text-dark-text-primary truncate">{{ $merchant ?: 'Struk #'.$scan->id }}</h3>
                                    <p class="text-label-xs text-text-muted dark:text-dark-text-muted">
                                        {{ $scan->created_at->diffForHumans() }} &bull;
                                        {{ $scan->generated_transaction_id ? 'Sudah Disimpan' : ucfirst($scan->status) }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    @if ($amount > 0)
                                        <span class="text-body-md font-bold tabular-nums text-text-primary dark:text-dark-text-primary">
                                            Rp {{ number_format($amount, 0, ',', '.') }}
                                        </span>
                                    @endif
                                    <span class="material-symbols-outlined text-[18px] text-text-muted align-middle ml-1">chevron_right</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-layouts.mobile>
