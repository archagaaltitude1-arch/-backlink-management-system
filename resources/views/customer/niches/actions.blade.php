@php $part = $part ?? 'both'; @endphp
<div class="flex flex-wrap items-center gap-2">
    @if ($demo || empty($site['id']))
        @if ($part !== 'buy')
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#E3E5E8] text-[#B8C2CC]" disabled title="Demo data" aria-label="Save {{ $site['domain'] }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19.5 12.6 12 20l-7.5-7.4a4.5 4.5 0 0 1 6.4-6.3L12 7.4l1.1-1.1a4.5 4.5 0 0 1 6.4 6.3Z"/></svg>
            </button>
        @endif
        @if ($part !== 'save')
            <a class="nd-publish" href="{{ route('publish.show', $site['domain']) }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M6 6h15l-1.5 9h-12L6 6Z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.2" fill="currentColor"/><circle cx="18" cy="20" r="1.2" fill="currentColor"/></svg>
                Publish
            </a>
        @endif
    @else
        @if ($part !== 'buy' && $canSave)
            <form method="POST" action="{{ route('customer.saved-websites.store') }}">
                @csrf
                <input type="hidden" name="website_name" value="{{ $site['domain'] }}">
                <input type="hidden" name="domain" value="{{ $site['domain'] }}">
                <input type="hidden" name="url" value="{{ 'https://'.$site['domain'] }}">
                <input type="hidden" name="domain_rating" value="{{ $site['dr'] }}">
                <input type="hidden" name="monthly_traffic" value="{{ $site['traffic'] }}">
                <input type="hidden" name="price" value="{{ $site['price'] }}">
                <button type="submit" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#E3E5E8] text-navy hover:bg-[#FFF1EC] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]" aria-label="Save {{ $site['domain'] }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19.5 12.6 12 20l-7.5-7.4a4.5 4.5 0 0 1 6.4-6.3L12 7.4l1.1-1.1a4.5 4.5 0 0 1 6.4 6.3Z"/></svg>
                </button>
            </form>
        @endif
        @if ($part !== 'save')
            <a class="nd-publish" href="{{ route('publish.show', $site['id']) }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M6 6h15l-1.5 9h-12L6 6Z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.2" fill="currentColor"/><circle cx="18" cy="20" r="1.2" fill="currentColor"/></svg>
                Publish
            </a>
        @endif
    @endif
</div>
