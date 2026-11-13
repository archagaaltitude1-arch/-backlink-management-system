@extends('layouts.public')

@section('document_title', $tool['label'].' – BacklinkPro')

@section('meta_description', $tool['summary'])

@section('content')
    @php
        $compact = fn ($value) => \App\Support\DemoNicheData::compact(is_numeric($value) ? (int) $value : null);
        $spamBand = function ($spam) {
            if (! is_numeric($spam)) {
                return 'Not stored';
            }

            $spam = (int) $spam;

            if ($spam <= 30) {
                return 'Low';
            }

            if ($spam <= 60) {
                return 'Medium';
            }

            return 'High';
        };
        $nicheHref = function (?array $row) {
            $name = $row['niches'][0] ?? null;

            if (! is_string($name) || $name === '' || $name === 'Publisher') {
                return null;
            }

            return route('niches.show', \Illuminate\Support\Str::slug($name));
        };
    @endphp
    <section class="bg-[#F2F2F2]">
        <div class="mx-auto max-w-[1240px] px-6 py-14 lg:py-16">
            <nav aria-label="Breadcrumb" class="text-sm font-bold text-[#4A5560]">
                <a href="{{ url('/') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Home</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ route('tools.index') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Tools</a>
                <span aria-hidden="true"> / </span>
                <span class="text-navy">{{ $tool['label'] }}</span>
            </nav>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
                <div>
                    <p class="text-[13px] font-extrabold tracking-wide text-[#C0340D] uppercase">SEO tools</p>
                    <h1 class="mt-2 font-display text-[30px] leading-tight font-extrabold text-navy lg:text-[44px]">{{ $tool['label'] }}</h1>
                    <p class="mt-4 max-w-[760px] text-[17px] leading-8 text-[#4A5560]">{{ $tool['about'] }}</p>
                    @if ($demo && $tool['kind'] !== 'anchor')
                        <p class="mt-4 rounded-xl bg-[#FFF1EC] px-4 py-3 text-[15px] leading-6 text-[#C0340D]">Sample data for preview. These figures belong to fictional .example publishers.</p>
                    @endif
                </div>
                <aside class="rounded-[20px] border border-[#E3E5E8] bg-white p-5">
                    <h2 class="font-display text-[18px] font-extrabold text-navy">What this tool shows</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($tool['checks'] as $check)
                            <li class="text-[15px] leading-6 text-[#4A5560]">{{ $check }}</li>
                        @endforeach
                    </ul>
                </aside>
            </div>

            <div class="mt-8 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
                @if ($tool['kind'] === 'anchor')
                    <form method="GET" action="{{ route('tools.show', $tool['slug']) }}" class="grid gap-4">
                        <div>
                            <label for="anchors" class="text-[15px] font-bold text-navy">Anchors, one per line</label>
                            <textarea id="anchors" name="anchors" rows="6" class="mt-2 w-full rounded-xl border border-[#E3E5E8] px-4 py-3 text-[15px] text-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $anchors }}</textarea>
                        </div>
                        <button type="submit" class="inline-flex min-h-11 w-fit items-center justify-center rounded-xl bg-[#D63A10] px-6 text-[15px] font-bold text-white hover:bg-[#FF4917] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Analyze anchors</button>
                    </form>
                @elseif ($tool['kind'] === 'competitor')
                    <form method="GET" action="{{ route('tools.show', $tool['slug']) }}" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end">
                        <div>
                            <label for="domain" class="text-[15px] font-bold text-navy">First website</label>
                            <input id="domain" name="domain" type="text" value="{{ $domain }}" maxlength="253" autocomplete="off" placeholder="publisher-domain.example" class="mt-2 h-12 w-full rounded-xl border border-[#E3E5E8] px-4 text-[15px] text-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                        </div>
                        <div>
                            <label for="compare" class="text-[15px] font-bold text-navy">Second website</label>
                            <input id="compare" name="compare" type="text" value="{{ $compare }}" maxlength="253" autocomplete="off" placeholder="another-domain.example" class="mt-2 h-12 w-full rounded-xl border border-[#E3E5E8] px-4 text-[15px] text-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                        </div>
                        <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#D63A10] px-6 text-[15px] font-bold text-white hover:bg-[#FF4917] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Compare</button>
                    </form>
                @else
                    <form method="GET" action="{{ route('tools.show', $tool['slug']) }}" class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                        <div>
                            <label for="domain" class="text-[15px] font-bold text-navy">Publisher domain</label>
                            <input id="domain" name="domain" type="text" value="{{ $domain }}" maxlength="253" autocomplete="off" placeholder="publisher-domain.example" class="mt-2 h-12 w-full rounded-xl border border-[#E3E5E8] px-4 text-[15px] text-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                        </div>
                        <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#D63A10] px-6 text-[15px] font-bold text-white hover:bg-[#FF4917] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Check website</button>
                    </form>
                @endif

                @if ($tool['kind'] !== 'anchor' && $suggestions !== [])
                    <div class="mt-5">
                        <p class="text-[13px] font-bold tracking-wide text-[#4A5560] uppercase">Listed publishers</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($suggestions as $suggestion)
                                <a href="{{ route('tools.show', ['slug' => $tool['slug'], 'domain' => $suggestion['domain'], 'compare' => $tool['kind'] === 'competitor' ? $compare : null]) }}" class="inline-flex min-h-11 items-center rounded-full border border-[#E3E5E8] px-4 text-[14px] font-bold text-navy hover:bg-[#FFF1EC] hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $suggestion['domain'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            @if ($tool['kind'] === 'anchor' && $anchorReport)
                <div class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
                    <h2 class="font-display text-[22px] font-extrabold text-navy">Anchor profile</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <p class="rounded-xl bg-[#F2F2F2] p-4"><span class="block text-[13px] font-bold text-[#4A5560] uppercase">Total</span><span class="mt-1 block font-display text-[32px] font-extrabold text-navy">{{ $anchorReport['total'] }}</span></p>
                        <p class="rounded-xl bg-[#F2F2F2] p-4"><span class="block text-[13px] font-bold text-[#4A5560] uppercase">Unique</span><span class="mt-1 block font-display text-[32px] font-extrabold text-navy">{{ $anchorReport['unique'] }}</span></p>
                        <p class="rounded-xl bg-[#F2F2F2] p-4"><span class="block text-[13px] font-bold text-[#4A5560] uppercase">Largest share</span><span class="mt-1 block font-display text-[32px] font-extrabold text-navy">{{ $anchorReport['topShare'] }}%</span></p>
                    </div>
                    <p class="mt-4 text-[15px] leading-7 text-[#4A5560]">{{ $anchorReport['note'] }}</p>
                    @if ($anchorReport['rows'] !== [])
                        <ul class="mt-5 space-y-3">
                            @foreach ($anchorReport['rows'] as $row)
                                <li>
                                    <div class="flex items-baseline justify-between gap-3 text-[15px] font-bold text-navy">
                                        <span>{{ $row['label'] }}</span>
                                        <span>{{ $row['count'] }} · {{ $row['share'] }}%</span>
                                    </div>
                                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-[#F2F2F2]" role="meter" aria-label="{{ $row['label'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $row['share'] }}">
                                        <div class="h-full rounded-full bg-[#FF4917]" style="width: {{ $row['share'] }}%"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @elseif ($tool['kind'] === 'competitor' && $checked)
                <div class="mt-6 overflow-x-auto rounded-[20px] border border-[#E3E5E8] bg-white">
                    <table class="w-full min-w-[640px] text-left text-[15px]">
                        <caption class="px-5 py-4 text-left font-display text-[22px] font-extrabold text-navy">Side by side</caption>
                        <thead class="bg-[#F7F8F9] text-[13px] tracking-wide text-[#4A5560] uppercase">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-bold">Metric</th>
                                <th scope="col" class="px-5 py-3 font-bold">{{ $domain !== '' ? $domain : 'First website' }}</th>
                                <th scope="col" class="px-5 py-3 font-bold">{{ $compare !== '' ? $compare : 'Second website' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (['backlinks' => 'Backlinks', 'referring_domains' => 'Referring domains', 'dr' => 'Domain Rating', 'da' => 'Domain Authority', 'traffic' => 'Monthly traffic'] as $key => $label)
                                <tr class="border-t border-[#E3E5E8]">
                                    <th scope="row" class="px-5 py-3 font-bold text-navy">{{ $label }}</th>
                                    <td class="px-5 py-3 text-navy">{{ $site ? ($key === 'traffic' || $key === 'backlinks' || $key === 'referring_domains' ? $compact($site[$key] ?? null) : ($site[$key] ?? '')) : 'Not listed' }}</td>
                                    <td class="px-5 py-3 text-navy">{{ $other ? ($key === 'traffic' || $key === 'backlinks' || $key === 'referring_domains' ? $compact($other[$key] ?? null) : ($other[$key] ?? '')) : 'Not listed' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif ($checked && $tool['kind'] !== 'anchor')
                @if (! $site)
                    <p class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white px-5 py-4 text-[15px] leading-7 text-[#4A5560]" role="status">That website is not in the publisher list, so this checker has no stored metrics for it.</p>
                @else
                    <div class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="font-display text-[26px] font-extrabold text-navy">{{ $site['domain'] }}</h2>
                                <p class="mt-1 text-[15px] text-[#4A5560]">{{ implode(' · ', $site['niches'] ?? []) }}@if (filled($site['language'] ?? null)) · {{ $site['language'] }}@endif</p>
                            </div>
                            @if ($href = $nicheHref($site))
                                <a href="{{ $href }}" class="inline-flex min-h-11 items-center text-[15px] font-bold text-[#C0340D] hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">View this niche</a>
                            @endif
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                            @if ($tool['kind'] === 'authority')
                                @include('customer.tools.meter', ['score' => $site['as'], 'label' => 'Authority score'])
                            @elseif ($tool['kind'] === 'dr')
                                @include('customer.tools.meter', ['score' => $site['dr'], 'label' => 'Domain Rating'])
                            @elseif ($tool['kind'] === 'da')
                                @include('customer.tools.meter', ['score' => $site['da'], 'label' => 'Domain Authority'])
                            @elseif ($tool['kind'] === 'spam')
                                @include('customer.tools.meter', ['score' => $site['spam'], 'label' => 'Spam score'])
                            @elseif ($tool['kind'] === 'backlinks')
                                <p class="rounded-xl bg-[#F2F2F2] p-4"><span class="block text-[13px] font-bold text-[#4A5560] uppercase">Backlinks</span><span class="mt-1 block font-display text-[40px] leading-none font-extrabold text-navy">{{ $compact($site['backlinks'] ?? null) }}</span></p>
                                <p class="rounded-xl bg-[#F2F2F2] p-4"><span class="block text-[13px] font-bold text-[#4A5560] uppercase">Referring domains</span><span class="mt-1 block font-display text-[40px] leading-none font-extrabold text-navy">{{ $compact($site['referring_domains'] ?? null) }}</span></p>
                            @elseif ($tool['kind'] === 'traffic')
                                <p class="rounded-xl bg-[#F2F2F2] p-4"><span class="block text-[13px] font-bold text-[#4A5560] uppercase">Ahrefs traffic</span><span class="mt-1 block font-display text-[40px] leading-none font-extrabold text-navy">{{ $compact($site['traffic'] ?? null) }}</span></p>
                                <p class="rounded-xl bg-[#F2F2F2] p-4"><span class="block text-[13px] font-bold text-[#4A5560] uppercase">Semrush traffic</span><span class="mt-1 block font-display text-[40px] leading-none font-extrabold text-navy">{{ $compact($site['semrush_traffic'] ?? null) }}</span></p>
                            @endif
                        </div>

                        @if ($tool['kind'] === 'spam')
                            <p class="mt-4 text-[17px] font-bold text-navy">Band: {{ $spamBand($site['spam'] ?? null) }}</p>
                        @endif

                        @if (in_array($tool['kind'], ['authority', 'dr', 'da', 'spam'], true))
                            <dl class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach (['as' => 'Authority score', 'dr' => 'Domain Rating', 'da' => 'Domain Authority', 'spam' => 'Spam score', 'traffic' => 'Ahrefs traffic', 'referring_domains' => 'Referring domains'] as $key => $label)
                                    <div class="rounded-xl border border-[#E3E5E8] px-4 py-3">
                                        <dt class="text-[13px] font-bold text-[#4A5560]">{{ $label }}</dt>
                                        <dd class="mt-1 font-display text-[22px] font-extrabold text-navy">{{ in_array($key, ['traffic', 'referring_domains'], true) ? $compact($site[$key] ?? null) : ($site[$key] ?? '') }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif

                        @if ($tool['kind'] === 'backlinks')
                            <dl class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-[#E3E5E8] px-4 py-3">
                                    <dt class="text-[13px] font-bold text-[#4A5560]">Link type</dt>
                                    <dd class="mt-1 text-[17px] font-bold text-navy">{{ $site['link_type'] ?? '' }}</dd>
                                </div>
                                <div class="rounded-xl border border-[#E3E5E8] px-4 py-3">
                                    <dt class="text-[13px] font-bold text-[#4A5560]">Link validity</dt>
                                    <dd class="mt-1 text-[17px] font-bold text-navy">{{ $site['link_validity'] ?? '' }}</dd>
                                </div>
                            </dl>
                        @endif

                        @if ($tool['kind'] === 'traffic')
                            <div class="mt-6">
                                <h3 class="text-[15px] font-extrabold text-navy">Top countries</h3>
                                @if (($site['top_countries'] ?? []) === [])
                                    <p class="mt-2 text-[15px] text-[#4A5560]">No country split is stored for this website.</p>
                                @else
                                    <ul class="mt-3 space-y-2">
                                        @foreach ($site['top_countries'] as $country)
                                            @php
                                                $raw = strtoupper((string) ($country[0] ?? ''));
                                                $code = $raw === 'UK' ? 'gb' : strtolower($raw);
                                                $share = $country[1] ?? null;
                                            @endphp
                                            <li class="flex min-h-11 items-center gap-3">
                                                <span class="inline-flex shrink-0 overflow-hidden rounded-[2px] ring-1 ring-black/10" style="width:20px;height:14px">
                                                    <x-ui.flag :code="$code" width="20" height="14" class="h-full w-full" />
                                                </span>
                                                <span class="w-10 text-[14px] font-bold text-navy uppercase">{{ $raw }}</span>
                                                <span class="h-2 flex-1 overflow-hidden rounded-full bg-[#F2F2F2]" role="meter" aria-label="{{ $raw }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) $share }}">
                                                    <span class="block h-full rounded-full bg-[#FF4917]" style="width: {{ max(0, min(100, (int) $share)) }}%"></span>
                                                </span>
                                                <span class="w-12 text-right text-[14px] font-bold text-navy">{{ is_numeric($share) ? (int) $share.'%' : '' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <p class="mt-4 text-[15px] text-[#4A5560]">Organic keywords: {{ $compact($site['keywords'] ?? null) }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            @endif

            <div class="mt-10">
                <h2 class="font-display text-[22px] font-extrabold text-navy">Other tools</h2>
                <ul class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($tools as $item)
                        @continue($item['slug'] === $tool['slug'])
                        <li>
                            <a href="{{ route('tools.show', $item['slug']) }}" class="flex min-h-11 flex-col justify-center rounded-xl border border-[#E3E5E8] bg-white px-4 py-3 hover:border-[#D63A10] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                                <span class="text-[15px] font-bold text-navy">{{ $item['label'] }}</span>
                                <span class="mt-1 text-[14px] leading-6 text-[#4A5560]">{{ $item['summary'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
@endsection
