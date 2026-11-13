@extends('layouts.public')

@section('document_title', 'SEO Tools – BacklinkPro')

@section('meta_description', 'SEO tools from BacklinkPro: authority, backlink, domain rating, domain authority, traffic, spam score, anchor text and competitor checks.')

@section('content')
    <section class="bg-[#F2F2F2]">
        <div class="mx-auto max-w-[1240px] px-6 py-14 lg:py-[88px]">
            <nav aria-label="Breadcrumb" class="text-sm font-bold text-[#4A5560]">
                <a href="{{ url('/') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Home</a>
                <span aria-hidden="true"> / </span>
                <span class="text-navy">Tools</span>
            </nav>
            <h1 class="mt-4 font-display text-[30px] leading-tight font-extrabold text-navy lg:text-[44px]">SEO tools</h1>
            <p class="mt-4 max-w-[760px] text-[17px] leading-8 text-[#4A5560]">Open a tool to read what it checks, then run it against a publisher website already listed in the marketplace.</p>
            <ul class="mt-10 grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ($tools as $tool)
                    <li>
                        <a href="{{ route('tools.show', $tool['slug']) }}" class="flex min-h-11 flex-col justify-center rounded-[20px] border border-[#E3E5E8] bg-white p-6 hover:border-[#D63A10] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                            <span class="font-display text-[20px] font-extrabold text-navy">{{ $tool['label'] }}</span>
                            <span class="mt-2 text-[15px] leading-7 text-[#4A5560]">{{ $tool['summary'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
