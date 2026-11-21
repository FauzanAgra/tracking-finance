{{--
    Global Flash Toast Notification (IMV-002)
    --------------------------------------------------
    Ditampilkan otomatis jika ada session flash:
      - session('status')  → toast hijau (sukses)
      - session('success') → toast hijau (sukses)
      - session('error')   → toast merah (error/peringatan)

    Toast muncul dari bawah layar (slide-up), auto-dismiss setelah 3,5 detik,
    dengan progress bar yang mengempis sebagai visual countdown.
--}}
@php
    $toastMessage = session('status') ?? session('success') ?? session('error') ?? '';
    $isError      = session()->has('error');
@endphp

@if ($toastMessage)
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 3500)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-24 inset-x-4 max-w-[358px] mx-auto z-[70]"
        role="alert"
        aria-live="polite"
    >
        <div class="relative overflow-hidden flex items-center gap-3 px-4 py-3 rounded-2xl shadow-lg border
            {{ $isError
                ? 'bg-rose-600 dark:bg-rose-700 border-rose-500/80 text-white'
                : 'bg-forest-primary dark:bg-dark-surface border-forest-primary/80 dark:border-dark-lime/40 text-white dark:text-dark-text-primary'
            }}"
        >
            {{-- Icon --}}
            <span class="material-symbols-outlined text-[20px] shrink-0 {{ $isError ? 'text-rose-200' : 'text-accent-lime dark:text-dark-lime' }}">
                {{ $isError ? 'error' : 'check_circle' }}
            </span>

            {{-- Message --}}
            <p class="text-label-md font-semibold flex-1 leading-snug">{{ $toastMessage }}</p>

            {{-- Close button --}}
            <button
                type="button"
                @click="show = false"
                class="shrink-0 w-6 h-6 flex items-center justify-center rounded-full opacity-60 hover:opacity-100 transition-opacity cursor-pointer"
                aria-label="Tutup notifikasi"
            >
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>

            {{-- Auto-dismiss progress bar --}}
            <div
                x-data="{ w: 100 }"
                x-init="$nextTick(() => { w = 0 })"
                class="absolute bottom-0 left-0 h-0.5 {{ $isError ? 'bg-rose-300/60' : 'bg-white/30' }} rounded-b-2xl transition-all duration-[3500ms] ease-linear"
                :style="`width: ${w}%`"
            ></div>
        </div>
    </div>
@endif
