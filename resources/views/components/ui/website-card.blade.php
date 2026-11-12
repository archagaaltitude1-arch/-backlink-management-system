@props([
    'website',
])

@php
    $domain = $website->domain ?: ($website->website_name ?: 'Website');
    $niche = $website->niche ?? null;

    if (is_object($niche)) {
        $niche = $niche->niche_name ?? null;
    }

    $formatTraffic = function ($value): string {
        if ($value === null || $value === '') {
            return '';
        }

        $number = (float) $value;

        if ($number >= 1000000) {
            $formatted = number_format($number / 1000000, 1);

            return rtrim(rtrim($formatted, '0'), '.').'M';
        }

        if ($number >= 1000) {
            $decimals = $number >= 10000 ? 0 : 1;
            $formatted = number_format($number / 1000, $decimals);

            return rtrim(rtrim($formatted, '0'), '.').'K';
        }

        return number_format($number, 0);
    };

    $priceLabel = is_numeric($website->price)
        ? '$'.number_format((float) $website->price, 2)
        : '';

    $href = url('/marketplace/'.(config('marketplace.demo') ? $domain : ($website->id ?: $domain)));
@endphp

<article {{ $attributes->class('panel flex h-full flex-col p-5 sm:p-6') }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="truncate font-display text-lg font-bold text-navy">
                <a href="{{ $href }}" class="hover:text-brand focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                    {{ $domain }}
                </a>
            </h3>
            @if ($website->website_name && $website->website_name !== $domain)
                <p class="mt-1 truncate text-sm text-navy/60">{{ $website->website_name }}</p>
            @endif
        </div>
        <p class="shrink-0 font-display text-lg font-bold text-brand">{{ $priceLabel }}</p>
    </div>

    @if (filled($niche))
        <p class="mt-4">
            <span class="badge bg-canvas text-navy">{{ $niche }}</span>
        </p>
    @endif

    <div class="mt-4 flex flex-wrap gap-2">
        <x-ui.metric-badge label="DR" :value="$website->domain_rating ?? ''" />
        <x-ui.metric-badge label="DA" :value="$website->domain_authority ?? ''" />
        <x-ui.metric-badge label="Traffic" :value="$formatTraffic($website->monthly_traffic)" />
    </div>

    <div class="mt-5">
        <x-ui.button :href="$href" variant="outline" class="w-full">View website</x-ui.button>
    </div>
</article>
