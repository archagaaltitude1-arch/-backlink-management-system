@php
    $services = [
        ['Buy guest post sites', 'Browse publishers for your next guest post.', [], 'bg-orange-50 text-orange-900', 'M2 4h3l3 11h11l3-8H6 M9 20h.01m9 0h.01'],
        ['Premium guest post sites', 'Find publishers with a domain rating of 60 or higher.', ['min_dr' => 60, 'sort' => 'dr'], 'bg-indigo-50 text-indigo-900', 'm12 2 3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z'],
        ['Sports guest posts', 'Browse sites listed under the Sports niche.', ['niche' => 'Sports'], 'bg-red-50 text-red-900', 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z m0-14 4 3-2 5h-4l-2-5z m0 0V2m4 9 5-2m-7 7 3 5m-7-5-3 5m1-10-5-2'],
        ['Health guest posts', 'Find publishers in health and wellness.', ['niche' => 'Health'], 'bg-teal-50 text-teal-900', 'M6 4h12v16H6z M12 8v8m-4-4h8'],
    ];
@endphp

<section aria-labelledby="guest-posting-title" class="space-y-5 border border-slate-200 p-4 sm:p-6 lg:p-7">
    <a href="{{ route('customer.services') }}" class="flex items-center gap-3 rounded-sm bg-sky-50 px-4 py-3 text-sm text-sky-900 hover:bg-sky-100">
        <svg class="size-5 shrink-0 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 11v5m0-8h.01" /></svg>
        <span>Compare backlink services before placing an order.</span>
    </a>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 id="guest-posting-title" class="flex items-center gap-2 text-xl font-semibold text-black">
            <svg class="size-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h16v15H5z M5 19H3V7h2 M8 7h4v4H8z M15 8h3m-3 3h3m-10 4h10" /></svg>
            Guest posting service
        </h2>
        <a href="{{ route('customer.websites').'#search' }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Search sites</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($services as [$title, $description, $filters, $colors, $icon])
            <a href="{{ route('customer.websites', $filters) }}" class="{{ $colors }} flex min-h-36 items-center justify-between gap-4 rounded-xl p-5 transition hover:ring-1 hover:ring-current focus-visible:outline-2 focus-visible:outline-offset-2">
                <div>
                    <h3 class="text-base font-bold">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-5">{{ $description }}</p>
                </div>
                <svg class="size-9 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}" /></svg>
            </a>
        @endforeach
    </div>
</section>
