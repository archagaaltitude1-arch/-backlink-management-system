<div class="flex items-center justify-between gap-3">
    <h3 class="text-xs font-bold uppercase tracking-wider text-navy">Send to Admin Approval</h3>
    <button
        type="button"
        class="preview-article-btn cursor-pointer text-xs font-semibold text-brand hover:underline"
        data-title="{{ $article['title'] }}"
        data-order="{{ $article['order_number'] }}"
        data-anchor="{{ $article['anchor_text'] }}"
        data-target="{{ $article['target_url'] }}"
        data-website="{{ $article['website'] }}"
        data-body='@json($article['paragraphs'])'
    >
        Preview Content &rarr;
    </button>
</div>

@if ($article['can_submit'])
    <div>
        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-navy/55" for="notes-{{ $article['order_item_id'] }}">
            Notes for admin
        </label>
        <textarea
            id="notes-{{ $article['order_item_id'] }}"
            name="staff_notes"
            rows="3"
            placeholder="Add context for the admin reviewer..."
            class="block w-full rounded-xl border border-navy/20 bg-white px-3 py-2 text-xs text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
        >{{ (string) old('_order_item_id') === (string) $article['order_item_id'] ? old('staff_notes', $article['staff_notes']) : $article['staff_notes'] }}</textarea>
    </div>
    <button type="submit" class="btn-primary w-full cursor-pointer py-2 text-xs">
        Send to Admin Approval
    </button>
    <p class="text-[11px] leading-5 text-navy/55">You can publish this blog only after an admin approves it.</p>
@elseif ($article['status'] === 'pending_admin')
    <p class="rounded-xl bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-800 ring-1 ring-amber-200">
        Waiting for admin approval. Publishing stays locked until an admin approves this blog.
    </p>
@elseif ($article['can_publish'])
    <p class="text-xs leading-5 text-navy/70">Admin approved this blog. Only you, the assigned SEO staff, can publish it.</p>
    <form method="POST" action="{{ route('seo.articles.publish', $article['article_id']) }}" class="space-y-2">
        @csrf
        <input type="hidden" name="_article_id" value="{{ $article['article_id'] }}">
        <label class="block text-[11px] font-semibold uppercase tracking-wider text-navy/55" for="published-url-{{ $article['article_id'] }}">
            Published URL
        </label>
        <input
            id="published-url-{{ $article['article_id'] }}"
            name="published_url"
            type="url"
            required
            value="{{ (string) old('_article_id') === (string) $article['article_id'] ? old('published_url') : '' }}"
            placeholder="https://publisher.example/your-blog"
            class="block w-full rounded-xl border border-navy/20 bg-white px-3 py-2 text-xs text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
        >
        <button type="submit" class="btn-primary w-full cursor-pointer py-2 text-xs">
            Publish Blog
        </button>
    </form>
@elseif ($article['status'] === 'published')
    <p class="text-xs leading-5 text-navy/70">This blog is published.</p>
    @if ($article['published_url'])
        <a href="{{ $article['published_url'] }}" target="_blank" rel="noopener noreferrer" class="break-all text-xs font-semibold text-brand hover:underline">
            {{ $article['published_url'] }}
        </a>
    @endif
@else
    <p class="text-xs leading-5 text-navy/70">Update the blog and send it to admin again. Publishing stays locked until an admin approves it.</p>
@endif
