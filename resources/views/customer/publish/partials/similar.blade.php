<section class="pb-similar" aria-labelledby="similar-title">
    <div class="pb-similar-head">
        <h2 id="similar-title">Similar publishers</h2>
        <div>
            <button type="button" data-similar="-1" aria-label="Previous publishers">‹</button>
            <button type="button" data-similar="1" aria-label="Next publishers">›</button>
        </div>
    </div>
    <div class="pb-similar-row" data-similar-row>
        @foreach ($offer['similar'] as $card)
            <article class="pb-similar-card">
                <div class="pb-similar-mark" aria-hidden="true">{{ strtoupper(mb_substr($card['domain'], 0, 1)) }}</div>
                <p class="pb-pill">{{ $card['category'] }}</p>
                <h3>Publish a Guest Post on {{ $card['domain'] }}</h3>
                @if (($card['reviews'] ?? 0) > 0 && $card['rating'] !== null)
                    <p aria-label="Average rating {{ $card['rating'] }} out of 5">{{ str_repeat('★', (int) round($card['rating'])) }}</p>
                @endif
                <p class="pb-similar-price">{{ $card['price_label'] }}</p>
                @if (\Illuminate\Support\Facades\Route::has('publish.show'))
                    <a class="pb-similar-btn" href="{{ route('publish.show', $card['key']) }}">Publish</a>
                @endif
            </article>
        @endforeach
    </div>
</section>
