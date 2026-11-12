@extends('layouts.public')

@section('document_title', $overview['h1'].' – BacklinkPro')

@section('meta_description', $overview['intro'])

@section('content')
    <style>
        .svc-h1 { margin: 0; font-size: 34px; line-height: 1.1; font-weight: 800; }
        .svc-h2 { margin: 0; font-size: 30px; line-height: 1.15; font-weight: 800; }
        .svc-section { padding: 56px 0; }
        @media (min-width: 1024px) {
            .svc-h1 { font-size: 52px; }
            .svc-h2 { font-size: 40px; }
            .svc-section { padding: 88px 0; }
        }
    </style>

    <section class="bg-[#F2F2F2]" aria-labelledby="services-overview-heading">
        <div class="mx-auto max-w-[1240px] px-6 svc-section">
            <p class="text-sm font-bold tracking-[0.08em] text-[#C0340D] uppercase">Services</p>
            <h1 id="services-overview-heading" class="svc-h1 mt-3 max-w-3xl font-display text-navy">{{ $overview['h1'] }}</h1>
            <p class="mt-4 max-w-2xl text-[17px] leading-8 text-[#4A5560] sm:text-[19px]">{{ $overview['intro'] }}</p>
        </div>
    </section>

    <section class="bg-white" aria-label="Service list">
        <div class="mx-auto max-w-[1240px] px-6 svc-section">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($cards as $card)
                    <article class="flex h-full flex-col rounded-[24px] border border-[#E3E5E8] bg-white p-6">
                        <span class="grid h-12 w-12 place-items-center rounded-xl bg-[#FFF1EC] text-[#D63A10]">
                            <x-ui.icon :name="$card['icon']" />
                        </span>
                        <h2 class="mt-4 font-display text-[22px] font-extrabold text-navy">{{ $card['name'] }}</h2>
                        <p class="mt-2 text-[15px] leading-6 text-[#4A5560]">{{ $card['menu'] }}</p>
                        <p class="mt-3 text-sm font-bold text-navy">{{ $card['turnaround'] }}</p>
                        <p class="mt-1 text-sm font-bold text-[#C0340D]">{{ $card['price'] }}</p>
                        <a href="{{ url('/services/'.$card['slug']) }}" class="mt-auto inline-flex min-h-11 items-center pt-4 text-[15px] font-bold text-[#C0340D] underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Learn more →</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#F2F2F2]" aria-labelledby="compare-heading">
        <div class="mx-auto max-w-[1240px] px-6 svc-section">
            <h2 id="compare-heading" class="svc-h2 font-display text-navy">Compare all services</h2>
            <div class="mt-8 overflow-x-auto rounded-[24px] border border-[#E3E5E8] bg-white">
                <table class="w-full min-w-[760px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-[#E3E5E8]">
                            <th scope="col" class="px-4 py-4 text-sm font-bold text-[#4A5560]"> </th>
                            @foreach ($cards as $card)
                                <th scope="col" class="px-4 py-4 font-display text-base font-extrabold text-navy">{{ $card['name'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($compareRows as $row)
                            <tr class="border-t border-[#E3E5E8]">
                                <th scope="row" class="px-4 py-4 text-[15px] font-bold text-navy">{{ $row['label'] }}</th>
                                @foreach ($row['cells'] as $cell)
                                    <td class="px-4 py-4 text-[15px] leading-6 text-[#4A5560]">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
