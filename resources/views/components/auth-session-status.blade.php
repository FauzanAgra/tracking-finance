@props(['status'])

@if ($status)
    <div
        {{ $attributes->merge(['class' => 'flex items-center gap-2 p-3 rounded-xl bg-semantic-profit-bg text-semantic-profit-text border border-accent-lime dark:bg-dark-surface-tint dark:text-dark-lime dark:border-dark-border text-xs font-medium']) }}>
        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
        <span>{{ $status }}</span>
    </div>
@endif
