@php
    $part = $part ?? 'both';
    $domainKey = mb_strtolower((string) ($site['domain'] ?? ''));
    $wishlist = session('wishlist', []);
    $onWishlist = $domainKey !== '' && is_array($wishlist) && array_key_exists($domainKey, $wishlist);
@endphp
<div class="flex flex-wrap items-center gap-2">
    @if ($part !== 'buy' && $domainKey !== '')
        <form method="POST" action="{{ $onWishlist ? route('wishlist.destroy') : route('wishlist.store') }}">
            @csrf
            @if ($onWishlist)
                @method('DELETE')
            @endif
            <input type="hidden" name="domain" value="{{ $site['domain'] }}">
            <button type="submit" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#E3E5E8] {{ $onWishlist ? 'text-[#FF4917]' : 'text-navy' }} hover:bg-[#FFF1EC] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]" aria-label="{{ $onWishlist ? 'Remove '.$site['domain'].' from wishlist' : 'Add '.$site['domain'].' to wishlist' }}" @if ($onWishlist) aria-pressed="true" @endif>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="{{ $onWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19.5 12.6 12 20l-7.5-7.4a4.5 4.5 0 0 1 6.4-6.3L12 7.4l1.1-1.1a4.5 4.5 0 0 1 6.4 6.3Z"/></svg>
            </button>
        </form>
    @endif
    @if ($part !== 'save')
        <a class="nd-publish" href="{{ route('publish.show', ($demo || empty($site['id'])) ? $site['domain'] : $site['id']) }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M6 6h15l-1.5 9h-12L6 6Z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.2" fill="currentColor"/><circle cx="18" cy="20" r="1.2" fill="currentColor"/></svg>
            Publish
        </a>
    @endif
</div>
