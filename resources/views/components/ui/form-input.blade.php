@props([
    'label',
    'name',
    'type' => 'text',
    'id' => null,
    'value' => null,
    'hint' => null,
])

@php
    $fieldId = $id ?? $name;
    $errorMessage = $errors->first($name);
    $hintId = $hint ? $fieldId.'-hint' : null;
    $errorId = $errorMessage ? $fieldId.'-error' : null;
    $describedBy = collect([$hintId, $errorId])->filter()->implode(' ');
@endphp

<div>
    <label for="{{ $fieldId }}" class="block text-sm font-medium text-navy">{{ $label }}</label>
    <input
        id="{{ $fieldId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        @if ($errorMessage) aria-invalid="true" @endif
        {{ $attributes->class([
            'mt-1.5 w-full rounded-xl bg-white px-3 py-2.5 text-base text-navy ring-1 transition placeholder:text-navy/40 focus:outline-2 focus:outline-offset-2 focus:outline-brand sm:text-sm',
            'ring-brand' => (bool) $errorMessage,
            'ring-navy/15' => ! $errorMessage,
        ]) }}
    >
    @if ($hint)
        <p id="{{ $hintId }}" class="mt-1.5 text-xs text-navy/60">{{ $hint }}</p>
    @endif
    @if ($errorMessage)
        <p id="{{ $errorId }}" class="mt-1.5 text-sm text-brand">{{ $errorMessage }}</p>
    @endif
</div>
