@props([
    'name',
    'id' => null,
    'model' => null,
    'value' => '',
    'placeholder' => '0',
    'onInput' => null,
])

@if ($model)
    <input type="hidden" name="{{ $name }}" :value="{{ $model }}">
    <input
        type="text"
        inputmode="decimal"
        @if ($id) id="{{ $id }}" @endif
        :value="window.formatMoney({{ $model }})"
        @input="const raw = window.parseMoney($event.target.value); {{ $model }} = raw; $event.target.value = window.formatMoney(raw); @if ($onInput) {{ $onInput }}; @endif"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'tabular-nums']) }}
    >
@else
    <div x-data="{ rawVal: @js((string) $value) }" class="contents">
        <input type="hidden" name="{{ $name }}" :value="rawVal">
        <input
            type="text"
            inputmode="decimal"
            @if ($id) id="{{ $id }}" @endif
            :value="window.formatMoney(rawVal)"
            @input="rawVal = window.parseMoney($event.target.value); $event.target.value = window.formatMoney(rawVal);"
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => 'tabular-nums']) }}
        >
    </div>
@endif
