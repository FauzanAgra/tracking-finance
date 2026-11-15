@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-1 text-xs text-semantic-loss-text dark:text-rose-400']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-center gap-1.5 font-medium">
                <span class="material-symbols-outlined text-[15px]"
                    style="font-variation-settings: 'FILL' 1;">error</span>
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
