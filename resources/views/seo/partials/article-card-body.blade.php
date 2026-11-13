<div class="min-w-0 flex-1 space-y-4">
    <div class="flex flex-wrap items-center gap-2.5">
        <span class="rounded-lg bg-navy/5 px-2.5 py-1 font-mono text-xs font-bold text-navy">{{ $article['order_number'] }}</span>
        <span class="article-status-badge badge {{ $badgeClasses }}">{{ $article['status_label'] }}</span>
        <span class="badge bg-canvas text-navy/70">Staff Prepared</span>
        @if ($article['submitted_at'])
            <span class="text-xs text-navy/50">Sent: {{ $article['submitted_at'] }}</span>
        @endif
    </div>

    @if ($article['can_submit'])
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-wider text-navy/50" for="title-{{ $article['order_item_id'] }}">Blog title</label>
            <input
                id="title-{{ $article['order_item_id'] }}"
                name="title"
                type="text"
                value="{{ (string) old('_order_item_id') === (string) $article['order_item_id'] ? old('title', $article['title']) : $article['title'] }}"
                required
                class="mt-1 block w-full rounded-xl border border-navy/20 bg-white px-3 py-2 text-sm font-semibold text-navy focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
            >
        </div>
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-wider text-navy/50" for="body-{{ $article['order_item_id'] }}">Blog</label>
            <textarea
                id="body-{{ $article['order_item_id'] }}"
                name="body"
                rows="6"
                required
                placeholder="Write the blog. Customers cannot submit this content."
                class="article-body-input mt-1 block w-full rounded-xl border border-navy/20 bg-white px-3 py-2 text-sm leading-6 text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
            >{{ (string) old('_order_item_id') === (string) $article['order_item_id'] ? old('body', $article['body']) : $article['body'] }}</textarea>
        </div>
    @else
        <div>
            <h2 class="text-base font-bold leading-snug text-navy sm:text-lg">{{ $article['title'] }}</h2>
            @if ($article['body'] !== '')
                <p class="mt-1.5 text-xs leading-relaxed text-navy/70">{{ \Illuminate\Support\Str::limit($article['body'], 220) }}</p>
            @endif
        </div>
    @endif

    @include('seo.partials.article-specs', ['article' => $article])

    @if ($article['feedback'])
        <div class="rounded-xl bg-amber-50 p-3.5 text-xs text-amber-900 ring-1 ring-amber-200">
            <span class="font-semibold">Admin feedback:</span>
            {{ $article['feedback'] }}
        </div>
    @endif
</div>
