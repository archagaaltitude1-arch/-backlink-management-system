@php
    $points = collect($chart ?? []);
    $max = max(1, (float) $points->max('value'));
@endphp
<div class="flex h-40 items-end gap-3">
    @forelse ($points as $point)
        <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
            <span class="text-[11px] font-medium tabular-nums text-navy/70">{{ $point['display'] }}</span>
            <div class="flex h-24 w-full items-end">
                <div class="w-full rounded-t-lg bg-brand" style="height: {{ max(4, (float) $point['value'] / $max * 100) }}%"></div>
            </div>
            <span class="truncate text-[11px] text-navy/55">{{ $point['label'] }}</span>
        </div>
    @empty
        <p class="text-sm text-navy/60">No chart data yet.</p>
    @endforelse
</div>
