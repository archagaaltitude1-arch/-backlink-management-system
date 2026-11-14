@extends('layouts.public')

@section('document_title', 'Publish a Guest Post on '.$offer['display_name'].' – '.$offer['link_type'].' Link | BacklinkPro')
@section('meta_description', $offer['meta_description'])

@push('head')
    @unless ($offer['demo'])
        @php
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => 'Publish a Guest Post on '.$offer['display_name'],
                'offers' => [
                    '@type' => 'Offer',
                    'price' => $offer['price'] === null ? '0' : number_format((float) $offer['price'], 2, '.', ''),
                    'priceCurrency' => 'USD',
                ],
            ];
            if (($offer['reviews']['count'] ?? 0) > 0) {
                $schema['aggregateRating'] = [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $offer['reviews']['average'],
                    'reviewCount' => $offer['reviews']['count'],
                ];
            }
        @endphp
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endunless
@endpush

@section('content')
@php
    $num = function ($value, int $places = 0) {
        if (! is_numeric($value)) {
            return '—';
        }

        return number_format((float) $value, $places);
    };
    $nicheHref = $offer['primary_slug'] && \Illuminate\Support\Facades\Route::has('niches.show')
        ? route('niches.show', $offer['primary_slug'])
        : null;
@endphp
<style>
    .pb-page { max-width: 1320px; margin: 0 auto; padding: 28px 20px 8px; color: #152029; font-family: Ubuntu, sans-serif; }
    .pb-page h1, .pb-page h2, .pb-page h3 { font-family: Montserrat, sans-serif; font-weight: 800; color: #152029; }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
    .pb-crumb { display: flex; flex-wrap: wrap; gap: 6px; margin: 0 0 12px; color: #4A5560; font-size: 14px; font-weight: 700; }
    .pb-crumb a { color: #4A5560; }
    .pb-note-demo { margin: 0 0 16px; color: #4A5560; font-size: 14px; }
    .pb-layout { display: flex; flex-direction: column; gap: 16px; }
    .pb-left, .pb-right { display: contents; }
    .pb-head { order: 1; } .pb-gallery { order: 2; } .pb-metrics { order: 3; } .pb-facts { order: 4; }
    .pb-countries { order: 5; } .pb-stats { order: 6; } .pb-rules { order: 7; } .pb-restricted { order: 8; } .pb-order { order: 9; }
    .pb-card { background: #fff; border: 1px solid #E3E5E8; border-radius: 16px; }
    .pb-h1 { margin: 0; font-size: 26px; line-height: 1.2; word-break: break-word; }
    .pb-badges { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 12px; }
    .pb-pill, .pb-id, .pb-premium, .pb-heart { display: inline-flex; align-items: center; justify-content: center; min-height: 32px; border-radius: 999px; padding: 4px 10px; font-size: 13px; font-weight: 700; }
    .pb-premium { background: #FF4917; color: #fff; gap: 6px; }
    .pb-hidden-url { color: #0F6E6E; background: #E3F4F4; }
    .pb-id { background: #fff; border: 1px solid #E3E5E8; color: #152029; }
    .pb-heart { width: 44px; height: 44px; padding: 0; border: 1px solid #E3E5E8; background: #fff; color: #152029; }
    .pb-price-row { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 16px; padding-bottom: 16px; border-bottom: 1px solid #E3E5E8; }
    .pb-price { margin: 0; font-family: Montserrat, sans-serif; font-size: 34px; font-weight: 800; line-height: 1; }
    .pb-tab { min-height: 44px; border: 1px solid #E3E5E8; border-radius: 12px; background: #fff; padding: 0 14px; color: #152029; font-weight: 700; }
    .pb-tab[aria-pressed="true"] { border-color: #FF4917; background: #FFF1EC; color: #C0340D; }
    .pb-gallery { padding: 12px; }
    .pb-stage { position: relative; aspect-ratio: 16 / 10; overflow: hidden; border-radius: 12px; background: #152029; }
    .pb-stage img { width: 100%; height: 100%; object-fit: cover; }
    .pb-address { position: absolute; top: 8.5%; left: 22%; margin: 0; color: #152029; font-size: clamp(11px, 1.6vw, 16px); font-weight: 700; }
    .pb-hero-domain { position: absolute; left: 8%; right: 14%; top: 38%; margin: 0; color: #152029; font-family: Montserrat, sans-serif; font-size: clamp(22px, 4vw, 48px); font-weight: 800; line-height: 1.1; word-break: break-word; }
    .pb-metric-hero { display: flex; flex-direction: column; justify-content: center; gap: 14px; padding: 24px; color: #fff; }
    .pb-metric-hero[hidden], .pb-stage[hidden] { display: none; }
    .pb-metric-hero-domain { margin: 0; font-family: Montserrat, sans-serif; font-size: clamp(20px, 3vw, 36px); font-weight: 800; word-break: break-word; }
    .pb-metric-hero-pills { display: flex; flex-wrap: wrap; gap: 8px; }
    .pb-metric-hero-pills span, .pb-metric-hero-note { display: inline-flex; min-height: 32px; align-items: center; border-radius: 999px; background: #fff; color: #152029; padding: 0 12px; font-weight: 700; }
    .pb-thumbs { display: flex; gap: 8px; margin-top: 10px; }
    .pb-thumb { width: 96px; height: 64px; padding: 0; overflow: hidden; border: 2px solid transparent; border-radius: 8px; background: #152029; }
    .pb-thumb[aria-pressed="true"] { border-color: #FF4917; }
    .pb-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .pb-thumb-metric { display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: 4px; color: #fff; font-size: 11px; font-weight: 700; padding: 6px; }
    .pb-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .pb-fact { background: #fff; border: 1px solid #E3E5E8; border-radius: 16px; padding: 16px 10px; text-align: center; }
    .pb-fact-wide { grid-column: 1 / -1; }
    .pb-fact-value { display: flex; align-items: center; justify-content: center; gap: 6px; margin: 0; font-weight: 700; word-break: break-word; }
    .pb-fact-value svg { width: 18px; height: 18px; flex: none; }
    .pb-fact-label { margin: 6px 0 0; color: #4A5560; font-size: 13px; }
    .pb-countries { padding: 16px; }
    .pb-countries h2, .pb-rules h2, .pb-restricted h2 { display: flex; align-items: center; gap: 8px; margin: 0 0 14px; font-size: 18px; }
    .pb-countries h2 svg, .pb-rules h2 svg { width: 20px; height: 20px; }
    .pb-countries ol { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
    .pb-country-row { display: flex; justify-content: space-between; gap: 12px; font-weight: 700; }
    .pb-country-name { display: flex; align-items: center; gap: 8px; min-width: 0; }
    .pb-code { display: inline-flex; min-width: 32px; justify-content: center; border-radius: 6px; background: #F2F2F2; padding: 2px 6px; font-size: 12px; }
    .pb-track { height: 6px; margin-top: 6px; border-radius: 999px; background: #E3E5E8; overflow: hidden; }
    .pb-track span { display: block; height: 100%; background: #3D7BC4; }
    .pb-track span.is-first { background: #FF4917; }
    .pb-country-foot { margin: 14px 0 0; color: #4A5560; font-size: 12px; text-align: center; }
    .pb-metrics { display: grid; grid-template-columns: 1fr; gap: 10px; }
    .pb-mbox { display: flex; min-width: 0; overflow: hidden; border: 1px solid; border-radius: 12px; background: #fff; }
    .pb-mbox-name { display: flex; align-items: center; justify-content: center; width: 96px; flex: none; padding: 10px; font-weight: 800; text-align: center; }
    .pb-mbox-values { display: flex; flex-wrap: wrap; gap: 12px 18px; padding: 12px; }
    .pb-mbox-stat { display: flex; flex-direction: column; gap: 2px; min-width: 72px; }
    .pb-mbox-k { color: #4A5560; font-size: 12px; text-decoration: underline; }
    .pb-mbox-stat strong { font-size: 20px; }
    .pb-mbox-authority { border-color: #3D7BC4; } .pb-mbox-authority .pb-mbox-name { background: #3D7BC4; color: #fff; } .pb-mbox-authority strong { color: #3D7BC4; }
    .pb-mbox-trust { border-color: #C0392B; } .pb-mbox-trust .pb-mbox-name { background: #FDE8E8; color: #C0392B; } .pb-mbox-trust strong { color: #C0392B; }
    .pb-mbox-strength { border-color: #FF4917; } .pb-mbox-strength .pb-mbox-name { background: #FFF1EC; color: #FF4917; } .pb-mbox-strength strong { color: #FF4917; }
    .pb-mbox-links { border-color: #17663A; } .pb-mbox-links .pb-mbox-name { background: #E7F5EC; color: #17663A; } .pb-mbox-links strong { color: #17663A; }
    .pb-mbox-links strong.is-risk { color: #A3221F; }
    .pb-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .pb-stat { background: #EEF5FC; border-radius: 12px; padding: 14px 10px; text-align: center; }
    .pb-stat strong { display: block; word-break: break-word; }
    .pb-stat span { color: #4A5560; font-size: 13px; }
    .pb-dofollow { color: #17663A; }
    .pb-rules, .pb-restricted { background: #EEF5FC; border-radius: 16px; padding: 16px; }
    .pb-restricted { background: transparent; padding: 0; }
    .pb-rules ul { margin: 0; padding-left: 18px; }
    .pb-rules li + li { margin-top: 6px; }
    .pb-samples { margin: 12px 0 0; }
    .pb-samples a { margin-right: 10px; color: #C0340D; font-weight: 700; }
    .pb-restricted-grid { display: grid; grid-template-columns: 1fr; gap: 10px; }
    .pb-restricted-grid article { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; border: 1px solid #E3E5E8; border-radius: 12px; background: #fff; padding: 12px; }
    .pb-restricted-grid svg { width: 28px; height: 28px; }
    .pb-restricted-grid h3 { margin: 0; font-size: 15px; }
    .pb-ok { color: #17663A; background: #E7F5EC; }
    .pb-no { color: #A3221F; background: #FDE8E8; }
    .pb-order { background: #fff; border: 1px solid #E3E5E8; border-radius: 16px; padding: 16px; }
    .pb-types, .pb-topics { margin: 0 0 16px; padding: 0; border: 0; }
    .pb-types legend, .pb-topics legend { margin-bottom: 8px; font-weight: 700; }
    .pb-choice { display: flex; gap: 10px; align-items: flex-start; width: 100%; margin-top: 8px; border: 1px solid #E3E5E8; border-radius: 12px; padding: 12px; }
    .pb-choice:has(input:checked) { border-color: #3D7BC4; background: #EEF5FC; }
    .pb-choice input { margin-top: 4px; width: 18px; height: 18px; accent-color: #3D7BC4; }
    .pb-help { color: #4A5560; }
    .pb-topic-wrap { display: flex; flex-wrap: wrap; gap: 8px; }
    .pb-topic { display: inline-flex; }
    .pb-topic input { position: absolute; opacity: 0; }
    .pb-topic span { display: inline-flex; align-items: center; min-height: 44px; border: 1px solid #E3E5E8; border-radius: 999px; padding: 0 14px; background: #fff; }
    .pb-topic input:checked + span { border-color: #3D7BC4; background: #EEF5FC; }
    .pb-topic input:focus-visible + span { outline: 2px solid #D63A10; outline-offset: 2px; }
    .pb-more { width: 100%; }
    .pb-more summary { min-height: 44px; display: inline-flex; align-items: center; cursor: pointer; color: #C0340D; font-weight: 700; }
    .pb-qty { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .pb-qty div { display: flex; align-items: center; gap: 6px; }
    .pb-qty button, .pb-qty input { min-width: 44px; min-height: 44px; border: 1px solid #E3E5E8; border-radius: 10px; background: #fff; text-align: center; font: inherit; }
    .pb-qty input { width: 64px; }
    .pb-summary { margin-top: 16px; padding-top: 12px; border-top: 1px solid #E3E5E8; }
    .pb-sum-row { display: flex; justify-content: space-between; gap: 12px; margin-top: 8px; }
    .pb-subtotal { margin-top: 12px; padding-top: 12px; border-top: 1px solid #E3E5E8; align-items: center; }
    .pb-subtotal strong { font-family: Montserrat, sans-serif; font-size: 24px; font-weight: 800; }
    .pb-note { margin: 12px 0; border-radius: 12px; background: #F2F2F2; padding: 12px; color: #4A5560; font-size: 14px; }
    .pb-cart { display: flex; align-items: center; justify-content: center; width: 100%; min-height: 52px; border: 0; border-radius: 12px; background: #D63A10; color: #fff; font-size: 16px; font-weight: 700; text-decoration: none; }
    .pb-cart:hover { background: #FF4917; color: #fff; }
    .pb-cart:disabled { cursor: not-allowed; }
    .pb-info { list-style: none; margin: 16px 0 0; padding: 0; display: grid; gap: 10px; }
    .pb-info strong { display: block; }
    .pb-info a { color: #C0340D; }
    .pb-tabs { display: flex; gap: 18px; margin-top: 28px; border-bottom: 1px solid #E3E5E8; }
    .pb-tabs [role="tab"] { min-height: 44px; border: 0; border-bottom: 3px solid transparent; background: transparent; padding: 0 2px; font-weight: 700; color: #4A5560; }
    .pb-tabs [aria-selected="true"] { border-bottom-color: #FF4917; color: #152029; }
    .pb-panel { padding: 20px 0 8px; }
    .pb-panel h3 { margin: 22px 0 8px; font-size: 20px; }
    .pb-lead { margin: 0; }
    .pb-defs { display: grid; gap: 8px; margin: 0; }
    .pb-defs div { display: flex; justify-content: space-between; gap: 12px; border-bottom: 1px solid #E3E5E8; padding-bottom: 6px; }
    .pb-defs dt { color: #4A5560; }
    .pb-defs dd { margin: 0; font-weight: 700; }
    .pb-cats { display: flex; flex-wrap: wrap; gap: 8px; }
    .pb-cats a, .pb-cats span { display: inline-flex; min-height: 36px; align-items: center; border-radius: 999px; background: #F2F2F2; padding: 0 12px; color: #152029; font-weight: 700; }
    .pb-review-top { display: grid; gap: 20px; }
    .pb-average { margin: 0; font-family: Montserrat, sans-serif; font-size: 48px; font-weight: 800; line-height: 1; }
    .pb-star-row { position: relative; display: inline-block; margin: 8px 0; color: #E3E5E8; letter-spacing: 2px; font-size: 22px; }
    .pb-star-fill { position: absolute; left: 0; top: 0; overflow: hidden; color: #FF4917; white-space: nowrap; }
    .pb-bars { list-style: none; margin: 12px 0 0; padding: 0; display: grid; gap: 6px; }
    .pb-bars li { display: grid; grid-template-columns: 36px 1fr 28px; gap: 8px; align-items: center; }
    .pb-bar { height: 8px; border-radius: 999px; background: #E3E5E8; overflow: hidden; }
    .pb-bar span { display: block; height: 100%; background: #FF4917; }
    .pb-write { background: #F2F2F2; border-radius: 16px; padding: 16px; }
    .pb-review-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; border: 0; border-radius: 12px; background: #D63A10; color: #fff; padding: 8px 14px; font-weight: 700; text-decoration: none; }
    .pb-review-btn:disabled { background: #E3E5E8; color: #4A5560; }
    .pb-review-btn-line { background: #fff; color: #152029; border: 1px solid #E3E5E8; }
    .pb-review-list { list-style: none; margin: 20px 0 0; padding: 0; display: grid; gap: 16px; }
    .pb-review-name { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin: 0; font-weight: 700; }
    .pb-flash { border-radius: 12px; background: #E7F5EC; color: #17663A; padding: 12px; }
    .pb-field { display: grid; gap: 6px; margin-top: 12px; font-weight: 700; }
    .pb-field input, .pb-field textarea { min-height: 44px; border: 1px solid #E3E5E8; border-radius: 10px; padding: 8px 10px; font: inherit; font-weight: 400; }
    .pb-field textarea { min-height: 120px; }
    .pb-star-choice { display: flex; gap: 8px; align-items: center; min-height: 36px; }
    .pb-dialog-actions { display: flex; gap: 8px; margin-top: 14px; }
    dialog { border: 0; border-radius: 16px; padding: 20px; max-width: 520px; width: calc(100% - 32px); }
    dialog::backdrop { background: rgba(21, 32, 41, .45); }
    .pb-similar { margin-top: 28px; }
    .pb-similar-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
    .pb-similar h2 { margin: 0; font-size: 26px; }
    .pb-similar-head button { width: 44px; height: 44px; border: 1px solid #E3E5E8; border-radius: 12px; background: #fff; font-size: 22px; }
    .pb-similar-row { display: flex; gap: 12px; overflow-x: auto; scroll-snap-type: x mandatory; padding: 12px 0 20px; }
    .pb-similar-card { flex: 0 0 240px; scroll-snap-align: start; border: 1px solid #E3E5E8; border-radius: 16px; background: #fff; padding: 14px; }
    .pb-similar-mark { display: grid; place-items: center; width: 100%; height: 72px; border-radius: 12px; background: #152029; color: #fff; font-family: Montserrat, sans-serif; font-weight: 800; font-size: 28px; }
    .pb-similar-card h3 { margin: 8px 0; font-size: 16px; }
    .pb-similar-price { margin: 8px 0; font-family: Montserrat, sans-serif; font-weight: 800; font-size: 20px; }
    .pb-similar-btn { display: flex; align-items: center; justify-content: center; min-height: 44px; border-radius: 10px; background: #D63A10; color: #fff; font-weight: 700; text-decoration: none; }
    .pb-page :focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
    @media (min-width: 700px) {
        .pb-metrics { grid-template-columns: 1fr 1fr; }
        .pb-mbox-wide { grid-column: 1 / -1; }
        .pb-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .pb-restricted-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .pb-review-top { grid-template-columns: 1.2fr .8fr; }
    }
    @media (max-width: 520px) {
        .pb-facts { grid-template-columns: 1fr 1fr; }
        .pb-fact-wide { grid-column: 1 / -1; }
    }
    @media (min-width: 1024px) {
        .pb-h1 { font-size: 34px; }
        .pb-layout { display: grid; grid-template-columns: minmax(0, 48fr) minmax(0, 52fr); gap: 28px; align-items: start; }
        .pb-left, .pb-right { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
        .pb-head, .pb-gallery, .pb-metrics, .pb-facts, .pb-countries, .pb-stats, .pb-rules, .pb-restricted, .pb-order { order: 0; }
    }
</style>

<div class="pb-page">
    <nav class="pb-crumb" aria-label="Breadcrumb">
        <a href="{{ \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/') }}">Home</a>
        <span aria-hidden="true">/</span>
        @if ($nicheHref)
            <a href="{{ $nicheHref }}">{{ $offer['primary_category'] }}</a>
        @else
            <span>{{ $offer['primary_category'] !== '' ? $offer['primary_category'] : '—' }}</span>
        @endif
        <span aria-hidden="true">/</span>
        <span>{{ $offer['display_name'] }}</span>
    </nav>
    @if ($offer['demo'])
        <p class="pb-note-demo">Sample data for preview</p>
    @endif

    <div class="pb-layout">
        <div class="pb-left">
            @include('customer.publish.partials.gallery')
            @include('customer.publish.partials.facts')
            @include('customer.publish.partials.countries')
        </div>
        <div class="pb-right">
            <div class="pb-head">
                <h1 class="pb-h1">Publish a Guest Post on {{ $offer['display_name'] }}</h1>
                <div class="pb-badges">
                    @if ($offer['premium'])
                        <span class="pb-premium"><svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="m12 3 2.6 5.6 6.1.8-4.5 4.2 1.1 6.1L12 16.8 6.7 19.7l1.1-6.1L3.3 9.4l6.1-.8L12 3Z"/></svg> Premium publisher</span>
                    @endif
                    @if (config('marketplace.hide_urls'))
                        <span class="pb-pill pb-hidden-url">URL hidden</span>
                    @endif
                    <span class="pb-id">Site ID: {{ $offer['site_id'] }}</span>
                    @auth
                        @if ($saveAction && $offer['website_id'])
                            <form method="POST" action="{{ $saveAction }}">
                                @csrf
                                <input type="hidden" name="website_id" value="{{ $offer['website_id'] }}">
                                <input type="hidden" name="domain" value="{{ $offer['domain'] }}">
                                <input type="hidden" name="website_name" value="{{ $offer['domain'] }}">
                                <input type="hidden" name="url" value="{{ 'https://'.$offer['domain'] }}">
                                <input type="hidden" name="domain_rating" value="{{ $offer['metrics']['dr'] }}">
                                <input type="hidden" name="monthly_traffic" value="{{ $offer['metrics']['traffic'] }}">
                                <input type="hidden" name="price" value="{{ $offer['price'] }}">
                                <button class="pb-heart" type="submit" aria-label="Add to wishlist">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19.5 12.6 12 20l-7.5-7.4a4.5 4.5 0 0 1 6.4-6.3L12 7.4l1.1-1.1a4.5 4.5 0 0 1 6.4 6.3Z"/></svg>
                                </button>
                            </form>
                        @else
                            <button class="pb-heart" type="button" disabled title="Demo data" aria-label="Add to wishlist">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19.5 12.6 12 20l-7.5-7.4a4.5 4.5 0 0 1 6.4-6.3L12 7.4l1.1-1.1a4.5 4.5 0 0 1 6.4 6.3Z"/></svg>
                            </button>
                        @endif
                    @else
                        <a class="pb-heart" href="{{ $loginHref }}" aria-label="Add to wishlist">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19.5 12.6 12 20l-7.5-7.4a4.5 4.5 0 0 1 6.4-6.3L12 7.4l1.1-1.1a4.5 4.5 0 0 1 6.4 6.3Z"/></svg>
                        </a>
                    @endauth
                </div>
                <div class="pb-price-row">
                    <p class="pb-price">{{ $offer['price_label'] }}</p>
                    <button type="button" class="pb-tab" data-service-tab="content_placement" aria-pressed="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="4" width="14" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M8 8h8M8 12h8" stroke="currentColor" stroke-width="1.6"/></svg>
                        Content placement
                    </button>
                    <button type="button" class="pb-tab" data-service-tab="link_insertion" aria-pressed="false">
                        <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.1.1l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M14 11a5 5 0 0 0-7.1-.1l-2 2a5 5 0 0 0 7.1 7.1l1.1-1.1" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                        Link insertion
                    </button>
                </div>
            </div>
            @include('customer.publish.partials.metrics')
            <section class="pb-stats" aria-label="Placement details">
                <article class="pb-stat"><strong>{{ $offer['backlinks_allowed'] ?? '—' }}</strong><span>Links allowed</span></article>
                <article class="pb-stat"><strong>{{ $offer['link_validity'] }}</strong><span>Link validity</span></article>
                <article class="pb-stat"><strong class="{{ strtolower($offer['link_type']) === 'dofollow' ? 'pb-dofollow' : '' }}">{{ $offer['link_type'] }}</strong><span>Link type</span></article>
                <article class="pb-stat"><strong>{{ $offer['article_length'] }}</strong><span>Article length</span></article>
            </section>
            @include('customer.publish.partials.rules')
            @include('customer.publish.partials.order-form')
        </div>
    </div>

    <div class="pb-panel">
        <div class="pb-tabs" role="tablist" aria-label="Publisher details">
            <button type="button" role="tab" id="tab-description" aria-controls="panel-description" aria-selected="true" tabindex="0">Description</button>
            <button type="button" role="tab" id="tab-reviews" aria-controls="panel-reviews" aria-selected="false" tabindex="-1">Reviews ({{ $offer['reviews']['count'] }})</button>
        </div>
        @include('customer.publish.partials.description')
        @include('customer.publish.partials.reviews')
    </div>

    @include('customer.publish.partials.similar')
</div>
<x-ui.country-browser />
@endsection

@push('scripts')
<script>
(function () {
    var galleryButtons = document.querySelectorAll('[data-gallery-thumb]');
    galleryButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var name = button.getAttribute('data-gallery-thumb');
            galleryButtons.forEach(function (item) {
                item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
            });
            document.querySelectorAll('[data-gallery-pane]').forEach(function (pane) {
                pane.hidden = pane.getAttribute('data-gallery-pane') !== name;
            });
        });
    });

    var serviceButtons = document.querySelectorAll('[data-service-tab]');
    serviceButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var name = button.getAttribute('data-service-tab');
            serviceButtons.forEach(function (item) {
                item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
            });
            document.querySelectorAll('[data-order-group]').forEach(function (group) {
                var show = group.getAttribute('data-order-group') === name;
                group.hidden = !show;
                if (show) {
                    var first = group.querySelector('input[type="radio"]');
                    if (first) {
                        first.checked = true;
                        first.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
        });
    });

    var order = document.querySelector('[data-order]');
    if (order) {
        var price = order.getAttribute('data-price') === '' ? null : Number(order.getAttribute('data-price'));
        var addonPrice = order.getAttribute('data-addon') === '' ? null : Number(order.getAttribute('data-addon'));
        var qtyInput = order.querySelector('[data-qty-input]');
        var qtyValue = order.querySelector('[data-qty-value]');
        var money = function (amount) {
            return '$' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        };
        var paint = function () {
            var qty = Math.min(10, Math.max(1, Number(qtyInput.value) || 1));
            qtyInput.value = String(qty);
            qtyValue.value = String(qty);
            var selected = order.querySelector('input[name="order_choice"]:checked');
            var topic = order.querySelector('input[name="topic_choice"]:checked');
            var addon = selected && selected.getAttribute('data-addon') === '1';
            if (selected) {
                order.querySelector('[data-product-key]').value = selected.getAttribute('data-product');
                order.querySelector('[data-order-type]').value = selected.value;
                var orderLabel = order.querySelector('[data-order-label]');
                if (orderLabel) orderLabel.value = selected.getAttribute('data-label') || '';
            }
            if (topic) {
                order.querySelector('[data-topic-value]').value = topic.value;
                order.querySelector('[data-topic-label]').textContent = 'Article topic – ' + topic.value;
            }
            order.querySelector('[data-line-label]').textContent = qty + '× Publish on ' + order.getAttribute('data-name') + ' – ' + order.getAttribute('data-link') + ' link';
            order.querySelector('[data-line-price]').textContent = price === null ? '—' : money(price * qty);
            order.querySelector('[data-type-label]').textContent = 'Order type – ' + (selected ? selected.getAttribute('data-label') : '');
            var typePrice = '—';
            var extra = 0;
            if (addon && addonPrice === null) {
                typePrice = '[PRICE]';
            } else if (addon && addonPrice !== null) {
                extra = addonPrice * qty;
                typePrice = '+ ' + money(extra);
            }
            order.querySelector('[data-type-price]').textContent = typePrice;
            order.querySelector('[data-subtotal]').textContent = price === null ? '—' : money(price * qty + extra);
        };
        order.querySelectorAll('input[type="radio"], [data-qty-input]').forEach(function (input) {
            input.addEventListener('change', paint);
            input.addEventListener('input', paint);
        });
        order.querySelectorAll('[data-qty]').forEach(function (button) {
            button.addEventListener('click', function () {
                qtyInput.value = String(Number(qtyInput.value || 1) + Number(button.getAttribute('data-qty')));
                paint();
            });
        });
        paint();
    }

    var tabs = Array.prototype.slice.call(document.querySelectorAll('[role="tab"]'));
    var selectTab = function (tab) {
        tabs.forEach(function (item) {
            var on = item === tab;
            item.setAttribute('aria-selected', on ? 'true' : 'false');
            item.tabIndex = on ? 0 : -1;
            var panel = document.getElementById(item.getAttribute('aria-controls'));
            if (panel) panel.hidden = !on;
        });
        tab.focus();
    };
    tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function () { selectTab(tab); });
        tab.addEventListener('keydown', function (event) {
            var next = null;
            if (event.key === 'ArrowRight') next = tabs[(index + 1) % tabs.length];
            if (event.key === 'ArrowLeft') next = tabs[(index - 1 + tabs.length) % tabs.length];
            if (next) {
                event.preventDefault();
                selectTab(next);
            }
        });
    });

    var dialog = document.getElementById('publisher-review-dialog');
    var opener = document.querySelector('[data-open-review]');
    if (dialog && opener) {
        opener.addEventListener('click', function () {
            dialog.showModal();
            var first = dialog.querySelector('input, textarea, button');
            if (first) first.focus();
        });
        dialog.querySelector('[data-close-review]').addEventListener('click', function () {
            dialog.close();
            opener.focus();
        });
        dialog.addEventListener('keydown', function (event) {
            if (event.key !== 'Tab') return;
            var items = dialog.querySelectorAll('input, textarea, button, select, a[href]');
            if (!items.length) return;
            var first = items[0];
            var last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    var row = document.querySelector('[data-similar-row]');
    document.querySelectorAll('[data-similar]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!row) return;
            var card = row.querySelector('.pb-similar-card');
            var width = card ? card.getBoundingClientRect().width + 12 : 240;
            row.scrollBy({ left: width * Number(button.getAttribute('data-similar')), behavior: 'smooth' });
        });
    });
})();
</script>
@endpush
