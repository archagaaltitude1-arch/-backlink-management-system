<section class="pb-rules" aria-labelledby="rules-title">
    <h2 id="rules-title">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="4" width="12" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M9 4.5h6v3H9zM9 11h6M9 14h6" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
        Publishing rules
    </h2>
    <ul>
        @foreach ($offer['rules'] as $rule)
            <li>{{ $rule }}</li>
        @endforeach
    </ul>
    @if ($offer['sample_links'] !== [])
        <p class="pb-samples">Sample articles:
            @foreach ($offer['sample_links'] as $link)
                <a href="{{ $link }}" rel="nofollow noopener" target="_blank">Sample {{ $loop->iteration }}</a>
            @endforeach
        </p>
    @endif
</section>
<section class="pb-restricted" aria-labelledby="restricted-title">
    <h2 id="restricted-title">Restricted topics</h2>
    <div class="pb-restricted-grid">
        <article>
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="3" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="8" cy="8" r="1.2" fill="currentColor"/><circle cx="16" cy="8" r="1.2" fill="currentColor"/><circle cx="12" cy="12" r="1.2" fill="currentColor"/><circle cx="8" cy="16" r="1.2" fill="currentColor"/><circle cx="16" cy="16" r="1.2" fill="currentColor"/></svg>
            <h3>Casino &amp; gambling</h3>
            <span class="pb-pill {{ ($offer['restricted']['casino_gambling'] ?? '') === 'accepted' ? 'pb-ok' : 'pb-no' }}">{{ ($offer['restricted']['casino_gambling'] ?? '') === 'accepted' ? 'Accepted' : 'Not accepted' }}</span>
        </article>
        <article>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 8v8M10 10.2c.4-.8 1.1-1.2 2-1.2 1.2 0 2 .6 2 1.6S13.2 12 12 12s-2 .6-2 1.6 1 1.6 2.2 1.6c.9 0 1.6-.4 2-1.2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            <h3>Crypto</h3>
            <span class="pb-pill {{ ($offer['restricted']['crypto'] ?? '') === 'accepted' ? 'pb-ok' : 'pb-no' }}">{{ ($offer['restricted']['crypto'] ?? '') === 'accepted' ? 'Accepted' : 'Not accepted' }}</span>
        </article>
        <article>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20c4-3 6-6.2 6-9.2C18 7.4 15.6 5 12.4 5 10 5 8 6.4 8 8.6c0 .5.1 1 .3 1.4C6.4 10.6 5 12.2 5 14.2 5 16.8 7.2 19 10 19c.7 0 1.4-.2 2-.6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            <h3>CBD</h3>
            <span class="pb-pill {{ ($offer['restricted']['cbd'] ?? '') === 'accepted' ? 'pb-ok' : 'pb-no' }}">{{ ($offer['restricted']['cbd'] ?? '') === 'accepted' ? 'Accepted' : 'Not accepted' }}</span>
        </article>
    </div>
</section>
