<x-layouts.mobile title="Notifikasi & Peringatan">
    <div class="flex-1 flex flex-col">
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
                <div>
                    <div class="flex items-center gap-1.5">
                        <h1 class="text-headline-sm font-bold text-text-primary dark:text-dark-text-primary leading-tight">
                            Pusat Notifikasi
                        </h1>
                        @if($unreadCount > 0)
                            <span class="px-2 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-bold">
                                {{ $unreadCount }} Baru
                            </span>
                        @endif
                    </div>
                    <p class="text-label-xs text-text-muted dark:text-dark-text-secondary">
                        Peringatan cerdas budget, utang & impian
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                        @csrf
                        <button
                            type="submit"
                            title="Tandai Semua Dibaca"
                            class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-forest-primary dark:hover:text-dark-lime active:scale-95 transition-all cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-[18px]">done_all</span>
                        </button>
                    </form>
                @endif

                @if($notifications->total() > 0)
                    <form
                        method="POST"
                        action="{{ route('notifications.clear') }}"
                        onsubmit="return confirm('Apakah kamu yakin ingin membersihkan semua riwayat notifikasi?');"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            title="Bersihkan Semua"
                            class="w-9 h-9 rounded-full bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center text-text-secondary dark:text-dark-text-secondary hover:text-rose-600 dark:hover:text-rose-400 active:scale-95 transition-all cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-[18px]">delete_sweep</span>
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <!-- Filter Tabs -->
        <div class="px-gutter pt-3 pb-2 flex items-center gap-2 border-b border-border-subtle/40 dark:border-dark-border/40">
            <a
                href="{{ route('notifications.index', ['filter' => 'all']) }}"
                class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $filter === 'all' ? 'bg-forest-primary text-white dark:bg-dark-lime dark:text-dark-canvas' : 'bg-surface-card dark:bg-dark-surface-tint text-text-secondary dark:text-dark-text-secondary border border-border-subtle dark:border-dark-border' }}"
            >
                Semua ({{ $notifications->total() }})
            </a>
            <a
                href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $filter === 'unread' ? 'bg-forest-primary text-white dark:bg-dark-lime dark:text-dark-canvas' : 'bg-surface-card dark:bg-dark-surface-tint text-text-secondary dark:text-dark-text-secondary border border-border-subtle dark:border-dark-border' }}"
            >
                Belum Dibaca ({{ $unreadCount }})
            </a>
        </div>

        <!-- Notification List -->
        <div class="px-gutter py-3 space-y-2.5">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = $notification->read_at === null;
                    $color = $data['color'] ?? 'amber';
                    $icon = $data['icon'] ?? 'notifications';

                    $badgeColors = [
                        'rose' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400',
                        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                        'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400',
                        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400',
                    ];
                    $badgeClass = $badgeColors[$color] ?? $badgeColors['amber'];
                @endphp

                <div class="p-3.5 rounded-2xl border transition-all {{ $isUnread ? 'bg-surface-card dark:bg-dark-surface-tint border-forest-primary/30 dark:border-dark-lime/30 shadow-sm' : 'bg-surface-card/60 dark:bg-dark-surface-tint/50 border-border-subtle dark:border-dark-border/50 opacity-80' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $badgeClass }}">
                            <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="text-sm font-bold text-text-primary dark:text-dark-text-primary leading-tight">
                                    {{ $data['title'] ?? 'Pemberitahuan' }}
                                </h2>
                                @if($isUnread)
                                    <span class="w-2 h-2 rounded-full bg-forest-primary dark:bg-dark-lime shrink-0 mt-1"></span>
                                @endif
                            </div>

                            <p class="text-xs text-text-secondary dark:text-dark-text-secondary mt-1 leading-relaxed">
                                {{ $data['message'] ?? '' }}
                            </p>

                            <div class="flex items-center justify-between gap-2 mt-3 pt-2 border-t border-border-subtle/50 dark:border-dark-border/40">
                                <span class="text-[10px] text-text-muted dark:text-dark-text-muted">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>

                                <div class="flex items-center gap-2">
                                    @if(!empty($data['action_url']))
                                        <a
                                            href="{{ $data['action_url'] }}"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-forest-primary dark:text-dark-lime hover:underline"
                                        >
                                            <span>{{ $data['action_label'] ?? 'Buka' }}</span>
                                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                        </a>
                                    @endif

                                    @if($isUnread)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                title="Tandai dibaca"
                                                class="text-[11px] font-medium text-text-muted hover:text-text-primary dark:hover:text-dark-text-primary px-1.5 py-0.5 rounded transition-colors cursor-pointer"
                                            >
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 px-4">
                    <div class="w-14 h-14 rounded-2xl bg-surface-card dark:bg-dark-surface-tint border border-border-subtle dark:border-dark-border flex items-center justify-center mx-auto text-forest-primary dark:text-dark-lime mb-3">
                        <span class="material-symbols-outlined text-[28px]">notifications_none</span>
                    </div>
                    <h2 class="text-sm font-bold text-text-primary dark:text-dark-text-primary">
                        Semua Aman & Terkendali!
                    </h2>
                    <p class="text-xs text-text-muted dark:text-dark-text-muted mt-1 max-w-xs mx-auto">
                        Tidak ada peringatan budget, utang, atau impian yang memerlukan tindakanmu saat ini.
                    </p>
                </div>
            @endforelse

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="pt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.mobile>
