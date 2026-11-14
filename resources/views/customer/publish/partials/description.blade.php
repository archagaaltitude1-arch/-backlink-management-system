@php $metrics = $offer['metrics']; @endphp
<div role="tabpanel" id="panel-description" aria-labelledby="tab-description">
    <p class="pb-lead">{{ $offer['lead'] }}</p>
    <h3>Website metrics</h3>
    <dl class="pb-defs">
        <div><dt>Domain Authority (DA)</dt><dd>{{ $num($metrics['da']) }}</dd></div>
        <div><dt>Page Authority (PA)</dt><dd>{{ $num($metrics['pa']) }}</dd></div>
        <div><dt>Domain Rating (DR)</dt><dd>{{ $num($metrics['dr']) }}</dd></div>
        <div><dt>Trust Flow (TF)</dt><dd>{{ $num($metrics['tf']) }}</dd></div>
        <div><dt>Citation Flow (CF)</dt><dd>{{ $num($metrics['cf']) }}</dd></div>
        <div><dt>Link type</dt><dd>{{ $offer['link_type'] }}</dd></div>
        <div><dt>Link validity</dt><dd>{{ $offer['link_validity'] }}</dd></div>
        <div><dt>Links allowed</dt><dd>{{ $offer['backlinks_allowed'] ?? '—' }}</dd></div>
        <div><dt>Foreign-language anchors</dt><dd>{{ $offer['foreign_language_accepted'] === true ? 'Accepted' : ($offer['foreign_language_accepted'] === false ? 'Not accepted' : '—') }}</dd></div>
    </dl>
    <h3>Categories</h3>
    <div class="pb-cats">
        @forelse ($offer['categories'] as $category)
            @if (\Illuminate\Support\Facades\Route::has('niches.show'))
                <a href="{{ route('niches.show', \Illuminate\Support\Str::slug($category)) }}">{{ $category }}</a>
            @else
                <span>{{ $category }}</span>
            @endif
        @empty
            <span>—</span>
        @endforelse
    </div>
    <h3>Submitting your own article</h3>
    <p>Send an original, well-written article that fits {{ $offer['primary_category'] !== '' ? $offer['primary_category'] : 'the site category' }}, with up to {{ $offer['backlinks_allowed'] ?? '—' }} links. Our editors check spelling, originality and relevance before it goes to the publisher.</p>
    <h3>Article writing add-on</h3>
    <p>Prefer us to write it? Our writers research your topic and create an original, SEO-friendly article with your anchor text and target URL included.</p>
    <h3>How this helps your website</h3>
    <p>A relevant link from an established website sends trust signals to search engines and can bring new visitors to your pages. Placements on sites with strong authority and real traffic tend to give the best long-term value.</p>
    <h3>Delivery</h3>
    <p>Usually live within {{ $offer['tat_days'] ?? '—' }} days. Have a question? <a href="{{ url('/#faq') }}">Read our FAQ</a>.</p>
</div>
