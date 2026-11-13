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
    <section class="bg-[#F2F2F2]">
        <div class="mx-auto max-w-[1240px] px-6 py-14 lg:py-16">
            <nav aria-label="Breadcrumb" class="text-sm font-bold text-[#4A5560]">
                <a href="{{ url('/') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Home</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ route('sites.index') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Sites</a>
                <span aria-hidden="true"> / </span>
                <span class="text-navy">{{ $site['label'] }}</span>
            </nav>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div class="min-w-0">
                    <p class="text-[13px] font-extrabold tracking-wide text-[#C0340D] uppercase">Sites</p>
                    <h1 class="mt-2 max-w-[900px] font-display text-[30px] leading-tight font-extrabold text-navy lg:text-[40px]">{{ $site['label'] }}</h1>
                    <p class="mt-4 max-w-[760px] text-[17px] leading-8 text-[#4A5560]">{{ $site['about'] }}</p>
                    <p class="mt-4 max-w-[760px] text-[17px] leading-8 text-[#4A5560]">{{ $site['detail'] }}</p>
                    @if ($demo && $site['kind'] !== 'favorites')
                        <p class="mt-4 max-w-[760px] rounded-xl bg-[#FFF1EC] px-4 py-3 text-[15px] leading-6 text-[#C0340D]">Sample data for preview. These figures belong to fictional .example publishers.</p>
                    @endif
                </div>
                <aside class="min-w-0 rounded-[20px] border border-[#E3E5E8] bg-white p-5">
                    <h2 class="font-display text-[18px] font-extrabold text-navy">What this list shows</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($site['checks'] as $check)
                            <li class="flex items-start gap-2 text-[15px] leading-6 text-[#4A5560]">
                                <svg class="mt-1 h-4 w-4 shrink-0 text-[#D63A10]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5L20 7" /></svg>
                                <span>{{ $check }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <h2 class="mt-6 font-display text-[18px] font-extrabold text-navy">Other site lists</h2>
                    <ul class="mt-3">
                        @foreach ($sites as $other)
                            @if ($other['slug'] !== $site['slug'])
                                <li>
                                    <a href="{{ route('sites.show', $other['slug']) }}" class="flex min-h-11 items-center rounded-xl px-2 text-[14px] leading-5 font-bold text-navy hover:bg-[#FFF1EC] hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $other['label'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </aside>
            </div>

            <div class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
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
