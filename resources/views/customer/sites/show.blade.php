@extends('layouts.public')

@section('document_title', $site['label'].' – BacklinkPro')

@section('meta_description', $site['about'])

@section('content')
    @php
        $compact = fn ($value) => \App\Support\DemoNicheData::compact(is_numeric($value) ? (int) $value : null);
        $savedHref = \Illuminate\Support\Facades\Route::has('customer.saved-websites')
            ? route('customer.saved-websites')
            : (\Illuminate\Support\Facades\Route::has('auth.login') ? route('auth.login') : url('/login'));
        $known = [];
        foreach (\App\Support\DemoNicheData::names() as $name) {
            $known[\Illuminate\Support\Str::slug($name)] = $name;
        }
        $flat = $groups !== null
            ? collect($groups)->flatMap(fn (array $group) => $group['rows'])->all()
            : $rows;
        $categories = [];
        foreach ($flat as $row) {
            foreach ($row['niches'] ?? [] as $niche) {
                if (is_string($niche) && $niche !== '' && $niche !== 'Publisher') {
                    $categories[$niche] = \Illuminate\Support\Str::slug($niche);
                }
            }
        }
        ksort($categories);
        $chartRows = [];
        $chartSlug = function (string $label): string {
            $slug = \Illuminate\Support\Str::slug($label);

            return $slug !== '' ? $slug : 'unknown';
        };
        if ($site['chart'] === 'country') {
            foreach (array_slice($groups ?? [], 0, 8) as $group) {
                $top = $group['rows'][0] ?? null;
                $value = (int) ($top['traffic'] ?? 0);
                $chartRows[] = [
                    'label' => $group['country'],
                    'value' => $value,
                    'text' => $compact($top['traffic'] ?? null),
                    'full' => number_format($value),
                    'target' => 'site-country-'.$chartSlug((string) $group['country']),
                ];
            }
        } elseif ($site['chart'] !== null) {
            foreach (array_slice($rows, 0, 8) as $row) {
                $value = $row[$site['chart']] ?? null;
                $number = is_numeric($value) ? (int) $value : 0;
                $chartRows[] = [
                    'label' => (string) ($row['domain'] ?? ''),
                    'value' => $number,
                    'text' => $site['chart'] === 'dr' ? (is_numeric($value) ? (string) (int) $value : '') : $compact($value),
                    'full' => is_numeric($value) ? number_format($number) : '',
                    'target' => 'site-row-'.$chartSlug((string) ($row['domain'] ?? 'site')),
                ];
            }
        }
        $chartMax = 0;
        foreach ($chartRows as $bar) {
            $chartMax = max($chartMax, $bar['value']);
        }
        $chartTitle = match ($site['chart']) {
            'dr' => 'Domain Rating',
            'traffic' => 'Monthly traffic',
            'keywords' => 'Organic keywords',
            'country' => 'Top traffic by country',
            default => 'Preview',
        };
    @endphp

    <style>
        .site-front { background: #fff; }
        .site-front-wrap { max-width: 1100px; margin: 0 auto; padding: 28px 20px 8px; color: #152029; }
        .site-front-crumb { margin: 0 0 18px; color: #4A5560; font-size: 14px; font-weight: 700; }
        .site-front-crumb a { color: inherit; text-decoration: none; }
        .site-front-crumb a:hover { color: #C0340D; }
        .site-front h1 {
            margin: 0;
            font-family: Montserrat, sans-serif;
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 800;
            line-height: 1.15;
        }
        .site-front-about, .site-front-detail {
            margin: 16px 0 0;
            max-width: 820px;
            color: #4A5560;
            font-size: 16px;
            line-height: 1.7;
        }
        .site-front h2 {
            margin: 28px 0 0;
            max-width: 820px;
            font-family: Montserrat, sans-serif;
            font-size: 22px;
            font-weight: 800;
            line-height: 1.3;
        }
        .site-front-checks {
            margin: 16px 0 0;
            max-width: 820px;
            padding: 0;
            list-style: none;
            color: #4A5560;
            font-size: 16px;
            line-height: 1.7;
        }
        .site-front-checks li + li { margin-top: 10px; }
        .site-front-note {
            margin: 16px 0 0;
            max-width: 820px;
            border-radius: 12px;
            background: #FFF1EC;
            padding: 12px 14px;
            color: #C0340D;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
        }
        .site-body { background: #F2F2F2; }
        .site-body-wrap { max-width: 1240px; margin: 0 auto; padding: 28px 20px 72px; }
    </style>

    <section class="site-front">
        <div class="site-front-wrap">
            <nav class="site-front-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ route('sites.index') }}">Sites</a>
                <span aria-hidden="true"> / </span>
                <span>{{ $site['label'] }}</span>
            </nav>

            <h1>{{ $site['label'] }}</h1>
            <p class="site-front-about">{{ $site['about'] }}</p>
            <h2>{{ $site['headline'] }}</h2>
            <p class="site-front-detail">{{ $site['detail'] }}</p>
            <ul class="site-front-checks">
                @foreach ($site['checks'] as $check)
                    <li>{{ $check }}</li>
                @endforeach
            </ul>
            @if ($demo && $site['kind'] !== 'favorites')
                <p class="site-front-note">Sample data for preview. These figures belong to fictional .example publishers.</p>
            @endif

            @include('customer.sites.partials.also-check', [
                'siteOptions' => $sites,
                'currentSlug' => $site['slug'],
            ])
        </div>
    </section>

    <section class="site-body">
        <div class="site-body-wrap">
            <div class="rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
                <h2 class="font-display text-[22px] font-extrabold text-navy">Preview</h2>
                @if ($site['chart'] === null)
                    <p class="mt-1 text-[15px] leading-6 text-[#4A5560]">Saved publishers appear here after you sign in.</p>
                @else
                    <p class="mt-1 text-[15px] leading-6 text-[#4A5560]">{{ $chartTitle }} from the publishers on this list.</p>
                @endif
                @if ($site['kind'] === 'favorites')
                    <p class="mt-4 text-[15px] leading-7 text-[#4A5560]">Save publishers on your account to see them here. This preview does not invent a chart.</p>
                @elseif ($chartRows === [] || $chartMax === 0)
                    <p class="mt-4 text-[15px] leading-7 text-[#4A5560]">No stored figures to chart for this list yet.</p>
                @else
                    <ul class="mt-5 space-y-2">
                        @foreach ($chartRows as $bar)
                            <li>
                                <button type="button" class="site-chart-bar flex min-h-11 w-full flex-col justify-center rounded-xl px-2 text-left hover:bg-[#FFF1EC] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]" data-target="{{ $bar['target'] }}" aria-label="Show {{ $bar['label'] }}, {{ $bar['full'] }}">
                                    <span class="flex items-baseline justify-between gap-3 text-[14px]">
                                        <span class="min-w-0 truncate font-bold text-navy">{{ $bar['label'] }}</span>
                                        <span class="shrink-0 text-[#4A5560]">{{ $bar['text'] }}</span>
                                    </span>
                                    <span class="mt-1 block h-2 overflow-hidden rounded-full bg-[#F2F2F2]" aria-hidden="true">
                                        <span class="block h-full rounded-full bg-[#D63A10]" style="width: {{ $bar['value'] > 0 ? max(4, (int) round($bar['value'] / $chartMax * 100)) : 0 }}%"></span>
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
                <h2 class="font-display text-[22px] font-extrabold text-navy">Categories</h2>
                @if ($site['kind'] === 'favorites')
                    <p class="mt-3 text-[15px] leading-7 text-[#4A5560]">Categories appear after you save publishers. Sign in to open the websites on your account.</p>
                @elseif ($categories === [])
                    <p class="mt-3 text-[15px] leading-7 text-[#4A5560]">No niche is stored for the publishers on this list.</p>
                @else
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($categories as $name => $slug)
                            <li>
                                @if (isset($known[$slug]))
                                    <a href="{{ route('niches.show', $slug) }}" class="inline-flex min-h-11 items-center rounded-full border border-[#E3E5E8] px-4 text-[14px] font-bold text-navy hover:bg-[#FFF1EC] hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $name }}</a>
                                @else
                                    <span class="inline-flex min-h-11 items-center rounded-full border border-[#E3E5E8] px-4 text-[14px] font-bold text-navy">{{ $name }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($site['kind'] === 'favorites')
                <div class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-6">
                    <p class="text-[16px] leading-7 text-[#4A5560]">Save a publisher with the heart on its listing. Those websites stay on your account.</p>
                    <a href="{{ $savedHref }}" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#D63A10] px-6 text-[15px] font-bold text-white hover:bg-[#FF4917] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Open favorite websites</a>
                </div>
            @elseif ($groups !== null)
                @forelse ($groups as $group)
                    <h2 id="site-country-{{ $chartSlug((string) $group['country']) }}" class="mt-8 scroll-mt-24 font-display text-[22px] font-extrabold text-navy">{{ $group['country'] }}</h2>
                    @include('customer.sites.table', ['rows' => $group['rows'], 'compact' => $compact])
                @empty
                    <p class="mt-8 rounded-[20px] border border-[#E3E5E8] bg-white px-5 py-4 text-[15px] leading-7 text-[#4A5560]">No publisher has a country stored for this list.</p>
                @endforelse
            @elseif ($rows === [])
                <p class="mt-8 rounded-[20px] border border-[#E3E5E8] bg-white px-5 py-4 text-[15px] leading-7 text-[#4A5560]">No publisher is listed for this yet.</p>
            @else
                <div class="mt-8">
                    @include('customer.sites.table', ['rows' => $rows, 'compact' => $compact])
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.site-chart-bar').forEach(function (button) {
            button.addEventListener('click', function () {
                var target = document.getElementById(button.getAttribute('data-target'));
                if (!target) {
                    return;
                }
                document.querySelectorAll('.is-chart-target').forEach(function (node) {
                    node.classList.remove('is-chart-target');
                });
                target.classList.add('is-chart-target');
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    </script>
@endpush
