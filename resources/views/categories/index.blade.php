<x-layouts.mobile title="Kelola Kategori" activeNav="transactions">
    @php
        $iconOptions = [
            'restaurant',
            'local_cafe',
            'directions_car',
            'shopping_bag',
            'receipt_long',
            'medical_services',
            'sports_esports',
            'school',
            'payments',
            'work',
            'trending_up',
            'card_giftcard',
            'home',
            'tag',
        ];
        $colorOptions = ['#1E4D38', '#10B981', '#3B82F6', '#F59E0B', '#F43F5E', '#8B5CF6', '#EC4899', '#14B8A6'];
    @endphp

    <div
        x-data="{
            filterType: 'all',
            modalOpen: @js($errors->any()),
            modalMode: @js(old('_method') === 'PUT' ? 'edit' : 'create'),
            editCategoryId: @js(old('_category_id')),
            form: {
                name: @js(old('name', '')),
                type: @js(old('type', 'expense')),
                icon: @js(old('icon', 'restaurant')),
                color: @js(old('color', '#F43F5E')),
            },
            openCreate() {
                this.modalMode = 'create';
                this.editCategoryId = null;
                this.form = {
                    name: '',
                    type: this.filterType === 'income' ? 'income' : 'expense',
                    icon: this.filterType === 'income' ? 'payments' : 'restaurant',
                    color: this.filterType === 'income' ? '#10B981' : '#F43F5E',
                };
                this.modalOpen = true;
            },
            openEdit(category) {
                this.modalMode = 'edit';
                this.editCategoryId = category.id;
                this.form = {
                    name: category.name,
                    type: category.type,
                    icon: category.icon || 'tag',
                    color: category.color || '#10B981',
                };
                this.modalOpen = true;
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
                <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary tracking-tight">Kelola Kategori</h1>
            </div>

            <div class="flex items-center gap-2">
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

                <button
                    type="button"
                    @click="openCreate()"
                    class="flex items-center gap-1 bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas px-3 py-2 rounded-full text-label-md font-semibold shadow-sm active:scale-95 transition-all cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Tambah</span>
                </button>
            </div>
        </header>

        <div class="px-gutter py-space-md space-y-4">
            @if (session('status'))
                <div class="p-3 rounded-xl bg-semantic-profit-bg/60 dark:bg-dark-surface-tint border border-forest-primary/20 dark:border-dark-lime/30 text-forest-primary dark:text-dark-lime text-body-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-body-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Segmented Filter Tabs -->
            <div class="bg-surface-card dark:bg-dark-surface p-1 rounded-xl border border-border-subtle dark:border-dark-border flex items-center gap-1">
                <button
                    type="button"
                    @click="filterType = 'all'"
                    :class="filterType === 'all' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary hover:text-text-primary dark:hover:text-dark-text-primary'"
                    class="flex-1 py-1.5 rounded-lg text-label-md font-semibold transition-all cursor-pointer"
                >
                    Semua ({{ $categories->count() }})
                </button>
                <button
                    type="button"
                    @click="filterType = 'expense'"
                    :class="filterType === 'expense' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary hover:text-text-primary dark:hover:text-dark-text-primary'"
                    class="flex-1 py-1.5 rounded-lg text-label-md font-semibold transition-all cursor-pointer"
                >
                    Pengeluaran ({{ $categories->where('type', 'expense')->count() }})
                </button>
                <button
                    type="button"
                    @click="filterType = 'income'"
                    :class="filterType === 'income' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary hover:text-text-primary dark:hover:text-dark-text-primary'"
                    class="flex-1 py-1.5 rounded-lg text-label-md font-semibold transition-all cursor-pointer"
                >
                    Pemasukan ({{ $categories->where('type', 'income')->count() }})
                </button>
            </div>

            <!-- Categories List -->
            @if ($categories->isEmpty())
                <div class="bg-surface-card dark:bg-dark-surface border border-dashed border-border-subtle dark:border-dark-border rounded-3xl p-8 text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-accent-lime-soft dark:bg-dark-surface-tint flex items-center justify-center text-forest-primary dark:text-dark-lime">
                        <span class="material-symbols-outlined text-[28px]">category</span>
                    </div>
                    <h3 class="text-title-md font-bold text-text-primary dark:text-dark-text-primary">Belum Ada Kategori</h3>
                    <p class="text-body-sm text-text-muted dark:text-dark-text-muted">
                        Buat kategori pengeluaran &amp; pemasukan pertamamu untuk mencatat arus kas dengan rapi.
                    </p>
                    <button
                        type="button"
                        @click="openCreate()"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-semibold active:scale-95 transition-transform cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Tambah Kategori Pertama</span>
                    </button>
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach ($categories as $category)
                        @php
                            $categoryPayload = [
                                'id' => $category->id,
                                'name' => $category->name,
                                'type' => $category->type,
                                'icon' => $category->icon,
                                'color' => $category->color,
                            ];
                        @endphp
                        <div
                            x-show="filterType === 'all' || filterType === '{{ $category->type }}'"
                            class="bg-surface-card dark:bg-dark-surface border border-border-subtle dark:border-dark-border rounded-2xl p-3.5 flex items-center justify-between gap-3 shadow-xs"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-xs"
                                    style="background-color: {{ $category->color }};"
                                >
                                    <span class="material-symbols-outlined text-[20px]">{{ $category->icon }}</span>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-title-md font-semibold text-text-primary dark:text-dark-text-primary truncate leading-tight">
                                        {{ $category->name }}
                                    </h3>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold {{ $category->type === 'income' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                            {{ $category->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                                        </span>
                                        <span class="text-label-xs text-text-muted dark:text-dark-text-muted">
                                            {{ $category->transactions_count }} transaksi
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <button
                                    type="button"
                                    @click="openEdit(@js($categoryPayload))"
                                    aria-label="Edit {{ $category->name }}"
                                    class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime active:scale-95 transition-all cursor-pointer"
                                >
                                    <span class="material-symbols-outlined text-[15px]">edit</span>
                                </button>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        aria-label="Hapus {{ $category->name }}"
                                        class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-rose-600 dark:hover:text-rose-400 active:scale-95 transition-all cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-[15px]">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Bottom-Sheet Modal Form Create / Edit Kategori -->
        <div
            x-show="modalOpen"
            x-cloak
            @keydown.escape.window="modalOpen = false"
            class="fixed inset-0 z-50 flex items-end justify-center"
            role="dialog"
            aria-modal="true"
            aria-labelledby="category-modal-title"
        >
            <div
                x-show="modalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="modalOpen = false"
                class="fixed inset-0 bg-black/60 backdrop-blur-[2px]"
            ></div>

            <section
                x-show="modalOpen"
                x-transition:enter="ease-out duration-250"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="relative z-10 w-full max-w-[390px] bg-surface-card dark:bg-dark-surface rounded-t-3xl border-t border-x border-border-subtle dark:border-dark-border shadow-2xl px-4 pt-2 pb-6 max-h-[90vh] overflow-y-auto no-scrollbar"
            >
                <div class="w-12 h-1.5 rounded-full bg-border-subtle dark:bg-dark-border mx-auto mt-1 mb-3"></div>

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 id="category-modal-title" class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary" x-text="modalMode === 'edit' ? 'Edit Kategori' : 'Tambah Kategori'"></h2>
                        <p class="text-label-xs text-text-muted dark:text-dark-text-muted">Atur nama, jenis arus kas, icon &amp; warna badge</p>
                    </div>
                    <button
                        type="button"
                        @click="modalOpen = false"
                        aria-label="Tutup modal"
                        class="w-8 h-8 rounded-full bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary cursor-pointer"
                    >
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

                <form
                    method="POST"
                    :action="modalMode === 'edit' ? '/categories/' + editCategoryId : '{{ route('categories.store') }}'"
                    class="space-y-4"
                >
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <div>
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="_category_id" :value="editCategoryId">
                        </div>
                    </template>

                    <!-- Segmented Type Switcher -->
                    <div>
                        <label class="block text-label-md font-semibold text-text-secondary dark:text-dark-text-secondary mb-1.5">Jenis Kategori</label>
                        <input type="hidden" name="type" x-model="form.type">
                        <div class="bg-surface-tint dark:bg-dark-sidebar p-1 rounded-xl border border-border-subtle dark:border-dark-border flex items-center gap-1">
                            <button
                                type="button"
                                @click="form.type = 'expense'"
                                :class="form.type === 'expense' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                                class="flex-1 py-1.5 rounded-lg text-label-md font-semibold transition-all cursor-pointer"
                            >
                                Pengeluaran
                            </button>
                            <button
                                type="button"
                                @click="form.type = 'income'"
                                :class="form.type === 'income' ? 'bg-forest-primary dark:bg-dark-forest text-white dark:text-dark-lime shadow-xs' : 'text-text-secondary dark:text-dark-text-secondary'"
                                class="flex-1 py-1.5 rounded-lg text-label-md font-semibold transition-all cursor-pointer"
                            >
                                Pemasukan
                            </button>
                        </div>
                    </div>

                    <!-- Category Name -->
                    <div>
                        <label for="cat-name" class="block text-label-md font-semibold text-text-secondary dark:text-dark-text-secondary mb-1">Nama Kategori</label>
                        <input
                            id="cat-name"
                            type="text"
                            name="name"
                            x-model="form.name"
                            required
                            maxlength="100"
                            placeholder="Contoh: Makan Siang, Gaji Bulanan"
                            class="w-full rounded-xl bg-surface-tint dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border px-3.5 py-2.5 text-body-md text-text-primary dark:text-dark-text-primary focus:outline-none focus:border-forest-primary dark:focus:border-dark-lime"
                        >
                    </div>

                    <!-- Icon Picker -->
                    <div>
                        <label class="block text-label-md font-semibold text-text-secondary dark:text-dark-text-secondary mb-1.5">Pilih Icon</label>
                        <input type="hidden" name="icon" x-model="form.icon">
                        <div class="grid grid-cols-7 gap-2">
                            @foreach ($iconOptions as $iconName)
                                <button
                                    type="button"
                                    @click="form.icon = '{{ $iconName }}'"
                                    :class="form.icon === '{{ $iconName }}' ? 'bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas border-forest-primary dark:border-dark-lime scale-105' : 'bg-surface-tint dark:bg-dark-surface-tint text-text-secondary dark:text-dark-text-secondary border-border-subtle dark:border-dark-border'"
                                    class="h-10 rounded-xl border flex items-center justify-center transition-all cursor-pointer"
                                >
                                    <span class="material-symbols-outlined text-[20px]">{{ $iconName }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Color Swatch Picker -->
                    <div>
                        <label class="block text-label-md font-semibold text-text-secondary dark:text-dark-text-secondary mb-1.5">Warna Badge</label>
                        <input type="hidden" name="color" x-model="form.color">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            @foreach ($colorOptions as $hex)
                                <button
                                    type="button"
                                    @click="form.color = '{{ $hex }}'"
                                    :class="form.color === '{{ $hex }}' ? 'ring-2 ring-offset-2 ring-forest-primary dark:ring-dark-lime dark:ring-offset-dark-surface scale-110' : ''"
                                    class="w-7 h-7 rounded-full transition-all cursor-pointer"
                                    style="background-color: {{ $hex }};"
                                    aria-label="Pilih warna {{ $hex }}"
                                ></button>
                            @endforeach
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 rounded-full bg-forest-primary dark:bg-dark-lime text-white dark:text-dark-canvas text-label-lg font-bold shadow-md active:scale-[0.98] transition-transform cursor-pointer"
                        x-text="modalMode === 'edit' ? 'Simpan Perubahan' : 'Simpan Kategori'"
                    ></button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.mobile>
