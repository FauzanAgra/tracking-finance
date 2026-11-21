{{--
    Reusable Delete Confirmation Modal (IMV-001)
    --------------------------------------------------
    Trigger dari halaman mana saja menggunakan Alpine.js event dispatch:
        $dispatch('confirm-delete', { label: 'Nama Item', action: '/url/delete' })

    Method DELETE digunakan secara default via hidden input _method.
--}}
<div
    x-data="{
        open: false,
        label: '',
        formAction: '',
    }"
    @confirm-delete.window="
        label      = $event.detail.label;
        formAction = $event.detail.action;
        open       = true;
    "
    @keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[60] flex items-end justify-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-delete-title"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-black/60 backdrop-blur-[2px]"
    ></div>

    {{-- Bottom-Sheet --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-250"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-5 pt-3 pb-8"
    >
        {{-- Drag Handle --}}
        <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mb-5"></div>

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-rose-100 dark:bg-rose-950/50 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
                <span class="material-symbols-outlined text-[22px]">delete_forever</span>
            </div>
            <div>
                <h3 id="confirm-delete-title" class="text-title-md font-bold text-text-primary dark:text-dark-text-primary leading-tight">
                    Konfirmasi Hapus
                </h3>
                <p class="text-label-xs text-text-muted dark:text-dark-text-muted mt-0.5">
                    Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
        </div>

        {{-- Body --}}
        <div class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-2xl px-4 py-3 mb-5">
            <p class="text-body-sm text-rose-700 dark:text-rose-300 font-medium">
                Hapus <strong x-text="label"></strong>?
            </p>
        </div>

        {{-- Actions --}}
        <div class="grid grid-cols-2 gap-2.5">
            <button
                type="button"
                @click="open = false"
                class="py-3 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border text-label-lg font-semibold text-text-secondary dark:text-dark-text-secondary cursor-pointer active:scale-95 transition-transform"
            >
                Batal
            </button>
            <form :action="formAction" method="POST" @submit="open = false">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <button
                    type="submit"
                    class="w-full py-3 rounded-full bg-rose-600 dark:bg-rose-500 text-white text-label-lg font-semibold shadow-sm cursor-pointer active:scale-95 transition-transform"
                >
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
