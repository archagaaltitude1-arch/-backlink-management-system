<div class="grid grid-cols-1 gap-3 rounded-xl border border-navy/5 bg-canvas/60 p-4 sm:grid-cols-3">
    <div>
        <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Customer & Length</p>
        <p class="mt-0.5 text-sm font-semibold text-navy">{{ $article['customer'] }}</p>
        <p class="mt-0.5 text-xs text-navy/60">{{ number_format($article['word_count']) }} words</p>
    </div>
    <div>
        <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Publisher Website</p>
        <p class="mt-0.5 text-sm font-semibold text-navy">{{ $article['website'] }}</p>
        <div class="mt-1 flex items-center gap-2 text-[11px]">
            <span class="rounded bg-white px-1.5 py-0.5 font-mono font-medium text-navy ring-1 ring-navy/10">DA {{ $article['da'] }}</span>
            <span class="rounded bg-brand/10 px-1.5 py-0.5 font-mono font-medium text-brand">DR {{ $article['dr'] }}</span>
        </div>
    </div>
    <div class="min-w-0">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Target URL & Anchor</p>
        <p class="mt-0.5">
            <span class="inline-block rounded bg-brand/10 px-2 py-0.5 text-xs font-semibold text-brand">"{{ $article['anchor_text'] }}"</span>
        </p>
        <a href="{{ $article['target_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex items-center gap-1 break-all text-xs text-navy/75 hover:text-brand">
            {{ $article['target_url'] }}
        </a>
    </div>
</div>

<div class="rounded-xl border border-navy/10 bg-white p-3.5 text-xs text-navy/75">
    <span class="font-semibold text-navy">Customer Requirements:</span>
    {{ $article['requirements'] ?: 'No specific customer requirements provided.' }}
</div>
