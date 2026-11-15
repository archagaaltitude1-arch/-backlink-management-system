@php
    $prices = [];

    foreach (config('backlink_services.overview.compare_rows', []) as $row) {
        if (($row['label'] ?? '') === 'Price from') {
            $prices = $row;
        }
    }

    $services = [
        ['guest-post', 'article', 'bg-orange-50 text-brand-dark'],
        ['niche-edit', 'edit', 'bg-navy text-white'],
        ['contextual-link', 'link', 'bg-canvas text-navy'],
        ['text-link', 'type', 'bg-white text-navy ring-1 ring-navy/10'],
    ];
    $menu = collect(config('backlink_services.menu', []))->keyBy('slug');
@endphp

<section aria-labelledby="guest-posting-title" class="panel space-y-4 p-4 sm:space-y-5 sm:p-6 lg:p-7">
    <a href="{{ route('services.index') }}" class="flex items-center gap-3 rounded-xl bg-orange-50 px-4 py-3 text-sm font-semibold text-navy hover:bg-orange-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
        <svg class="size-5 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 11v5m0-8h.01" /></svg>
        <span>Compare guest posts, niche edits, contextual links and text links before you order.</span>
    </a>

    <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 id="guest-posting-title" class="flex items-center gap-2 text-xl font-semibold text-navy">
            <x-ui.icon name="article" class="size-6 shrink-0 text-brand" />
            Link building services
        </h2>
        <a href="{{ route('publishers.index') }}" class="btn-primary min-h-11 w-full sm:w-auto">Search sites</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($services as [$slug, $icon, $colors])
            @php
                $item = $menu->get($slug);
                $name = (string) ($item['name'] ?? $slug);
                $line = (string) ($item['line'] ?? '');
                $price = (string) ($prices[$slug] ?? '');
            @endphp
            <a href="{{ route('services.show', $slug) }}" class="{{ $colors }} flex min-w-0 items-center justify-between gap-3 rounded-xl p-4 transition hover:ring-1 hover:ring-brand focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand sm:min-h-36 sm:p-5">
                <div class="min-w-0">
                    <h3 class="text-base font-bold">{{ $name }}</h3>
                    <p class="mt-2 text-sm leading-5">{{ $line }}.@if ($price !== '') From {{ $price }}.@endif</p>
                </div>
                <x-ui.icon :name="$icon" class="size-7 shrink-0 lg:size-9" />
            </a>
        @endforeach
    </div>
</section>
