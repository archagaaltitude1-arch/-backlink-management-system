@extends('layouts.public')

@section('document_title', 'Wishlist – BacklinkPro')

@section('meta_description', 'Save publisher websites from the list and open them again from the heart in the navbar.')

@section('content')
    @php
        $count = $saved->count();
        $canRemoveAccount = auth()->check() && \Illuminate\Support\Facades\Route::has('customer.saved-websites.destroy');
        $features = [
            ['Publisher list', 'Every stored guest-post publisher.', route('publishers.index')],
            ['Niches', 'Publishers grouped by category.', route('niches.index')],
            ['Services', 'Guest posts, niche edits, and links.', url('/services')],
            ['Packages', 'Backlink packages by size.', route('packages.index')],
            ['SEO tools', 'Check a stored domain.', route('tools.index')],
            ['Site lists', 'Traffic, country, and keyword lists.', route('sites.index')],
        ];
        $metric = function ($label, $value) {
            return '<div class="wl-metric"><dt>'.$label.'</dt><dd>'.e($value).'</dd></div>';
        };
    @endphp

    <style>
        .wl { background: #F2F2F2; }
        .wl-wrap { max-width: 1240px; margin: 0 auto; padding: 28px 24px 72px; }
        .wl-crumb { margin: 0; color: #4A5560; font-size: 14px; font-weight: 700; }
        .wl-crumb a { color: inherit; }
        .wl-crumb a:hover { color: #C0340D; }
        .wl-hero { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(260px, .65fr); gap: 20px; margin-top: 22px; }
        .wl-intro, .wl-steps, .wl-panel, .wl-pick, .wl-card { background: #fff; border: 1px solid #E3E5E8; border-radius: 24px; }
        .wl-intro { position: relative; overflow: hidden; padding: 36px 36px 32px; }
        .wl-intro::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: linear-gradient(90deg, #D63A10 0%, #FF4917 55%, #FFB199 100%); }
        .wl-kicker { margin: 0; color: #C0340D; font-size: 13px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .wl-intro h1 { margin: 10px 0 0; color: #152029; font-size: 40px; line-height: 1.1; font-weight: 800; }
        .wl-lead { max-width: 560px; margin: 12px 0 0; color: #4A5560; font-size: 17px; line-height: 1.65; }
        .wl-count { display: inline-flex; min-height: 36px; align-items: center; margin-top: 18px; border-radius: 999px; background: #FFF1EC; padding: 0 14px; color: #C0340D; font-size: 14px; font-weight: 800; }
        .wl-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .wl-btn { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; border-radius: 12px; padding: 0 18px; font-size: 15px; font-weight: 800; }
        .wl-btn-primary { border: 0; background: #D63A10; color: #fff; }
        .wl-btn-primary:hover { background: #FF4917; }
        .wl-btn-line { border: 1.5px solid #152029; background: #fff; color: #152029; }
        .wl-btn-line:hover { background: #F2F2F2; }
        .wl-steps { background: #152029; color: #fff; padding: 28px 24px; }
        .wl-steps h2 { margin: 0; font-size: 20px; font-weight: 800; }
        .wl-steps ol { margin: 16px 0 0; padding: 0; list-style: none; }
        .wl-steps li { display: flex; gap: 12px; margin-top: 14px; color: #D5DCE3; font-size: 15px; line-height: 1.5; }
        .wl-num { display: inline-flex; width: 36px; height: 36px; flex: none; align-items: center; justify-content: center; border-radius: 999px; background: #D63A10; color: #fff; font-size: 15px; font-weight: 800; }
        .wl-note { margin: 16px 0 0; border-radius: 14px; padding: 12px 14px; font-size: 15px; font-weight: 700; }
        .wl-note-ok { background: #E8F7EE; color: #146C43; }
        .wl-note-bad { background: #FFF1EC; color: #C0340D; }
        .wl-block { margin-top: 22px; }
        .wl-block h2 { margin: 0; color: #152029; font-size: 26px; font-weight: 800; }
        .wl-block > p { max-width: 640px; margin: 6px 0 0; color: #4A5560; font-size: 16px; line-height: 1.6; }
        .wl-saved { display: grid; grid-template-columns: 1fr; gap: 12px; margin-top: 16px; }
        .wl-card { display: grid; grid-template-columns: minmax(0, 1.4fr) auto auto; gap: 16px; align-items: center; padding: 18px 20px; }
        .wl-card h3 { margin: 0; overflow: hidden; color: #152029; font-size: 18px; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; }
        .wl-card .wl-domain { margin: 4px 0 0; color: #C0340D; font-size: 14px; font-weight: 700; }
        .wl-metrics { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; }
        .wl-metric { min-width: 72px; border-radius: 12px; background: #F7F8F9; padding: 8px 10px; }
        .wl-metric dt { color: #4A5560; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .wl-metric dd { margin: 2px 0 0; color: #152029; font-size: 16px; font-weight: 800; }
        .wl-empty { margin-top: 16px; border: 1px dashed #E3E5E8; border-radius: 24px; background: #fff; padding: 28px; color: #4A5560; font-size: 16px; line-height: 1.6; }
        .wl-picks { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
        .wl-pick { display: flex; min-height: 180px; flex-direction: column; padding: 16px; }
        .wl-pick h3 { margin: 0; overflow: hidden; color: #152029; font-size: 16px; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; }
        .wl-pick p { margin: 6px 0 0; color: #4A5560; font-size: 13px; line-height: 1.45; }
        .wl-pick form { margin-top: auto; padding-top: 14px; }
        .wl-pick .wl-btn { width: 100%; }
        .wl-features { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
        .wl-feature { display: flex; min-height: 92px; flex-direction: column; justify-content: center; border: 1px solid #E3E5E8; border-radius: 18px; background: #fff; padding: 16px 18px; }
        .wl-feature:hover { border-color: #D63A10; }
        .wl-feature strong { color: #152029; font-size: 16px; }
        .wl-feature span { margin-top: 4px; color: #4A5560; font-size: 14px; line-height: 1.45; }
        .wl-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .wl-pill { display: inline-flex; min-height: 44px; align-items: center; border: 1px solid #E3E5E8; border-radius: 999px; background: #fff; padding: 0 14px; color: #152029; font-size: 14px; font-weight: 800; }
        .wl-pill:hover { background: #FFF1EC; color: #C0340D; }
        @media (max-width: 1100px) {
            .wl-picks { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .wl-card { grid-template-columns: 1fr; }
        }
        @media (max-width: 860px) {
            .wl-hero, .wl-features { grid-template-columns: 1fr; }
            .wl-intro h1 { font-size: 32px; }
            .wl-picks { grid-template-columns: 1fr; }
        }
    </style>

    <section class="wl">
        <div class="wl-wrap">
            <nav class="wl-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true"> / </span>
                <span>Wishlist</span>
            </nav>

            <div class="wl-hero">
                <div class="wl-intro">
                    <p class="wl-kicker">Saved publishers</p>
                    <h1>Wishlist</h1>
                    <p class="wl-lead">Keep the publishers you want to order from. Save one below, and it stays on this page when you come back through the heart.</p>
                    <p class="wl-count">{{ $count }} {{ $count === 1 ? 'publisher' : 'publishers' }} saved</p>
                    <div class="wl-actions">
                        <a class="wl-btn wl-btn-primary" href="#save-a-publisher">Save a publisher</a>
                        <a class="wl-btn wl-btn-line" href="{{ route('publishers.index') }}">Open the full list</a>
                    </div>
                </div>
                <aside class="wl-steps" aria-labelledby="wl-steps-title">
                    <h2 id="wl-steps-title">How it works</h2>
                    <ol>
                        <li><span class="wl-num">1</span><span>Choose a publisher from the cards on this page.</span></li>
                        <li><span class="wl-num">2</span><span>Press Save. The website is kept for this browser.</span></li>
                        <li><span class="wl-num">3</span><span>Open the heart in the navbar to return to the same list.</span></li>
                    </ol>
                </aside>
            </div>

            @if (session('wishlist_status'))
                <p class="wl-note wl-note-ok" role="status">{{ session('wishlist_status') }}</p>
            @endif
            @if (session('wishlist_error'))
                <p class="wl-note wl-note-bad" role="alert">{{ session('wishlist_error') }}</p>
            @endif

            <div class="wl-block">
                <h2>Your list</h2>
                @if ($saved->isEmpty())
                    <p class="wl-empty">Nothing is saved yet. Use Save on a publisher card below. Only publishers already on the site can be added.</p>
                @else
                    <ul class="wl-saved">
                        @foreach ($saved as $item)
                            <li class="wl-card">
                                <div>
                                    <h3>{{ $item['name'] ?? $item['domain'] }}</h3>
                                    @if (filled($item['url'] ?? null))
                                        <a class="wl-domain" href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer">{{ $item['domain'] }}</a>
                                    @else
                                        <p class="wl-domain">{{ $item['domain'] }}</p>
                                    @endif
                                    @if (filled($item['niche'] ?? null) || filled($item['language'] ?? null))
                                        <p class="wl-domain" style="color:#4A5560;font-weight:700">{{ collect([$item['niche'] ?? '', $item['language'] ?? ''])->filter()->implode(' · ') }}</p>
                                    @endif
                                </div>
                                <dl class="wl-metrics">
                                    @if ($item['dr'] !== null)
                                        {!! $metric('DR', $item['dr']) !!}
                                    @endif
                                    @if ($item['da'] !== null)
                                        {!! $metric('DA', $item['da']) !!}
                                    @endif
                                    @if ($item['traffic'] !== null)
                                        {!! $metric('Traffic', number_format((int) $item['traffic'])) !!}
                                    @endif
                                    @if (is_numeric($item['price'] ?? null))
                                        {!! $metric('Price', '$'.number_format((float) $item['price'], 2)) !!}
                                    @endif
                                </dl>
                                @if (($item['source'] ?? '') === 'account' && $canRemoveAccount && ! empty($item['id']))
                                    <form method="POST" action="{{ route('customer.saved-websites.destroy', $item['id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="wl-btn wl-btn-line" type="submit">Remove</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('wishlist.destroy') }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="domain" value="{{ $item['domain'] }}">
                                        <button class="wl-btn wl-btn-line" type="submit">Remove</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="wl-block" id="save-a-publisher">
                <h2>Save a publisher</h2>
                <p>These are publishers already stored on the site, highest Domain Rating first. Saving one adds it to the list above.</p>
                @if ($picks->isEmpty())
                    <p class="wl-empty">Every publisher currently offered on this page is already on your wishlist.</p>
                @else
                    <ul class="wl-picks">
                        @foreach ($picks as $row)
                            <li class="wl-pick">
                                <h3>{{ $row['domain'] }}</h3>
                                <p>
                                    {{ $row['niches'][0] ?? 'Publisher' }}
                                    @if ($row['dr'] !== null)
                                        · DR {{ $row['dr'] }}
                                    @endif
                                    @if (is_numeric($row['price'] ?? null))
                                        · ${{ number_format((float) $row['price'], 2) }}
                                    @endif
                                </p>
                                <form method="POST" action="{{ route('wishlist.store') }}">
                                    @csrf
                                    <input type="hidden" name="domain" value="{{ $row['domain'] }}">
                                    <button class="wl-btn wl-btn-primary" type="submit">Save</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="wl-block">
                <h2>Other features</h2>
                <ul class="wl-features">
                    @foreach ($features as [$title, $text, $href])
                        <li>
                            <a class="wl-feature" href="{{ $href }}">
                                <strong>{{ $title }}</strong>
                                <span>{{ $text }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="wl-pills" aria-label="Site lists">
                    @foreach ($sites as $site)
                        <a class="wl-pill" href="{{ route('sites.show', $site['slug']) }}">{{ $site['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
