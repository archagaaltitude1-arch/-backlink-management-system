@extends('layouts.public')

@section('document_title', 'Sites – BacklinkPro')

@section('meta_description', 'Publisher site lists from BacklinkPro: premium guest posts, niche edits, organic traffic, countries, sidebar links, saved sites and keyword sites.')

@section('content')
    <section class="bg-[#F7F8FA]">
        <div class="w-full px-4 py-14 lg:px-6 lg:py-20">
            <nav aria-label="Breadcrumb" class="text-sm font-bold text-[#4A5560]">
                <a href="{{ url('/') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Home</a>
                <span aria-hidden="true"> / </span>
                <span class="text-navy">Sites</span>
            </nav>
            <p class="eyebrow mt-6 text-[#FF4917]">Publisher lists</p>
            <h1 class="mt-3 font-display text-[30px] leading-tight font-extrabold text-navy lg:text-[44px]">Sites</h1>
            <p class="mt-4 max-w-[68ch] text-[17px] leading-7 text-[#4A5560]">Choose a publisher list. Each one opens the websites that match.</p>
            <ul class="mt-10 grid items-stretch gap-4 sm:grid-cols-2">
                @foreach (\App\Support\PublicSites::items() as $site)
                    @php $wide = $loop->last && $loop->count % 2 === 1; @endphp
                    <li id="{{ $site['slug'] }}" class="{{ $wide ? 'sm:col-span-2' : '' }} flex">
                        <a href="{{ route('sites.show', $site['slug']) }}" class="{{ $wide ? 'flex-col justify-between gap-6 sm:flex-row sm:items-center' : 'flex-col' }} group flex w-full rounded-[20px] border border-[#E3E5E8] bg-white p-6 transition hover:border-[#FF4917] hover:shadow-[0_18px_40px_rgba(21,32,41,0.08)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                            <span class="{{ $wide ? 'max-w-3xl' : 'flex h-full flex-col' }}">
                                <span class="font-sans text-[12px] font-extrabold tracking-[0.14em] text-[#FF4917]">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h2 class="{{ $wide ? '' : 'min-h-[2.8em]' }} mt-3 font-display text-[18px] leading-snug font-extrabold text-navy group-hover:text-[#C0340D]">{{ $site['label'] }}</h2>
                                <span class="{{ $wide ? '' : 'flex-1' }} mt-2 text-[15px] leading-7 text-[#4A5560]">{{ $site['about'] }}</span>
                            </span>
                            <span class="{{ $wide ? '' : 'mt-6' }} inline-flex shrink-0 items-center gap-1.5 text-sm font-extrabold text-[#C0340D]">View websites <span aria-hidden="true" class="transition-transform group-hover:translate-x-0.5">→</span></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
