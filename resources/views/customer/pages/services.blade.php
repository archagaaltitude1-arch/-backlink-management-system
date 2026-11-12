@extends('layouts.public')

@section('title', 'Services')

@section('meta_description', 'Guest posts, niche edits, and link insertions. Compare who writes the content, the link type, turnaround, and the starting price.')

@section('content')
    @php
        $money = function ($value): string {
            return is_numeric($value) ? '$'.number_format((float) $value, 0) : '';
        };
    @endphp

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">
            <x-ui.section-heading
                eyebrow="Services"
                title="Choose how the link gets placed."
                subtitle="Each service starts from a price and a credit cost. You provide the target URL, the anchor text, and either an article topic or your own article."
                level="h1"
            />
        </div>
    </section>

    <section class="bg-canvas" aria-labelledby="service-list-heading">
        <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">
            <h2 id="service-list-heading" class="sr-only">Active services</h2>
            @if ($services->isEmpty())
                <x-ui.empty-state
                    title="No services are listed yet"
                    message="Active services will appear here with what you need to provide, the starting price, and a link into the marketplace."
                />
            @else
                <div class="grid gap-4 lg:grid-cols-3">
                    @foreach ($services as $card)
                        @php
                            $service = $card['service'];
                            $profile = $card['profile'];
                        @endphp
                        <article id="{{ $card['slug'] }}" class="panel flex h-full scroll-mt-24 flex-col p-6">
                            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-canvas text-brand" aria-hidden="true">
                                @if ($profile['icon'] === 'guest')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 3h9l3 3v15H6z" />
                                        <path d="M15 3v3h3M8 12h8M8 16h5" />
                                    </svg>
                                @elseif ($profile['icon'] === 'niche')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 20h4l10-10-4-4L4 16z" />
                                        <path d="m13 7 4 4" />
                                    </svg>
                                @elseif ($profile['icon'] === 'insertion')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 13a5 5 0 0 0 7.1.1l2.1-2.1a5 5 0 0 0-7.1-7.1L10.8 5" />
                                        <path d="M14 11a5 5 0 0 0-7.1-.1L4.8 13a5 5 0 0 0 7.1 7.1L13.2 19" />
                                    </svg>
                                @else
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="8" />
                                        <path d="M12 8v8M8 12h8" />
                                    </svg>
                                @endif
                            </div>
                            <h3 class="mt-4 font-display text-xl font-bold text-navy">{{ $service->service_name }}</h3>
                            @if (filled($service->description))
                                <p class="mt-3 text-sm leading-6 text-navy/70">{{ $service->description }}</p>
                            @endif
                            <h4 class="mt-5 text-xs font-semibold tracking-wider text-navy/50 uppercase">You provide</h4>
                            <ul class="mt-2 space-y-1 text-sm text-navy/80">
                                @foreach ($profile['provides'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            @if (is_numeric($service->base_price))
                                <p class="mt-5 font-display text-lg font-bold text-brand">From {{ $money($service->base_price) }}</p>
                            @endif
                            <p class="mt-1 text-sm text-navy/70">
                                @if (is_numeric($service->credit_cost))
                                    {{ rtrim(rtrim(number_format((float) $service->credit_cost, 2), '0'), '.') }} credits
                                @else
                                    Credit cost on request
                                @endif
                            </p>
                            <div class="mt-6">
                                <x-ui.button variant="navy" class="w-full" :href="url('/marketplace?service='.$card['slug'])">Find websites</x-ui.button>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-14">
                    <x-ui.section-heading
                        eyebrow="Compare"
                        title="Which service fits the campaign"
                        subtitle="Content, link type, and turnaround next to the starting price."
                    />
                    <div class="panel mt-8 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <caption class="sr-only">Service comparison</caption>
                            <thead class="border-b border-navy/10 text-xs tracking-wider text-navy/50 uppercase">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-semibold">Service</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Content by</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Link type</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Turnaround</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Price from</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($services as $card)
                                    <tr class="border-b border-navy/5 last:border-0">
                                        <th scope="row" class="px-4 py-4 font-display font-bold text-navy">
                                            <a href="#{{ $card['slug'] }}" class="hover:text-brand focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">{{ $card['service']->service_name }}</a>
                                        </th>
                                        <td class="px-4 py-4 text-navy/80">{{ $card['profile']['content_by'] }}</td>
                                        <td class="px-4 py-4 text-navy/80">{{ $card['profile']['link_type'] }}</td>
                                        <td class="px-4 py-4 text-navy/80">{{ $card['profile']['turnaround'] }}</td>
                                        <td class="px-4 py-4 font-semibold">{{ $money($card['service']->base_price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
