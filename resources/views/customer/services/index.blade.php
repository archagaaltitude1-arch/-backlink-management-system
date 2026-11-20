@extends('layouts.public')

@section('document_title', $overview['h1'].' – BacklinkPro')

@section('meta_description', $overview['intro'])

@section('content')
    <style>
        .svc-wrap { padding-left: clamp(24px, 4vw, 56px); padding-right: clamp(24px, 4vw, 56px); }
        .svc-hero { position: relative; overflow: hidden; background: #152029; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .svc-hero-bg { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; pointer-events: none; }
        .svc-hero-shade { position: absolute; inset: 0; background: rgba(21, 32, 41, 0.55); pointer-events: none; }
        .svc-hero-inner {
            position: relative;
            padding-top: 64px;
            padding-bottom: 64px;
        }
        .svc-kicker {
            margin: 0 0 12px;
            color: #FF4917;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .svc-hero-copy {
            max-width: 48rem;
            margin-inline: auto;
            text-align: center;
        }
        .svc-h1 {
            margin: 0 auto;
            color: #fff;
            font-size: clamp(34px, 4.4vw, 52px);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .svc-lead {
            margin: 16px auto 0;
            max-width: 46ch;
            color: rgba(255, 255, 255, 0.82);
            font-size: 17px;
            line-height: 1.65;
        }
        .svc-list { background: #F6F7F9; }
        .svc-list-inner { padding-top: 36px; padding-bottom: 56px; }
        .svc-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .svc-card {
            display: flex;
            min-height: 100%;
            flex-direction: column;
            border: 1px solid #E3E5E8;
            border-radius: 20px;
            background: #fff;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(21, 32, 41, 0.05);
        }
        .svc-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
        .svc-icon {
            display: grid;
            width: 48px;
            height: 48px;
            place-items: center;
            border-radius: 14px;
            background: #FFF1EC;
            color: #D63A10;
        }
        .svc-from { margin: 0; color: #C0340D; font-size: 15px; font-weight: 800; }
        .svc-name { margin: 20px 0 0; color: #152029; font-size: 22px; line-height: 1.2; font-weight: 800; }
        .svc-line { margin: 8px 0 0; color: #4A5560; font-size: 15px; line-height: 1.55; }
        .svc-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 18px 0 0;
            color: #152029;
            font-size: 13px;
            font-weight: 700;
        }
        .svc-meta::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 99px;
            background: #FF4917;
        }
        .svc-more {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            margin-top: auto;
            padding: 0 16px;
            border: 1px solid #152029;
            border-radius: 999px;
            color: #152029;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
        }
        .svc-card .svc-more { margin-top: 22px; align-self: flex-start; }
        .svc-more:hover { background: #152029; color: #fff; }
        .svc-more:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .svc-compare { background: #fff; }
        .svc-compare-inner { padding-top: 56px; padding-bottom: 72px; }
        .svc-h2 {
            margin: 0;
            color: #152029;
            font-size: clamp(28px, 3vw, 36px);
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .svc-compare-lead { margin: 10px 0 0; max-width: 42ch; color: #4A5560; font-size: 16px; line-height: 1.6; }
        .svc-scroll-note { margin: 18px 0 0; color: #4A5560; font-size: 13px; font-weight: 700; }
        .svc-table-wrap {
            margin-top: 22px;
            overflow-x: auto;
            border: 1px solid #E3E5E8;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(21, 32, 41, 0.05);
        }
        .svc-table { width: 100%; min-width: 760px; border-collapse: collapse; text-align: left; }
        .svc-table th, .svc-table td { padding: 16px 18px; vertical-align: top; }
        .svc-table thead th {
            background: #F6F7F9;
            color: #152029;
            font-size: 15px;
            font-weight: 800;
        }
        .svc-table tbody th {
            position: sticky;
            left: 0;
            background: #fff;
            color: #152029;
            font-size: 14px;
            font-weight: 800;
        }
        .svc-table td { color: #4A5560; font-size: 15px; line-height: 1.5; }
        .svc-table tbody tr + tr th,
        .svc-table tbody tr + tr td { border-top: 1px solid #E3E5E8; }
        .svc-price-row th, .svc-price-row td { background: #FFF1EC; color: #152029; font-weight: 800; }
        .svc-price-row th { background: #FFF1EC; }
        @media (min-width: 900px) {
            .svc-hero-inner { padding-top: 96px; padding-bottom: 96px; }
            .svc-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }
            .svc-list-inner { padding-top: 40px; padding-bottom: 72px; }
            .svc-scroll-note { display: none; }
        }
        @media (min-width: 640px) and (max-width: 899px) {
            .svc-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>

    <section class="svc-hero" aria-labelledby="services-overview-heading">
        <img class="svc-hero-bg" src="{{ asset('images/services/services-overview-bg.jpg') }}" alt="">
        <div class="svc-hero-shade" aria-hidden="true"></div>
        <div class="svc-wrap svc-hero-inner">
            <div class="svc-hero-copy">
                <p class="svc-kicker">Services</p>
                <h1 id="services-overview-heading" class="svc-h1 font-display">{{ $overview['h1'] }}</h1>
                <p class="svc-lead">{{ $overview['intro'] }}</p>
            </div>
        </div>
    </section>

    <section class="svc-list" aria-label="Service list">
        <div class="svc-wrap svc-list-inner">
            <div class="svc-grid">
                @foreach ($cards as $card)
                    <article class="svc-card">
                        <div class="svc-card-top">
                            <span class="svc-icon">
                                <x-ui.icon :name="$card['icon']" />
                            </span>
                            <p class="svc-from">{{ $card['price'] }}</p>
                        </div>
                        <h2 class="svc-name font-display">{{ $card['name'] }}</h2>
                        <p class="svc-line">{{ $card['menu'] }}</p>
                        <p class="svc-meta">{{ $card['turnaround'] }}</p>
                        <a href="{{ url('/services/'.$card['slug']) }}" class="svc-more">View {{ $card['name'] }}</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="svc-compare" aria-labelledby="compare-heading">
        <div class="svc-wrap svc-compare-inner">
            <h2 id="compare-heading" class="svc-h2 font-display">Compare all services</h2>
            <p class="svc-compare-lead">Content, placement, turnaround and starting price for each link type.</p>
            <p class="svc-scroll-note">Scroll sideways to compare every column.</p>
            <div class="svc-table-wrap">
                <table class="svc-table">
                    <thead>
                        <tr>
                            <th scope="col"> </th>
                            @foreach ($cards as $card)
                                <th scope="col">{{ $card['name'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($compareRows as $row)
                            <tr @class(['svc-price-row' => $row['label'] === 'Price from'])>
                                <th scope="row">{{ $row['label'] }}</th>
                                @foreach ($row['cells'] as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
