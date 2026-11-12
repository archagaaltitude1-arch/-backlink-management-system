@extends('layouts.public')

@section('document_title', 'Sites – BacklinkPro')

@section('meta_description', 'Publisher site lists from BacklinkPro: premium guest posts, niche edits, organic traffic, countries, sidebar links, saved sites and keyword sites.')

@section('content')
    <section class="bg-[#F2F2F2]">
        <div class="mx-auto max-w-[1240px] px-6 py-14 lg:py-[88px]">
            <nav aria-label="Breadcrumb" class="text-sm font-bold text-[#4A5560]">
                <a href="{{ url('/') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Home</a>
                <span aria-hidden="true"> / </span>
                <span class="text-navy">Sites</span>
            </nav>
            <h1 class="mt-4 font-display text-[30px] leading-tight font-extrabold text-navy lg:text-[44px]">Sites</h1>
            <p class="mt-4 max-w-[760px] text-[17px] leading-8 text-[#4A5560]">Choose a publisher list. Each one opens the websites that match.</p>
            <ul class="mt-10 grid grid-cols-1 gap-4">
                @foreach (\App\Support\PublicSites::items() as $site)
                    <li id="{{ $site['slug'] }}" class="rounded-[20px] border border-[#E3E5E8] bg-white p-6">
                        <h2 class="font-display text-[22px] font-extrabold text-navy">
                            <a href="{{ route('sites.show', $site['slug']) }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $site['label'] }}</a>
                        </h2>
                        <p class="mt-2 text-[15px] leading-7 text-[#4A5560]">{{ $site['about'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
