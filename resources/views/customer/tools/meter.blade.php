@php
    $meterScore = is_numeric($score ?? null) ? max(0, min(100, (int) $score)) : null;
@endphp
<div>
    <div class="flex items-end justify-between gap-4">
        <p class="text-[13px] font-bold tracking-wide text-[#4A5560] uppercase">{{ $label }}</p>
        <p class="font-display text-[40px] leading-none font-extrabold text-navy">{{ $meterScore === null ? '' : $meterScore }}</p>
    </div>
    <div
        class="mt-3 h-3 overflow-hidden rounded-full bg-[#F2F2F2]"
        role="meter"
        aria-label="{{ $label }}"
        aria-valuemin="0"
        aria-valuemax="100"
        @if ($meterScore !== null) aria-valuenow="{{ $meterScore }}" @endif
    >
        <div class="h-full rounded-full bg-[#FF4917]" style="width: {{ $meterScore ?? 0 }}%"></div>
    </div>
</div>
