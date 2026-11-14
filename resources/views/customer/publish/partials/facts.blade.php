<section class="pb-facts" aria-label="Publisher facts">
    <article class="pb-fact">
        <p class="pb-fact-value">{{ $offer['primary_category'] !== '' ? $offer['primary_category'] : '—' }}</p>
        <p class="pb-fact-label">Primary category</p>
    </article>
    <article class="pb-fact">
        <p class="pb-fact-value">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M3 12h18M12 3c2.5 2.8 3.8 5.8 3.8 9S14.5 18.2 12 21c-2.5-2.8-3.8-5.8-3.8-9S9.5 5.8 12 3Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
            {{ $offer['language'] }}
        </p>
        <p class="pb-fact-label">Language</p>
    </article>
    <article class="pb-fact">
        <p class="pb-fact-value">
            @if ($offer['foreign_language_accepted'] === true)
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5 9.2 17 19 7" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                Accepted
            @elseif ($offer['foreign_language_accepted'] === false)
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7l10 10M17 7 7 17" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                Not accepted
            @else
                —
            @endif
        </p>
        <p class="pb-fact-label">Foreign-language anchors</p>
    </article>
    <article class="pb-fact pb-fact-wide">
        <p class="pb-fact-value">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M7 9h10M7 12h10M7 15h6" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
            @if ($offer['google_news'] === true)
                Included
            @elseif ($offer['google_news'] === false)
                Not included
            @else
                —
            @endif
        </p>
        <p class="pb-fact-label">Google News</p>
    </article>
</section>
