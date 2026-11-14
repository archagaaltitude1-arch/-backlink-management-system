@php
    $shot = $offer['screenshots'][0] ?? null;
    $metrics = $offer['metrics'];
@endphp
<section class="pb-card pb-gallery" aria-label="Website preview">
    <div class="pb-stage" data-gallery-pane="preview">
        @if ($shot)
            <img src="{{ $shot }}" alt="Screenshot of {{ $offer['display_name'] }}">
        @else
            <img src="{{ asset('images/publish/preview-placeholder.svg') }}" alt="">
            <p class="pb-address">{{ $offer['domain'] }}</p>
            <p class="pb-hero-domain">{{ $offer['domain'] }}</p>
        @endif
    </div>
    <div class="pb-stage pb-metric-hero" data-gallery-pane="metrics" hidden>
        <p class="pb-metric-hero-domain">{{ $offer['domain'] }}</p>
        <div class="pb-metric-hero-pills">
            <span>DA {{ $num($metrics['da']) }}</span>
            <span>PA {{ $num($metrics['pa']) }}</span>
            <span>DR {{ $num($metrics['dr']) }}</span>
        </div>
        <p class="pb-metric-hero-note">Guest post available</p>
    </div>
    <div class="pb-thumbs" role="group" aria-label="Preview images">
        <button type="button" class="pb-thumb" data-gallery-thumb="preview" aria-pressed="true" aria-label="Website preview">
            @if ($shot)
                <img src="{{ $shot }}" alt="">
            @else
                <img src="{{ asset('images/publish/preview-placeholder.svg') }}" alt="">
            @endif
        </button>
        <button type="button" class="pb-thumb pb-thumb-metric" data-gallery-thumb="metrics" aria-pressed="false" aria-label="Metric card">
            <span>{{ strtoupper(mb_substr($offer['domain'], 0, 1)) }}</span>
            <span>DA {{ $num($metrics['da']) }}</span>
        </button>
    </div>
</section>
