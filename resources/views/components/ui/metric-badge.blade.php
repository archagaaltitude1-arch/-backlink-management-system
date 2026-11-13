@props([
    'label',
    'value',
])

@php
    $metricKey = strtolower((string) $label);
    $isSpam = str_contains($metricKey, 'spam');
    $numeric = null;

    if (is_numeric($value)) {
        $numeric = (float) $value;
    } elseif (preg_match('/\d+(?:\.\d+)?/', (string) $value, $matches) === 1) {
        $numeric = (float) $matches[0];
    }

    if ($isSpam) {
        if ($numeric !== null && $numeric <= 3) {
            $tone = 'bg-green-50 text-green-800 ring-1 ring-green-700/20';
        } elseif ($numeric !== null && $numeric <= 7) {
            $tone = 'bg-amber-50 text-amber-800 ring-1 ring-amber-600/25';
        } else {
            $tone = 'bg-red-50 text-red-800 ring-1 ring-red-700/20';
        }
    } else {
        $tones = [
            'dr' => 'bg-brand text-white',
            'da' => 'bg-navy text-white',
            'traffic' => 'bg-canvas text-navy ring-1 ring-navy/10',
        ];

        $tone = $tones[$metricKey] ?? 'bg-white text-navy ring-1 ring-navy/10';
    }
@endphp

<span {{ $attributes->class("inline-flex min-w-16 flex-col rounded-xl px-3 py-2 $tone") }}>
    <span class="text-[10px] font-semibold tracking-wider uppercase">{{ $label }}</span>
    <span class="font-display text-sm font-bold">{{ $value }}</span>
</span>
