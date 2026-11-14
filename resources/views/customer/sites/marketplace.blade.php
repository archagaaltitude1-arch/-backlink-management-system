@extends('layouts.public')

@section('document_title', $site['label'].' – BacklinkPro')
@section('meta_description', $site['about'])

@section('content')
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
            @if ($demo)
                <p class="site-front-note">Sample data for preview. These figures belong to fictional .example publishers.</p>
            @endif

            @include('customer.sites.partials.also-check', [
                'siteOptions' => $sites,
                'currentSlug' => $site['slug'],
            ])
        </div>
    </section>

    @include('customer.publishers.listing', [
        'rows' => $rows,
        'demo' => false,
        'categories' => $categories,
        'defaultType' => $defaultType ?? '',
        'defaultNiche' => $defaultNiche ?? '',
        'defaultDr' => $defaultDr ?? '',
        'siteOptions' => $sites,
        'skipIntro' => true,
    ])
@endsection
